<?php
	
    class BEditorClients extends BEditorModule
    {
		protected $data_folders;
  		protected $mysqlH;
        protected $replace;
        protected $sel_usr = "";
        protected $search;
  		protected $users;
        protected $labels;
        protected $keys;
        protected $clientKey;
      
        function __construct(&$xt, $lang, $mysqlH)
        {
            $this->moduleName = "clients";
            $this->formName = "clients";
            $this->lang = $lang[0];
            $this->langPreset = $lang[1];
            $this->xt = $xt;
            $this->mysqlH = $mysqlH;
           
            $this->search  = array ('ä',  'ö',  'ü',  'Ä', 'Ö', 'Ü', 'ß',  'ø', 'é', 'Á', ' ', '–',
							  '“', '”', '/', '(', ')', '&', '%', '!', '"', '"', 'ñ', 'Ñ', 'í',
                              'á', 'Á', 'Í', 'Ó', 'ó', 'É', 'é', '~');
			$this->replace = array ('ae', 'oe', 'ue', 'Ae','Oe','Ue','sz', 'o', 'e', 'a', '',  '-',
							  '"', '"', '',  '',  '',  '',  '',  '', '', '', 'n', 'N', 'i',
                              'a', 'A', 'I', 'O', 'o', 'E', 'e', '');
            
            $this->labels = array();
            $this->labels['editUsr'] = array();
            $this->labels['editUsr']['de'] = "Klient:"; $this->labels['editUsr']['en'] = "Client:"; $this->labels['editUsr']['es'] = "Cliente:";
            $this->labels['addUsr'] = array();
            $this->labels['addUsr']['de'] = "hinzufuegen:"; $this->labels['addUsr']['en'] = "Add Client:"; $this->labels['addUsr']['es'] = "Añadir cliente:";
            $this->labels['allUsr'] = array();
            $this->labels['allUsr']['de'] = "Alle Klienten"; $this->labels['allUsr']['en'] = "All Clients"; $this->labels['allUsr']['es'] = "Todos clientes";
            $this->labels['name'] = array();
            $this->labels['name']['de'] = "Name"; $this->labels['name']['en'] = "Name:"; $this->labels['name']['es'] = "Nombre:";
            $this->labels['passw'] = array();
            $this->labels['passw']['de'] = "Passwort:&nbsp;&nbsp;&nbsp;"; $this->labels['passw']['en'] = "Password:&nbsp;&nbsp;&nbsp;"; $this->labels['passw']['es'] = "Clave:&nbsp;&nbsp;&nbsp;";
            
            // get all data folders
            $this->data_folders = $this->getDataPaths();
            
            $this->clientKey = new KClient();
		}
        
        
        function addHeadEditor(&$cont)
		{}
        
        
		function printForm(&$cont, &$keys, &$sel, $scroll)
        {
			$link = 'editor.php?mod='.$this->moduleName.'&lang='.$this->lang;
            
            // select formular
            $cont['body'] .= '<div class="rightCol">
            <form name="'.$this->formName.'" class="main" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">
            ';

            $cont['body'] .= '<h2>Client Manager</h2>';
			$cont['body'] .= '<div class="highL highLvl0">';

            // invisible submit button for submit by return
			$cont['body'] .= '<input type="submit" name="newurl " value="" style="position:absolute;visibility:hidden;">';
            
            // new user
            $cont['body'] .= '<div class="label">'.$this->labels['addUsr'][$this->lang].'</div>';
            $cont['body'] .= '<div class="picAndForm fullrow">';
            $cont['body'] .= '<input type="text" name="newstrname" value=""><input type="submit" class="hook but shiftUp2" name="subm" value="">';
            $cont['body'] .= '</div>';
            
			$cont['body'] .= '</div>';
            
            $gPar = new BEditorDrawPar($keys);
            $gPar->argSize = 16;
            $gPar->canRemove = false;
            $gPar->drawTake = false;
            $gPar->inpClass = "inp";
            $gPar->inpArgClass = "inp";
            $gPar->showVal = false;
            
            $ind = 0;
            
            for ($i=0;$i<$this->mysqlH->getNrClients();$i++)
            {
                $args = $this->mysqlH->getClientArgs($i);
                $name = $this->mysqlH->getClientArg($i, 'name');
                $users = $this->mysqlH->getUsersOfClient($name);
                $type = $this->clientKey->type;

                // hand over clients as simplexmlelement
                $gPar->val = new SimpleXMLElement('<data></data>');
                foreach($this->clientKey->args as $v)
                    $gPar->val->addAttribute($v, $args[$v]);

                // create subNode List with users
                $gPar->medialist = array();
                foreach($users as $usr)
                {
                    $usrNode = new SimpleXMLElement('<'.$type.'></'.$type.'>');
                    foreach($this->clientKey->subArgs as $v)
                        $usrNode->addAttribute($v, $usr[$v]);
                    array_push($gPar->medialist, $usrNode);
                }
                
                // print clients
                if ($name != $GLOBALS["pubClientName"])
                {
                    $cont['body'] .= '<div class="highL highLvl'.(($ind+1)%2).'">';
                    $cont['body'] .= '<div class="form_head">'.$name.'</div>';

                    $cont['body'] .= '<div style="width:60px;display:inline-block;">Link:</div>';
                    $cont['body'] .= '<div class="plainEntry fullrow margBott10">'.$this->getUsrLink($name).'</div><br>';
                    
                    $gPar->post_name = $name;
                    $this->clientKey->addToEditor($gPar, $cont);

                    $cont['body'] .= '<input type="submit" class="hook but shiftUp2" name="subm" value="">';
                    $cont['body'] .= "<input type='submit' class='trash but shiftUp2' onclick='var con=confirm(\"All Data will be deleted. Are you sure, you want to continue?\");if (con){document.forms[1].action+=(\"&mod=".$this->moduleName."&confirm=".$name."\");document.forms[0].submit();}'></input>";

                    $cont['body'] .= '</div>';

                    $ind++;
                } else {
                    $ind = max($ind -1, 0);
                }
            }
            
            $cont['body'] .= '</form>';
            $cont['body'] .= '</div>';
		}
        
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {
            $postProc = new BEditorPostProc($post, $keys, $this->xt, $lang,
                                            $this->langPreset, $backup, $this->mysqlH);
            
            // add new client
            if ( isset($post['newstrname']) && $post['newstrname'] != "" && isset($GLOBALS["xmlClientLvl"]))
            {
                $newUsrName = str_replace($this->search, $this->replace, $post['newstrname']);
                
                // generate a unique rnd folder name
                $folderName = "u".date("U");
                $r = $this->mysqlH->addClient( array($newUsrName, $this->generate_password(10), $folderName, "usr") );

                // create user specific folders
                if ($r)
                {
                    // create user root folder
                    mkdir($GLOBALS["clientBaseUrl"].$folderName, 0775);
                    
                    foreach($this->data_folders as $fold)
                        if (!file_exists($GLOBALS["clientBaseUrl"].$folderName."/".$fold))
                            mkdir ($GLOBALS["clientBaseUrl"].$folderName."/".$fold, 0775);
                }

                // add entry in the data.xml
                $fo = new BFormObj("l".$GLOBALS["xmlClientLvl"], $value, $keys, $this->xt, $this->mysqlH );
                $fo->ins = "newEntry";
                $fo->keyType = "l".$GLOBALS["xmlClientLvl"];
                $fo->actKey = new KLevel();
       
                for ( $i=0;$i<sizeof( $keys['base'.$GLOBALS["xmlClientLvl"]] );$i++ )
                {
                    if ( $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->xmlName == "name" )
                    {
                        $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->initVal = array();
                        $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->initVal['de'] = $newUsrName;
                        $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->initVal['en'] = $newUsrName;
                        $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->initVal['es'] = $newUsrName;
                        
                    } else if ( $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->xmlName == "@client" )
                    {
                        $keys['base'.$GLOBALS["xmlClientLvl"]][$i]->initVal = $newUsrName;
                    }
                }

                $postProc->addNewHighLvl( $fo, $post );
                $this->xt->saveDOM();
            }
            
            
            $clients = $this->mysqlH->getClients();
            for ($i=0;$i<sizeof($clients);$i++)
            {
                // check if new user has to be added
                if ( isset($post['newMultKeyEntry_'.$clients[$i]['name']])
                    && $this->mysqlH->clientExists( $clients[$i]['name'] ))
                    $this->mysqlH->addNewUserForClient($clients[$i]['name']);
                
                // remove client
                $users = $this->mysqlH->getUsersOfClient($clients[$i]['name']);
                for ($j=0;$j<sizeof($users);$j++)
                    if ( isset($post['remove_'.$clients[$i]['name'].'_'.$j]) )
                        $this->mysqlH->delUserOfClient($clients[$i]['name'], $post['change_'.$clients[$i]['name'].'_'.$j.'_userId']);
            }
            
            
            // select user
            if ( isset($_GET['changeusr']) && $_GET['changeusr'] != "" )
                $this->sel_usr = $_GET['changeusr'];
            
            
            // update arguments
            foreach ( $post as $key => $value )
            {
                $keyar = explode("_", $key);
                
                if ( $keyar[0] == "change" && $this->mysqlH->clientExists($keyar[1]))
                {
                    if (sizeof($keyar) == 3)
                    {
                        for($i=0;$i<sizeof($this->clientKey->args);$i++)
                        {
                            if ($this->clientKey->args[$i] == "name" && $keyar[2] == $this->clientKey->args[$i])
                            {
                                $newUsrName = str_replace($this->search, $this->replace, $value);
                                $this->mysqlH->updateByName('name', $keyar[1], $newUsrName);
                                
                                // update top level name in database
                                for($i=0;$i<sizeof($keys['langs']);$i++)
                                {
                                    $xpq = $this->xt->xpath->query( "//name/".$keys['langs'][$i]."[text()='".$keyar[1]."']" );
                                    if ($xpq->length >0)
                                        $xpq->item(0)->nodeValue = $newUsrName;
                                }
                                
                                // update client arguments in database
                                for($i=0;$i<$keys['nrLevels'];$i++)
                                {
                                    $xpq = $this->xt->xpath->query( "//l".$i."[@client='".$keyar[1]."']" );
                                    for($j=0;$j<$xpq->length;$j++)
                                        $xpq->item($j)->setAttribute( "client", $newUsrName );
                                }
                                
                                $this->xt->saveDom();
                            } else
                            {
                                if ($keyar[2] == $this->clientKey->args[$i])
                                    $this->mysqlH->updateByName($this->clientKey->args[$i], $keyar[1], $value);
                            }
                        }
                    } elseif (sizeof($keyar) == 4)
                    {
                        for($i=0;$i<sizeof($this->clientKey->subArgs);$i++)
                            if ($keyar[3] == $this->clientKey->subArgs[$i])
                                $this->mysqlH->updateUserArgByClientNameAndUserId($keyar[1],
                                                                                  $post['change_'.$keyar[1].'_'.$keyar[2].'_userId'],
                                                                                  $keyar[3],
                                                                                  $value);
                    }
                }
            }

            
            // delete client
            if ( isset($_GET['confirm']) && $this->mysqlH->clientExists($_GET['confirm']) )
            {
                $folderName = $this->mysqlH->getClientArgByName($_GET['confirm'], "folder");
                $r = $this->mysqlH->delClientByName($_GET['confirm']);
                
                if ($r)
                    if (file_exists($GLOBALS["clientBaseUrl"].$folderName))
                        deletePath($GLOBALS["clientBaseUrl"].$folderName);
                
                // remove the node
                $xpq = $this->xt->xpath->query( "l".$GLOBALS["xmlClientLvl"]."[@client='".$_GET['confirm']."']" );
                $thisNode = $xpq->item(0);
                $thisNode->parentNode->removeChild( $thisNode );
                $this->xt->saveDOM();

                unset($_GET['confirm']);
            }
            
            
            // reload users
            $q = $this->mysqlH->reloadClients();
        }
        
        
        function generate_password( $length = 8 )
        {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $password = substr( str_shuffle( $chars ), 0, $length );
            return $password;
        }
        
        
        function getUsrLink($name)
        {
            $cUrl = $this->curPageURL();
            $sp = explode("editor", $cUrl);
            return $sp[0].$name;
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
            
            usort($dataPaths, function ($a, $b) {
                $depth_a = substr_count($a, DIRECTORY_SEPARATOR);
                $depth_b = substr_count($b, DIRECTORY_SEPARATOR);
                if ($depth_a == $depth_b) return 0;
                // If depth_a is smaller than depth_b, return -1; otherwise return 1
                return ($depth_a < $depth_b) ? -1 : 1;
            });
            
            return $dataPaths;
        }
    }
?>