<?php
    include_once ('./config_globals.php');
    include_once ('BCMS.php');
    include_once ('BUtilityFunctions.php');
    include_once ('BEditorDrawPar.php');
    include_once ('BLoadKeyDefs.php');

    class BCMSEditor extends BCMS
	{		
		public $lang;

		protected $actMod;
        protected $backupMod;
		protected $cont = array();
		protected $getLevel;
		protected $keys;
		protected $langPreset;
		protected $nrLevels;
        protected $mysqlH;
		protected $oldLang;
		protected $scroll;
        protected $stylePath;
		protected $suppLangs = array("de", "en", "es");
		protected $t;
        
        const totWidth = 810;
		const leftCol = 150;

		//function BCMSEditor($_dataPath, $_configPath, $_langPreset, $_stylePath, $_mysqlH)
        function __construct($_dataPath, $_configPath, $_langPreset, $_stylePath, $_mysqlH)
		{
            $this->mysqlH = $_mysqlH;
            
            // load superclass
            include_once('./phplib/cms/EditorModules/BEditorModule.php');
            
            // include_once all modules in the folder EditorModules without subfolders
            $h = opendir('./phplib/cms/EditorModules'); //Open the current directory
            while (false !== ($entry = readdir($h)))
                if ($entry != '.' && $entry != '..' && $entry != '.DS_Store')
                    if (!is_dir('./phplib/cms/EditorModules/'.$entry) && $entry != 'BEditorModule.php')
                        include_once('EditorModules/'.$entry);
            
            
            // here all the classes of available keys are loaded
            include_once ( 'BLoadKeyDefs.php' );
			include_once ( $_configPath );
			include_once ( 'stylemapping.php' );
            
			$this->nrLevels = $keys['nrLevels'];
			$this->keys = &$keys;
			$this->oldLang = "";
			$this->langPreset = $_langPreset;
			$this->stylePath = $_stylePath;
            
			$this->cont['head'] = "";
			$this->cont['js'] = "";
			$this->cont['jquery'] = "";
			$this->cont['body'] = "";
			$this->cont['footer'] = "";
            
			// open database as DomDocument
			$this->xt = new XMLTools($_dataPath);

			$this->getLevel = array();
            $this->procGets();
            $this->mysqlH->actClient = $GLOBALS["pubClientName"];

            // get actual user
            if (isset($GLOBALS["xmlClientLvl"]) && $_SERVER['QUERY_STRING'] != "" && !isset($_GET['mod']))
            {
                $exp = explode("&", $_SERVER['QUERY_STRING']);
                $exp = array_values(array_filter($exp));
                
                if (sizeof($exp) > $GLOBALS["xmlClientLvl"])
                {
                    $exp = $exp[$GLOBALS["xmlClientLvl"]];
                    $exp = explode("=", $exp);
                    $q = $this->xt->xml->xpath("l".$GLOBALS["xmlClientLvl"]."[@short='".$exp[1]."']/@client");
                    
                    if (sizeof($q)>0)
                        $this->mysqlH->actClient = $q[0];
                }
            }

            if ( isset( $_POST['changeLang'] ) ) $this->lang = $_POST['changeLang'];
			
			// reload database
			$this->xt->reload();

            // load backup module
            $this->backupMod = new BEditorBackup($this->xt, array($this->lang, $_langPreset), $this->mysqlH, $this->keys);

            // load the actual module,
            if ( isset($_GET['mod']) )
            {
                if ( $_GET['mod'] != "backup" )
                {
                    $modName = 'BEditor'.ucfirst($_GET['mod']);
                    $this->actMod = new $modName($this->xt, array($this->lang, $_langPreset), $this->mysqlH, $this->keys);
                } else {
                    $this->actMod = $this->backupMod;
                }
            } else {
                $this->actMod = new BEditorContent($this->xt, array($this->lang, $_langPreset), $this->mysqlH, $this->keys);
            }
                        
            // let the loaded module process the post
            if ( $this->oldLang == "" ) $l = $this->lang; else $l = $this->oldLang;
            $this->actMod->postProc($_POST, $this->keys, $l, $this->backupMod);
            
            // reload xml
            $this->xt->reload();
            
            // compose html
			$this->makeHead($this->cont['head']);                        
            $this->actMod->addHeadEditor($this->cont);
            
            $this->makeBody($this->cont);
            $this->printMenu($this->cont, $keys, $levelNames, $this->getLevel);
            $this->actMod->printForm($this->cont, $keys, $this->getLevel, $this->scroll);

            print $this->cont['head'];
            $this->closeHead($this->cont['head']);
            
            print $this->cont['body'];
            $this->closeBody();
            
			print $this->cont['footer'];
		}
		
		
		private function procGets()
		{
			if ( isset($_GET['lang']) )
            {
				$this->lang = $_GET['lang']; 
			} else {
				$this->lang = $this->langPreset; 
			}
			if ( isset($_GET['oldlang']) ) $this->oldLang = $_GET['oldlang'];

			// check if any levels were selected
			for ($i=0;$i<$this->nrLevels;$i++) 
			{
				if ( isset($_GET[ 'l'.$i ]) )
				{ 
					$this->getLevel[$i] = $_GET[ 'l'.$i ];
				} else {
					$this->getLevel[$i] = false;
				}
			}
			
			// get the last scroll position from where the form was sent
			if ( isset($_GET['scroll']) ) { $this->scroll = $_GET['scroll']; } else { $this->scroll = 0; }
		}
        
        
		function makeHead(&$head)
		{
			$head .= '<!DOCTYPE html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<script src="'.$GLOBALS['root'].'js/jquery-1.8.2.js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="'.$this->stylePath.'" />
			';
		}
        

        function closeHead(&$head)
		{
			print '</head>
			<script type="text/javascript">'.$this->cont['js'].'
			$(function() {'.$this->cont['jquery'].'});
			</script>';
		}
        
        
		function makeBody(&$cont)
		{
			$cont['body'] .= '<body style="background-image:none;" onload="document.documentElement.scrollTop='.$this->scroll.'"><br>
            <div class="cent outerCnt">
            <div><h1>BCMS Editor</h1></div>
			';
		}
        
        
        function closeBody()
		{
            print '</div></body>';
		}
		
		
		function menuLevelCb(&$cont, $lvl, &$sel, $xmlStr, $linkStr)
		{
			$levelNames = array();
			$levelNames[$lvl] = array();
			$levelNames[$lvl]['long'] = (array) $this->xt->xml->xpath( $xmlStr."/name/".$this->lang );
			$levelNames[$lvl]['short'] = (array) $this->xt->xml->xpath( $xmlStr."/@short" );
            
            $tlNames = array();
            $tlNames['de'] = "Inhalt";
            $tlNames['en'] = "Content";
            $tlNames['es'] = "Contenido";
            
			// print all the lowest levels
			for ($j=0; $j<sizeof($levelNames[$lvl]['long']); $j++)
			{
				if ( $j==0 )
				{
					$cont['body'] .= '<ul style="margin:0px;font-size:0.7em;';
					if ( $lvl > 0 ) {
						$cont['body'] .= 'list-style-type:disc;';
					} else {
						$cont['body'] .= 'list-style-type:none;';
					}
					$cont['body'] .= '">';
                    if ($lvl == 0) $cont['body'] .= '<h3>'.$tlNames[$this->lang].'</h3>';
                }

				$cont['body'] .= '<li ';
                
                // set different style if is selected
                $selected = false;
                if (isset($sel[$lvl+1]))
                {
                    if ($levelNames[$lvl]['short'][$j] == $sel[$lvl] && $sel[$lvl+1] == "")
                        $selected = true;
                } else {
                    if ($levelNames[$lvl]['short'][$j] == $sel[$lvl])
                        $selected = true;
                }
                
                if ($selected) $cont['body'] .= 'class="selected" ';

				$cont['body'] .= 'style="margin-left:'.($lvl *8).'px;">';

                $cont['body'] .= '<div class="menuEntrTable">';
                $cont['body'] .= '<a href="'.$linkStr.$levelNames[$lvl]['short'][$j].'&lang='.$this->lang.'" target="_self">';
                $cont['body'] .= '<div class="menuEntrTableEntrL">';
                
				if ( $levelNames[$lvl]['long'][$j] != "" )
				{
					$cont['body'] .= ucfirst( $levelNames[$lvl]['long'][$j] );
				} else {
					$cont['body'] .= "-untitled-";
				}
				
				$cont['body'] .= '</div></a>';
                
                // if selected draw arrows and trash buttons
                if ($selected)
                {
                    $post_name = "";
                    for($i=0;$i<sizeof($sel);$i++)
                    {
                        $post_name .= $sel[$i];
                        if ($i!= sizeof($sel)-1) $post_name .= "_";
                    }
                    
                    $cont['body'] .= '<div class="menuEntrTableEntrR">';
                    if ( ( !isset($GLOBALS["xmlClientLvl"]) && $lvl != 2 ) || (isset($GLOBALS["xmlClientLvl"]) && $lvl != 3 ) )
                        $cont['body'] .= '<input class="but2 plus2" type="submit" name="newEntry_'.$post_name.'_level" value="">';
                    $cont['body'] .= '<input class="but2 up2" type="submit" name="shiftUp_'.$post_name.'_name_" value="">';
                    $cont['body'] .= '<input class="but2 down2" type="submit" name="shiftDown_'.$post_name.'_name_" value="">';
                    $cont['body'] .= '<input class="but2 trash2" type="submit" name="remove_'.$post_name.'_level_" value="">';
                    $cont['body'] .= '</div>';

                }
                
                $cont['body'] .= '</div>';
                $cont['body'] .= '</li>';
                
				// if level selected show sublevels
				if ( $levelNames[$lvl]['short'][$j] == $sel[$lvl] )
					$this->menuLevelCb(
									   $cont,
									   $lvl+1,
									   $sel,
									   $xmlStr."[@short='".$levelNames[$lvl]['short'][$j]."']/l".($lvl+1),
									   $linkStr.$levelNames[$lvl]['short'][$j]."&l".($lvl+1)."="
									   );
				
				if ( $j == sizeof($levelNames[$lvl]['long'])-1 ) $cont['body'] .= "</ul>";
			}
		}
        
		
		function printMenu(&$cont, &$keys, &$levelNames, &$sel)
		{
			$cont['body'] .= '<div class="leftCol">
			<h2>&nbsp;</h2>';
			
			$cont['body'] .= '<ul><a class="topMen" href="editor.php?mod=backup&lang='.$this->lang.'"><li class="topMen">Backup Manager</li></a>';
            $cont['body'] .= '<a class="topMen" href="editor.php?mod=clients&lang='.$this->lang.'"><li class="topMen">Client Manager</li></a>';
			$cont['body'] .= '<a class="topMen" href="editor.php?lang='.$this->lang.'"><li class="topMen">Content Editor</li></a>';
			$cont['body'] .= '<a class="topMen" href="editor.php?mod=rss&lang='.$this->lang.'"><li class="topMen">Rss Editor</li></a>';
            $cont['body'] .= '<a class="topMen" href="editor.php?mod=search&lang='.$this->lang.'"><li class="topMen">Search Engine</li></a>';
			$cont['body'] .= '</ul><hr>';
            
            $xmlStr = "l0";
            $linkStr = "editor.php?l0=";
            $link = $linkStr;
            
            for($i=0;$i<sizeof($sel);$i++)
            {
                if ($sel[$i] != "")
                {
                    $link .= $sel[$i];
                    if ($i != sizeof($sel)-1 && $sel[$i+1] != "" ) $link .= "&l".($i+1)."=";
                }
            }
            $link .= "&lang=".$this->lang;
            
            
            // the menu on the left side works as form
            $cont['body'] .= '<form name="editForm" class="main" onsubmit="document.forms[0].action+= (\'&scroll=\'+document.body.scrollTop);" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">';
            
            $this->menuLevelCb($cont, 0, $sel, $xmlStr, $linkStr);
            
            $cont['body'] .= '</form>';
            
			$cont['body'] .= '</div>
			';
		}
	}
?>