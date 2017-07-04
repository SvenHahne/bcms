<?php

    class BEditorBackup extends BEditorModule
    {
        protected $genPar = array(
                                  array("saveintrv", 5, "Save Interval"),
                                  array("nrbackups", 10, "Nr of Backups")
                                  );
        protected $gpVal = array();
        protected $intPar = array(
                                  array("editcount", 0),
                                  array("backupnr", -1)
                                  );
        protected $ipVal = array();
        protected $dstFolder = ".backup";
        protected $bRootFolder = "backupRoot";
        protected $backupFolders = array("pic", "db", "downloads", "media", "audio");
        protected $lang = "es";
        
	function __construct(&$xt, $lang, $mysqlH)
        {
            $this->moduleName = "backup";
            $this->formName = "backupF";
            $this->lang = $lang[0];
            $this->langPreset = $lang[1];
            $this->mysqlH = $mysqlH;
            
            // check if edit counter exists, if not create it
            if ( !file_exists("edit_data.xml") )
            {
                $xml = new DOMDocument();
                $xml->formatOutput = TRUE;
                $xml_data = $xml->createElement("data");
                
                // genPar
                foreach($this->genPar as $key => $ar)
                {
                    $xml_gp = $xml->createElement( $ar[0] );
                    $xml_gp->nodeValue = $ar[1];
                    $xml_data->appendChild( $xml_gp );
                }

                // intPar
                foreach($this->intPar as $key => $ar)
                {
                    $xml_ip = $xml->createElement( $ar[0] );
                    $xml_ip->nodeValue = $ar[1];
                    $xml_data->appendChild( $xml_ip );
                }
                
                $xml_bk = $xml->createElement( "backups" );
                $xml_data->appendChild( $xml_bk );
                
                $xml->appendChild( $xml_data );

                $xml->save("edit_data.xml");
                chmod("edit_data.xml", 0775);
            } 
            
            // open the edit file
            $this->xt = new XMLTools("edit_data.xml");
            
            // get the general parameters
            foreach($this->genPar as $key => $ar)
	   		{
				$sel = $ar[0];
                $this->gpVal[$ar[0]] = $this->xt->xml->$sel;
	   		}

            // get the general parameters
            foreach($this->intPar as $key => $ar)
	   		{
				$sel = $ar[0];
                $this->ipVal[$ar[0]] = $this->xt->xml->$sel;
	    	}
        }

        
        function doBackup()
        {
            // if not existing create backup folder
            if (!file_exists( $this->dstFolder ))
				mkdir($this->dstFolder, 0775);
            
            $bRoot = $this->dstFolder."/".$this->bRootFolder;
            
            // check if backuproot doesn´t exist, rsync for the first time, make real copy
            if (!file_exists($bRoot))
            {
                mkdir($bRoot, 0775);

                $cmd = "";
                foreach ($this->backupFolders as $folder)
                    $cmd .= "rsync -a $folder ".getcwd()."/$bRoot/$folder > /dev/null &";
                exec($cmd);
                
                // rsync config.php
                $cmd = "rsync -a config.php ".getcwd()."/$bRoot/config.php > /dev/null &";
                exec($cmd);
            }

            // get actual backup nr
            $volNr = ($this->ipVal['backupnr'] + 1) % $this->gpVal['nrbackups'];
            $this->xt->xml->backupnr = $volNr;

            $dst = $this->dstFolder."/backup.".$volNr;
            $date = date("c");
            
            // check if actual backup directory exists, if not create it
            if (!file_exists( $dst ))
            {
                mkdir($dst, 0775);
                
                // rsync create Hardlinks
                $cmd = "";
                foreach ($this->backupFolders as $folder)
                {
                    $cmd .= "rsync -a --link-dest=".getcwd()."/$bRoot/$folder $folder/ $dst/$folder  > /dev/null &";
                }
                
                exec($cmd);
                
                // check if a xml entry exists
                $newNode = $this->xt->xml->backups->addChild("bentry", $date);
                $newNode->addAttribute("nr", $volNr);                
                $newNode->addAttribute("path", $dst);
                
            } else {
                
                // if directory exists, rsync bRoot to this directory and delete old files
                
                // CHECKEN!!!!
                $cmd = "rsync -aH --delete $dst/ $bRoot > /dev/null &";
                exec($cmd);
                
                // rsync the directory to actual state and delete old files
                $cmd = "";
                foreach ($this->backupFolders as $folder)
                    $cmd .= "rsync -aH --delete --link-dest=".getcwd()."/$bRoot/$folder $folder/ $dst/$folder > /dev/null & ";
                exec($cmd);
                
                // rsync the config file
                $cmd .= "rsync -aH --link-dest=".getcwd()."/$bRoot/config.php config.php $dst/config.php > /dev/null & ";
                exec($cmd);
                
                // update database entry
                $node = $this->xt->xml->xpath("backups/bentry[@nr='".$volNr."']");
                if ( sizeof($node) > 0 )
                {
                    $node[0][0] = $date;
                    $node[0]->attributes()->path = $dst;
                }
            }
            
            $this->xt->save();
        }
        
        
		function printForm(&$cont, &$keys, &$sel, $scroll)
        {
            $link = 'editor.php?mod=backup&lang='.$this->lang;
            
			$cont['body'] .= '
			<div class="rightCol">
			
			<!-- Formular -->

			<form name="'.$this->formName.'" class="main" onsubmit="document.forms[0].action+= (\'&scroll=\'+document.body.scrollTop);" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">
			';
            
            $cont['body'] .= '<h2>Backup Manager</h2>';
            
            $cont['body'] .= '<input type="submit" name="subm" value="" style="position:absolute;visibility:hidden;">';
            
            $cont['body'] .= '<div class="highL highLvl0">';

            foreach($this->genPar as $key => $ar)
            {
                $cont['body'] .= '<div class="keyBlock0_b">';
                $cont['body'] .= '<div class="label">'.$ar[2].'</div>';
                $cont['body'] .= '<div class="picAndForm fullrow"><input name="change_'.$ar[0].'" size="3" maxlength="3" value="'.$this->gpVal[ $ar[0] ].'" type="text"></div>';
                $cont['body'] .= '</div>';
            }
            
            $cont['body'] .= '</div>';

            $cont['body'] .= '<div class="highL highLvl1">';
            $cont['body'] .= '<br><b>Active backups</b>';
            
            $backups = $this->xt->xml->backups;
            if ( sizeof($backups) > 0 ) $backups = $backups[0];
            
            foreach($backups as $key => $ar)
            {
                $cont['body'] .= '<div class="picAndForm fullrow">'.$ar.'&nbsp;&nbsp;&nbsp;';
                $cont['body'] .= "<button onclick='var win=window.open(\"".$GLOBALS['root']."index.php?lang=$this->lang&path=".$ar['path']."\", \"_new\");win.focus();'>Preview</button>";
                $cont['body'] .= "<button onclick='var con=confirm(\"Now Restoring Backup. This can´t be undone. Are you sure, you want to continue?\");if (con){document.forms[0].action+=(\"&mod=".$this->moduleName."&confirm=1&path=".$ar['path']."\");document.forms[0].submit();}'>Restore</button>";
                $cont['body'] .= '</div>';
                
            }
            $cont['body'] .= '</div>';

			$cont['body'] .= '</form>
            </div>
			';
        }
        
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {
            foreach($this->genPar as $key => $ar)
                if ( isset($post['change_'.$ar[0]]) )
                    $this->xt->xml->$ar[0] = $post['change_'.$ar[0]];
            
            if (isset($_GET['confirm']))
            {
                print "Now Restoring Backup!<br>";
                
                foreach ($this->backupFolders as $folder)
                    $cmd = "cp -r ".$_GET['path']."/".$folder." .";
                    exec($cmd);
                
                print "Backup successfully restored!";
            }
            
            $this->xt->save();
        }
        
        
        function countEditNr()
        {
            // add one to the edit count
            $editNr = (int)$this->xt->xml->editcount;
            
            if ($editNr >= (int)$this->gpVal['saveintrv'])
            {
                $this->xt->xml->editcount = 0;
                $this->doBackup();
            } else {
                $this->xt->xml->editcount = $editNr + 1;
            }
            $this->xt->save();
        }
    }
?>