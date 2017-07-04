<?php
    
    class KLMenu
    {
        public function __construct()
		{}
        
        function addHead(&$head, &$dPar)
        {}
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $is_act = "";

            $lang = (string) $dPar->lang;
            if ( isset($_GET['dstyle']) ) $this->dispStyle = $_GET['dstyle'];
            $dPar->subNavList = array();
            $dPar->subNavList['selLvlShort'] = "";
            
            if (isset($xmlItem['submenu']) && $xmlItem['submenu'] != "")
            {
                // get the level depth of the actual entry
                $menuLevel = sizeof( explode("/", (string)$xmlItem['submenu']) );
                $linkLevel = sizeof( explode("/", (string)$dPar->xmlStr) );

                // get the short of the actual selected entry in the
                // refered menu level
                if ($linkLevel > $menuLevel)
                    $is_act = substr($dPar->xmlStr,
                                     strpos($dPar->xmlStr, "l".$menuLevel) +11,
                                     4);

                // set xpath to refered level                
                $subLvls = $dPar->xt->xml->xpath($xmlItem['submenu']."/l".$menuLevel);

                
                for ($i=0;$i<sizeof($subLvls);$i++)
                {
                    $dPar->subNavList[$i] = array();
                    $dPar->subNavList[$i]['name'] = $subLvls[$i]->name->$lang;
                    $dPar->subNavList[$i]['image'] = $subLvls[$i]->xpath("@image");
                    $dPar->subNavList[$i]['image'] = $dPar->subNavList[$i]['image'][0];
                    $dPar->subNavList[$i]['short'] = $subLvls[$i]->xpath("@short");
                    $dPar->subNavList[$i]['short'] = $dPar->subNavList[$i]['short'][0];
                    $dPar->subNavList[$i]['xmlPath'] = $xmlItem['submenu'];

                    $link = preg_replace("/\[@short|'|\]/i", "", (string)$xmlItem['submenu']);
                    $link = str_replace("/", "&", $link);
                    $dPar->subNavList[$i]['link'] = $GLOBALS['root']."index.php?".$link."&l2=".$dPar->subNavList[$i]['short'];
                    
                    // get the image path of the level image, replace the client level if we are using clients
                    $dPar->subNavList[$i]['imgPath'] = preg_replace("/l[0-9]|\[@short=|'|\]/i", "", (string)$xmlItem['submenu']);
                                        
                    if( isset($GLOBALS["xmlPubClientShort"]) )
                    {
                        $clientFold = getClientFolder($dPar->xt, $xmlItem['submenu'], $dPar->mysqlH)[0];
                        $split = explode("/", $dPar->subNavList[$i]['imgPath']);
                   		$newPath = $clientFold."pic/";
                   		for ($j=1;$j<sizeof($split);$j++)
                   		{
                   			$newPath .= $split[$j];
                   			if($j != sizeof($split) -1) $newPath .= "/";
                  		}
                   		$dPar->subNavList[$i]['imgPath'] = $newPath;
                   		                    	
                    } else {
                    	$dPar->subNavList[$i]['imgPath'] = "pic/".$dPar->subNavList[$i]['imgPath'];
                    }
                    
                    $dPar->subNavList[$i]['imgPath'] = $GLOBALS["root"].$GLOBALS['backupUrl'].$GLOBALS["clientBaseUrl"].$dPar->subNavList[$i]['imgPath'];
                }
                
                $dPar->subNavList['selLvlShort'] = $is_act;
            }
        }
    }
?>