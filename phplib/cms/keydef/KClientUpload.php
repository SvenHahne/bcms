<?php
    
    class KClientUpload extends KeyDef
    {
        protected $butLabl;
        protected $client;
        protected $dstRoot;
        protected $endingMap;
        protected $fields;
        protected $formName;
        protected $replace;
        protected $search;
        protected $modified;
        
        public function __construct()
        {
            $this->xmlName = "clientupload";
            $this->htmlName['de'] = "User Upload";
            $this->htmlName['es'] = "Upload Usuario";
            $this->htmlName['en'] = "User upload";
            $this->type = "file";
            $this->postId = "clientupload";
            $this->args = array("gridStyle", "lastChanged");
            $this->argsStdVal = array("", date("Y-m-d G:i:s"));
            $this->argsKeyDef = array("KEDropDown", "");
            $this->argsVals = array(array("col1", "col2", "col3", "col4"));
            $this->argsShow = array(true);
            $this->init();
            $this->uploadPath = "upload";
            $this->uploadType = "*";
            $this->isUplType = TRUE;
            $this->isMultType = TRUE;
            $this->noTextField = TRUE;
            $this->usesThumbs = TRUE;
            
            $this->formName = "upload";
            
            // wenn pdf kopier das pdflogo.jpg und mach einen eintrag
            $this->endingMap = array(array ("pdf", "pic/", "PDF_Logo.jpg"),
                                     array ("doc", "pic/", "word.jpg"),
                                     array ("xls", "pic/", "excel.png"));
            
            $this->subArgs = array("thumb", "date", "descr");
            $this->subArgsKeyDef = array("", "", "");
            $this->subArgsStdVal = array("", date("F j, Y, g:i a"), "");
        }
        
        
        function init()
        {
            $this->headLabel['de'] = "User Upload";
            $this->headLabel['es'] = "User Upload";
            $this->headLabel['en'] = "User Upload";
            
            $this->butLabl['de'] = array("UPLOAD");
            $this->butLabl['es'] = array("UPLOAD");
            $this->butLabl['en'] = array("UPLOAD");
            
            $this->search  = array ('ä',  'ö',  'ü',  'Ä', 'Ö', 'Ü', 'ß',  'ø', 'é', 'Á', ' ', '–',
                                    '“', '”', '/', '(', ')', '&', '%', '!', '"', '"', 'ñ', 'Ñ', 'í',
                                    'á', 'Á', 'Í', 'Ó', 'ó', 'É', 'é');
            $this->replace = array ('ae', 'oe', 'ue', 'Ae','Oe','Ue','sz', 'o', 'e', 'a', '',  '-',
                                    '"', '"', '',  '',  '',  '',  '',  '', '', '', 'n', 'N', 'i',
                                    'a', 'A', 'I', 'O', 'o', 'E', 'e');
        }
        
        
        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {}
        
        
        function addToEditor(&$gPar, &$cont)
        {
            $gPar->inpArgClass = "inp2";
            $df = new KEMultiPic($this, $cont, $gPar);
            $gPar->inpArgClass = "inp";
        }
        
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);

            $modified = false;
            
            if ( !isset($GLOBALS["clientUpload"]) || $GLOBALS["clientUpload"] == false )
            {
                $head['jqDocReady'] .= '
                $("button.clientUploadSubmit").click(function() {  $("input[type=file]").click(); });
                ';
                
                $GLOBALS["clientUpload"] = true;
            }
            
            if ( isset($_FILES['upload']) )
            {
                $modified = true;
                $this->proc_upload($_FILES['upload'], $dPar);
            }
            
            // update comments
            $argNodes = $dPar->xt->xpath->query($dPar->xmlStr."/".$this->xmlName."/".$this->type);
            
            foreach($_POST as $key => $val)
            {
                $keyar = explode("_", $key);
                
                // create other arguments, skip the first argument (image)
                for ($i=2;$i<sizeof($this->subArgs);$i++)
                    if ($keyar[0] == $this->subArgs[$i])
                    {
                        $arg = $argNodes->item($keyar[1])->attributes->item($i);
                        $arg->nodeValue = $val;
                        $this->modified = true;
                    }
                
                // remove entry if remove set
                if ($keyar[0] == "remove")
                {
                    // unlink thumbnail
                    $thumbPath = $this->dstRoot."/".$argNodes->item($keyar[1])->getAttribute("image");
                    unlink($thumbPath);

                    // unlink data
                    $path = $this->dstRoot."/".$argNodes->item($keyar[1])->nodeValue;
                    unlink($path);
                    
                    // remove node
                    $arg = $argNodes->item($keyar[1])->parentNode->removeChild($argNodes->item($keyar[1]));
                    
                    $modified = true;
                }
            }
            
            $this->getClient($dPar);
            
            // get the user, calculate the difference between the time of the
            // last change and the actual time, if higher than interval, send a notification email
            if ($modified == true && isset($GLOBALS["xmlClientLvl"]))
            {
                $lastChng = $dPar->xt->xpath->query($dPar->xmlStr."/".$this->xmlName."/@lastChanged");
                if ($lastChng->length > 0) $lastChng = $lastChng->item(0);

                if ((string)$lastChng->nodeValue != date("Y-m-d G:i:s"))
                {
                    $date1 = new DateTime((string)$lastChng->nodeValue);
                    $date2 = new DateTime(date("Y-m-d G:i:s"));
                    $interval = $date1->diff($date2);
                    $diffAbs = $interval->i + ($interval->h * 60) + ($interval->d * 1440) + ($interval->m * 43200) + ($interval->y * 518400);
                    
                    // if update notify required send it
                    if ( $diffAbs >= $GLOBALS["notifyIntrv"] )
                    {
                        $lastChng->nodeValue = date("Y-m-d G:i:s");

                        // get users of this project
                        $contacts = $dPar->xt->xpath->query($dPar->xmlStr."/projectinfo/contacto");
                        $projName = $dPar->xt->xpath->query($dPar->xmlStr."/projectinfo/".$dPar->lang);
                        if ($projName->length > 0) $projName = (string) $projName->item(0)->nodeValue;
                        
                        foreach($contacts as $usr)
                        {
                            $usrId = array_search($usr->getAttribute("name"), array_column($dPar->clientUsers, 'name'));
                            $inf = $dPar->clientUsers[$usrId];
                            
                            $sendM = new BSendMail($inf['email'],
                                                   "info@".$GLOBALS["siteName"],
                                                   $inf['email'],
                                                   "change notify user: ".$this->client,
                                                   "El cliente ".$this->client." ha modificado el contenido del proyecto: ".$projName.".");
                            $sendM->setSmtpInfo($GLOBALS["smtpHost"], $GLOBALS["smtpPort"], true, $GLOBALS["smtpUser"], $GLOBALS["smtpPass"]);
                            $sendM->send(false);
                        }
                    }
                }
            }
            
            $dPar->xt->saveDOM();
            $dPar->xt->reload();
        }
        
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            // reload xmlItem, for making shure, that the new last changes will be displayed
            $xItem = $dPar->xt->xml->xpath($dPar->xmlStr."/".$this->xmlName);
            $xItem = $xItem[0];

            $body .= "<h3 class='projectInf'>".$this->headLabel[$dPar->lang]."</h3><br>";
            
            $body .= "<form class='clientUpload' id='clientUpload' name='".$this->formName."' action='".$dPar->link."' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
            
            // hidden input field, hier die levels hinzufügen
            $body .= "<input  class='fileselect' type='file' id='file1' name='upload[]' style='display:none' multiple=''></input>";
            $body .= "<button class='clientUploadSubmit' type='button'>".$this->butLabl[$dPar->lang][0]."</button>";
            
            $body .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>';
            
            $this->dstRoot = $this->getClientFolder($dPar);
            
            // print content
            $type = $this->type;
            $ind = 0;
            foreach($xItem->$type as $key => $val)
            {
                $body .= "<div class='clientUplCont'>";
                $body .= "<div class='clientUplRow'>";
                
                $body .= "<div class='clientUplCell clientUplCellImg'>";
                $body .= "<a href='".$this->dstRoot."/".$val."'><img class='clientUplImg' src='".$this->dstRoot."/".$val[ $this->subArgs[0] ]."'></a>";
                $body .= "</div>";

                // filename and comment
                $body .= "<div class='clientUplCell clientUplCellLabel'><b><a href='".$this->dstRoot."/".$val."'>$val</a></b><br>comentario: </div><div class='clientUplCell clientUplCellCom'>";
                $body .= "<textarea name='".$this->subArgs[2]."_".$ind."' class='clientUplComment'>".$val[ $this->subArgs[2] ]."</textarea>";
                $body .= "</div>";
                
                // hook and trash
                $body .= "<div class='clientUplCell clientUplCellButs'>";
                $body .= "<input type='submit' class='hook but' name='subm' value='' style='margin-bottom:3px;'>";
                //$body .= "<input type='submit' class='but trash' name='remove_".$ind."' value=''>";
                $body .= "</div>";
                
                $body .= "<div class='clientUplCell clientUplCellRight'>".$val[ $this->subArgs[1] ]."</div>";
                
                $body .= "</div>";
                $body .= "</div>";
                
                $ind++;
            }
            
            $body .= "</form>";
        }
        
        
        function proc_upload($_files, &$dPar)
        {
            $files = array();
            $files['name'] = array();
            $files['tmp_name'] = array();
			 
            // get client from the user level
            $this->dstRoot = $this->getClientFolder($dPar);

            // wenn es nur eine datei ist, mach ein array, damit die handhabung diesselbe ist
            // wie bei mehreren dateien
            foreach ( $_FILES as $key => $value )
            {
                if ( !is_array( $value['name'] )  )
                {
                    if ( $value['name'] != "" )
                    {
                        array_push( $files['name'], $value['name'] );
                        array_push( $files['tmp_name'], $value['tmp_name'] );
                    }
                } else
                {
                    foreach( $value['name'] as $name ) if ( $name != "" ) array_push( $files['name'], $name );
                    foreach( $value['tmp_name'] as $name ) if ( $name != "" ) array_push( $files['tmp_name'], $name );
                }
            }

            
            for ($j=0;$j<sizeof( $files['name'] );$j++)
            {
                $res_pfad_str = $this->getUniqueFileName( $files['name'][$j], $this->dstRoot );
            
                $ending = $res_pfad_str[2];
                $pfad_str = $res_pfad_str[1];
                $res_pfad_str = $res_pfad_str[0];
                $thumb_str = $pfad_str;

                // check if file already exists
                if ( !file_exists( $this->dstRoot."/".$res_pfad_str ) )
                {
                    // if file doesn´t exist, check if folders exist, recursively
                    $folders = explode( "/", $this->dstRoot );
                    $checkFold = "";
                    
                    for ($i=0;$i<sizeof($folders);$i++)
                    {
                        $checkFold .= $folders[$i];
                        if ( !file_exists( $checkFold ) ) mkdir( $checkFold, 0755 );
                        $checkFold .= "/";
                    }
                }
                
                // copy file from temp to destination
                move_uploaded_file( $files['tmp_name'][$j], $this->dstRoot."/".$res_pfad_str );
                chmod( $this->dstRoot."/".$res_pfad_str, 0755 );
                
                
                // get node from xmlTools
                $pNode = $dPar->xt->xpath->query($dPar->xmlStr."/".$this->xmlName);
                $node = $dPar->xt->doc->createElement( $this->type );
                
                // create thumb pic
                $attr = $dPar->xt->doc->createAttribute( $this->subArgs[0] );

                // schau was fuer eine art von datei es war
                // wenn ein jpg hochgeladen wurde mach einen thumbnail und trag den als image ein
                if (strtolower($ending) == "gif" || strtolower($ending) == "jpg" || strtolower($ending) == "png")
                {
                    list($width, $height, $type, $imgAttr) = getimagesize($this->dstRoot."/".$res_pfad_str);
                    
                    $dWidth = 400;
                    smart_resize_image($this->dstRoot."/".$res_pfad_str,
                                       $dWidth,
                                       $height * ($dWidth / $width),
                                       true,
                                       $this->dstRoot."/thmb_".$res_pfad_str,
                                       false);
                    
                    $attr->nodeValue = "thmb_".$res_pfad_str;
                } else
                {
                    // wenn pdf kopier das pdflogo.jpg und mach einen eintrag
                    foreach ( $this->endingMap as $key => $ar )
                        if ( $ending == $ar[0] )
                        {
                            copy( $ar[1].$ar[2], $this->dstRoot."/".$ar[2] );
                            $attr->nodeValue = $ar[2];
                        }
                }
                
                $node->appendChild( $attr );
                
                // create other arguments, skip the first argument (image)
                for ($i=1;$i<sizeof($this->subArgs);$i++)
                {
                    $attr = $dPar->xt->doc->createAttribute( $this->subArgs[$i] );
                    $attr->nodeValue = $this->subArgsStdVal[$i];
                    $node->appendChild( $attr );
                }

                $node->nodeValue = $res_pfad_str;
                $pNode->item(0)->appendChild( $node );

               // trigger_error( print_R($q, 1) );
            }
        }
        
        
        function getUniqueFileName ($inName, $folder)
        {
            $resString = "";
            
            $expl = explode( ".", $inName );
            $ending = $expl[ sizeof($expl)-1 ];
            if ( $ending == "jpeg" || $ending == "JPG" ) $ending = "jpg";
            
            $pfad_str = "";
            for ($k=0;$k<(sizeof($expl)-1);$k++) {
                $pfad_str .= $expl[$k];
            }
            $pfad_str = str_replace($this->search, $this->replace, $pfad_str);
            $pfad_str = substr( $pfad_str, 0, 25 );
            
            $resString = $pfad_str.".".$ending;
            
            if ( file_exists( $folder."/".$resString ) )
            {
                $fCnt = 0;
                while ( file_exists( $folder."/".$pfad_str.$fCnt.".".$ending ) ) {
                    $fCnt++;
                }
                $resString = $pfad_str.$fCnt.".".$ending;
            }
            
            return array($resString, $pfad_str, $ending);
        }
        
        
        function getClient(&$dPar)
        {
            if (isset($GLOBALS["xmlClientLvl"]))
            {
                $str = $dPar->xmlStr;
                $str = explode("/", $str);
                if (sizeof($str) > $str[$GLOBALS["xmlClientLvl"]])
                {
                    $str = $str[$GLOBALS["xmlClientLvl"]];
                    $this->client = $dPar->xt->xpath->query( $str."/@client" );
                    $this->client = $this->client->item(0)->nodeValue;
                    
                    // get user folder
                    $path = $dPar->mysqlH->getClientArgByName($this->client, "folder");
                }
            }
        }
        
        
        function getClientFolder(&$dPar)
        {
            $path = "";
            
            if (isset($GLOBALS["xmlClientLvl"]))
            {
                $str = $dPar->xmlStr;
                $str = explode("/", $str);
                if (sizeof($str) > $str[$GLOBALS["xmlClientLvl"]])
                {
                    $str = $str[$GLOBALS["xmlClientLvl"]];
                    $this->client = $dPar->xt->xpath->query( $str."/@client" );
                    $this->client = $this->client->item(0)->nodeValue;
                    
                    // get user folder
                    $path = $dPar->mysqlH->getClientArgByName($this->client, "folder");
                }
            }
            
            $path = $GLOBALS["clientBaseUrl"].$path."/".$this->uploadPath;
            
            // get levels, extract arguments
            $exp = substr($dPar->curUrl, strpos($dPar->curUrl, "?")+1, strlen($dPar->curUrl) - strpos($dPar->curUrl, "?") -1);
            // remove lang
            $exp = substr($exp, 0, strpos($exp, "&lang"));
            $exp = preg_replace("/&l[0-9]=/", "/", $exp);

            if (isset($GLOBALS["xmlClientLvl"])){
                $post_name = substr($exp, 8, strlen($exp) -8);
            } else {
                $post_name = substr($exp, 3, strlen($exp) -3);
            }
            
            $path .= "/".$post_name;
            
            return $path;
        }
    }
?>