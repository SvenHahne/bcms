<?php
	
    class BEditorUsers extends BEditorModule
    {
		protected $data_folders;
  		protected $mySqlH;
        protected $replace;
        protected $sel_usr = "";
        protected $search;
  		protected $users;
        protected $labels;
        protected $keys;
      
        function __construct(&$xt, $lang, $mySqlH)
        {
            $this->moduleName = "users";
            $this->formName = "users";
            $this->lang = $lang[0];
            $this->langPreset = $lang[1];
            $this->xt = $xt;
            $this->mySqlH = $mySqlH;
           
            $this->search  = array ('ä',  'ö',  'ü',  'Ä', 'Ö', 'Ü', 'ß',  'ø', 'é', 'Á', ' ', '–',
							  '“', '”', '/', '(', ')', '&', '%', '!', '"', '"', 'ñ', 'Ñ', 'í',
                              'á', 'Á', 'Í', 'Ó', 'ó', 'É', 'é', '~');
			$this->replace = array ('ae', 'oe', 'ue', 'Ae','Oe','Ue','sz', 'o', 'e', 'a', '',  '-',
							  '"', '"', '',  '',  '',  '',  '',  '', '', '', 'n', 'N', 'i',
                              'a', 'A', 'I', 'O', 'o', 'E', 'e', '');
            
            $this->labels = array();
            $this->labels['editUsr'] = array();
            $this->labels['editUsr']['de'] = "Benutzer:"; $this->labels['editUsr']['en'] = "User:"; $this->labels['editUsr']['es'] = "Usario:";
            $this->labels['addUsr'] = array();
            $this->labels['addUsr']['de'] = "hinzufuegen:"; $this->labels['addUsr']['en'] = "Add User:"; $this->labels['addUsr']['es'] = "Añadir usario:";
            $this->labels['allUsr'] = array();
            $this->labels['allUsr']['de'] = "Alle Benutzer"; $this->labels['allUsr']['en'] = "All Users"; $this->labels['allUsr']['es'] = "Todos usarios";
            $this->labels['name'] = array();
            $this->labels['name']['de'] = "Name"; $this->labels['name']['en'] = "Name:"; $this->labels['name']['es'] = "Nombre:";
            $this->labels['passw'] = array();
            $this->labels['passw']['de'] = "Passwort:&nbsp;&nbsp;&nbsp;"; $this->labels['passw']['en'] = "Password:&nbsp;&nbsp;&nbsp;"; $this->labels['passw']['es'] = "Clave:&nbsp;&nbsp;&nbsp;";
            
            // get all data folders
            $this->data_folders = $this->getDataPaths();
		}
        
        
        function addHeadEditor(&$cont)
		{}
        
        
		function printForm(&$cont, &$keys, &$sel, $scroll)
        {
			$link = 'editor.php?mod=users&lang='.$this->lang;
            
            // select formular
            $cont['body'] .= '<div class="rightCol">
            <form name="'.$this->formName.'" class="main" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8" onsubmit="var e=document.getElementById(\'usrSel\');document.forms[0].action+=(\'&changeusr=\'+e.selectedIndex);">
            ';
            
            $cont['body'] .= '<h2>User Manager</h2>';

			$cont['body'] .= '<div class="highL highLvl0">';

            // invisible submit button for submit by return
			$cont['body'] .= '<input type="submit" name="newurl " value="" style="position:absolute;visibility:hidden;">';
			
            // show active users
            $cont['body'] .= '<div class="label">'.$this->labels['editUsr'][$this->lang].'</div>';
            $cont['body'] .= '<div class="picAndForm fullrow"><select id="usrSel" name="usr">';
            
            $selNr=0;
            if ( $this->sel_usr != "" ) $selNr = $this->sel_usr;
            
            for ($i=0;$i<$this->mySqlH->getNrUsers();$i++)
            {
                $cont['body'] .=  "<option";
                if ( $i == $selNr ) $cont['body'] .=  " selected";
                $cont['body'] .=  ">".$this->mySqlH->getUserArg($i, 'name')."</option>";
            }

            $cont['body'] .= '</select></div>';
            
            // new user
            $cont['body'] .= '<div class="label">'.$this->labels['addUsr'][$this->lang].'</div>';
            $cont['body'] .= '<div class="picAndForm fullrow"><input type="text" name="newstrname" value=""><input type="submit" class="hook but shiftUp2" name="subm" value=""></div>';
            
			$cont['body'] .= '</div>';
            
            $cont['body'] .= '<div class="highL highLvl1">';
            $cont['body'] .= '<div class="form_head">'.$this->labels['allUsr'][$this->lang].'</div>';
            
            for ($i=0;$i<$this->mySqlH->getNrUsers();$i++)
            {
                $name = $this->mySqlH->getUserArg($i, 'name');
                
                if ($name != "pub")
                {
                    $cont['body'] .= '<div class="label">'.$this->labels['name'][$this->lang].'</div><div class="picAndForm fullrow">';
                    $cont['body'] .= '<input type="text" name="change_usr_'.$i.'" value="'.$name.'"></input>';
                    $cont['body'] .= '&nbsp;&nbsp;&nbsp;'.$this->labels['passw'][$this->lang].'<input type="text" name="change_passw_'.$i.'" value="'.$this->mySqlH->getUserArg($i, 'passw').'">';
                    $cont['body'] .= '<input type="submit" class="hook but shiftUp2" name="subm" value="">';
                    $cont['body'] .= "<input type='submit' class='trash but shiftUp2' onclick='var con=confirm(\"All Data will be deleted. Are you sure, you want to continue?\");if (con){document.forms[1].action+=(\"&mod=".$this->moduleName."&confirm=".$i."\");document.forms[0].submit();}'></input></div>";

                    $cont['body'] .= '<div class="label">Link:</div><div class="plainEntry fullrow margBott10">'.$this->getUsrLink($i).'</div>';
                }
            }
			$cont['body'] .= '</div>';

            $cont['body'] .= '</form>';
            $cont['body'] .= '</div>';
		}
        
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {
            $postProc = new BEditorPostProc($post, $keys, $this->xt, $lang,
                                            $this->langPreset, $backup, $this->mySqlH);

            // add new user
            if ( isset($post['newstrname']) && $post['newstrname'] != "" )
            {
                $newUsrName = str_replace($this->search, $this->replace, $post['newstrname']);
                // generate a unique rnd folder name
                $folderName = "u".date("U");
                $r = $this->mySqlH->addUser($newUsrName, $this->generate_password(10), $folderName);

                // create user specific folders
                if ($r)
                {
                    foreach($this->data_folders as $fold)
                    {
                        if (!file_exists($fold)) mkdir ($fold, 0775);
                        mkdir($fold.DIRECTORY_SEPARATOR.$folderName, 0775);
                    }
                }

                // add entry in the data.xml
                $fo = new BFormObj("l0", $value, $keys, $this->xt);
                $fo->ins = "newEntry";
                $fo->keyType = "l0";
                $fo->actKey = new KLevel();
       
                for ( $i=0;$i<sizeof( $keys['base0'] );$i++ )
                {
                    if ( $keys['base0'][$i]->xmlName == "name" )
                    {
                        $keys['base0'][$i]->initVal = array();
                        $keys['base0'][$i]->initVal['de'] = $newUsrName;
                        $keys['base0'][$i]->initVal['en'] = $newUsrName;
                        $keys['base0'][$i]->initVal['es'] = $newUsrName;
                        
                    } else if ( $keys['base0'][$i]->xmlName == "@owner" )
                    {
                        $keys['base0'][$i]->initVal = $newUsrName;
                    }
                }

                $postProc->addNewHighLvl( $fo, $post );
                $this->xt->saveDOM();
            }

            
            if ( isset($_GET['changeusr']) && $_GET['changeusr'] != "" )
            {
                $this->sel_usr = $_GET['changeusr'];
            }
            
            
            foreach ( $post as $key => $value )
            {
                $keyar = explode("_", $key);
                
                if ( $keyar[0] == "change" )
                {
                    if ($keyar[1] == "usr")
                    {
                        $newUsrName = str_replace($this->search, $this->replace, $value);
                        $this->mySqlH->update('name', $keyar[2], $newUsrName);
                        
                    } else if ($keyar[1] == "passw")
                    {
                        $this->mySqlH->update('passw', $keyar[2], $value);

                    }
                }
            }
            
            if ( isset($_GET['confirm'])  )
            {
                $folderName = $this->mySqlH->getUserArg($_GET['confirm'], "folder");
                $r = $this->mySqlH->delUser($_GET['confirm']);
                if ($r)
                {
                    foreach($this->data_folders as $fold)
                    {
                        if ( $fold != "" )
                        {
                            $dir = $fold.DIRECTORY_SEPARATOR.$folderName;
                            if (file_exists($dir))
                            {
                                $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                                foreach($files as $file) {
                                    if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                                        continue;
                                    }
                                    if ($file->isDir()){
                                        rmdir($file->getRealPath());
                                    } else {
                                        unlink($file->getRealPath());
                                    }
                                }
                            }
                            if ($dir != $this->data_folders) rmdir($dir);
                        }
                    }
                    rmdir($folderName);
                }

                $usrName = $this->mySqlH->getUserArg($_GET['confirm'], "name");

                // remove the node
                $xpq = $this->xt->xpath->query( "l0[@owner='".$usrName."']" );
                $thisNode = $xpq->item(0);
                $thisNode->parentNode->removeChild( $thisNode );
                $this->xt->saveDOM();
            }
            
            // reload users
            $q = $this->mySqlH->reloadUsers();
        }
        
        
        function generate_password( $length = 8 )
        {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $password = substr( str_shuffle( $chars ), 0, $length );
            return $password;
        }
        
        
        function getUsrLink($usrNr)
        {
            $cUrl = $this->curPageURL();
            $sp = explode("editor", $cUrl);
            return $sp[0].$this->mySqlH->getUserArg($usrNr, 'name');
        }

        
        function curPageURL()
		{
			$pageURL = 'http';
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}
        
        
        function getDataPaths()
        {
            $dataPaths = array();
            
            $h = opendir('./phplib/cms/keydef'); //Open the current directory
            while (false !== ($entry = readdir($h)))
                if ($entry != '.' && $entry != '..' && $entry != '.DS_Store')
                    if (!is_dir('./phplib/cms/keydef/'.$entry) && $entry != "KeyDef.php")
                    {
                        $class = explode(".", $entry);
                        $class = $class[0];
                        $inst = new $class();
                        if ( $inst->uploadPath != "" ) array_push($dataPaths, $inst->uploadPath);
                        unset($inst);
                    }
            
            return $dataPaths;
        }
    }
?>