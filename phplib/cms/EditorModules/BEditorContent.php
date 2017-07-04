<?php

    include_once ('ContentModule/BEditorContentDrawEntry.php');

    class BEditorContent extends BEditorModule
    {
        function __construct(&$xt, $lang, $mysqlH)
        {
            $this->moduleName = "content";
            $this->formName = "editForm";
            $this->lang = $lang[0];
            $this->langPreset = $lang[1];
            $this->xt = $xt;
            $this->mysqlH = $mysqlH;
        }
        
        function addHeadEditor(&$cont)
		{}
        
		function printForm(&$cont, &$keys, &$sel, $scroll)
		{
            // print all the entries of the level, ignore the sublevels
            // these will only appear on the left menu
            
			$cont['body'] .= '
			<div class="rightCol">
			
			<!-- Formular -->
			
			<div style="text-align:left;font-family:Arial;font-size:18px;margin-bottom:8px;">
			';
			
			$xmlStr = "/data/";
			$linkStr = "editor.php?";
			$post_name = "";
			$actLvl = -1;
			$isLowest = false;
            
            // get user folder
            
            // when there is nothing selected, choose the first entry of the public user
            if (isset($GLOBALS["xmlClientLvl"]))
            {
                if ($sel[$GLOBALS["xmlClientLvl"]+1] == "")
                {
                    $q = $this->xt->xml->xpath($xmlStr."l".$GLOBALS["xmlClientLvl"]."[@short='".$GLOBALS["xmlPubClientShort"]."']/l".($GLOBALS["xmlClientLvl"]+1)."[1]/@short");
                    if (sizeof($q) > 0)
                        $sel[$GLOBALS["xmlClientLvl"]+1] = $q[0];
                } 
            } else
            {
                if ($sel[0] == "")
                {
                    $q = $this->xt->xml->xpath($xmlStr."l0[1]/@short");
                    if (sizeof($q) > 0)
                        $sel[0] = $q[0];
                }
            }
            
            $checkStr = $xmlStr;

            
            // check if all the entries in $sel do exist
            for ($i=0;$i<sizeof($sel);$i++)
            {
                if ($sel[$i] != "")
                {
                    $checkStr .= "/l".$i."[@short='".$sel[$i]."']";
                    $q = $this->xt->xml->xpath($checkStr);
                    if (sizeof($q) == 0) $sel[$i] = "";
                } else {
                    break;
                }
            }
            
            
            // set the xml data path, get the client of the selected level
            // this refers to the $_GET['sel'] option
			for ($i=0;$i<$keys['nrLevels'];$i++)
			{
				if ( $sel[$i] != "" )
				{
					$xmlStr .= "/l".$i;
					$linkStr .= "&l".$i."=";
                    
					$actLvl++;
					$xmlStr .= "[@short='".$sel[$i]."']";
					if ( $i<$keys['nrLevels'] ) $post_name .= $sel[$i];
                    
					$name = (array) $this->xt->xml->xpath( $xmlStr."/name/".$this->lang );
					$short_name = (array) $this->xt->xml->xpath( $xmlStr."/@short" );

                    // get user folder
                    if ( $i==0 )
                    {
                        $client = $this->xt->xml->xpath( $xmlStr."/@client" );
                        $client = $client[0];
                        //$GLOBALS["clientBaseUrl"] = $GLOBALS["clientBaseUrl"]."/".$this->mysqlH->getClientArgByName($client, "folder")."/";
                    }
                    
                    // print the path to the entry in the database
					if ( sizeof($name) > 0 )
					{
						$cont['body'] .= '<b>'.$name[0].'</b> /
                        ';
					} else {
						$cont['body'] .= '<b>untitled</b>
						';
					}
					
					$linkStr .= $short_name[0];
				}
				if ( $i<($keys['nrLevels']-1) ) $post_name .= "_";
                //if ( $i<$keys['nrLevels'] ) $post_name .= "_";
			}
            
            
            // build the options for the link
			$link = $linkStr;
			if ( $link[strlen($link) -1] != '?' ) $link .= '&';
            $link .= 'lang='.$this->lang;

            // get the entries
            $eintraege = $this->xt->xpath->query( $xmlStr."/*" );
                        
			// print the language buttons
			$this->printLang($cont['body'], $keys, $this->lang, -32);
            
            // ------ begin the form ------
			$cont['body'] .= '</div>
			
			<form id="'.$this->formName.'" name="'.$this->formName.'" class="main" onsubmit="document.getElementById(\''.$this->formName.'\').action+=(\'&scroll=\'+document.documentElement.scrollTop);submit();" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">
			';
            

			$drawEntry = new BEditorContentDrawEntry($this->stylesPath, $this->lang, $keys, $cont, $this->mysqlH);
            
			// parameters for DrawEntry that don´t change per key
			$drawEntry->actLevel = $actLvl;
			$drawEntry->scroll = $scroll;
			$drawEntry->formName = $this->formName;
			$drawEntry->setXt( $this->xt );
			$drawEntry->langPreset = $this->langPreset;
			$drawEntry->curUrl = $this->curPageURL();
            
            $post_name_exp = explode ("_", $post_name);
            
            $sub_post_name = "";
            for ($j=0;$j<sizeof($post_name_exp);$j++)
            {
                $sub_post_name .= $post_name_exp[$j];
                //                if ( $j == $actLvl ) $sub_post_name .= $eintraege->item($i)->getAttribute("short");
                if ( $j != sizeof($post_name_exp)-1 ) $sub_post_name .= "_";
            }
            
            $drawEntry->setPostName( $sub_post_name );
            
            // ------ draw all base keys ------
            
            $cont['body'] .= "<div class='highL highLvl0'>";
            
            // draw the basic keys
            for($j=0;$j<sizeof( $keys['base'.$actLvl] );$j++)
                $drawEntry->draw($keys, 0, $keys['base'.$actLvl][$j], -1, $sel, $xmlStr, 0);
            
            $cont['body'] .= "<hr>";

            // ------ print the "add" drop down menu ------
            
            $cont['body'] .= "<select name='newKeySelect_".$sub_post_name."' id='type' size='1'>";
                
            $cont['body'] .= "<option>".$keys['addEntryDropDownFirstItem'][$this->lang]."</option>";
            for ($j=0;$j<sizeof($keys[$actLvl]);$j++)
                if ( $keys[$actLvl][$j]->htmlName[$this->langPreset] != "Level" )
                    $cont['body'] .= "<option>".$keys[$actLvl][$j]->htmlName[$this->langPreset]."</option>";
            
            $cont['body'] .= "</select>";
                
            $cont['body'] .=  "<input class='but plus shiftUp2' type='submit' name='newKeyEntry_".$sub_post_name."' value=''>";
            
            
            // ------ print entries ------------------------
            
            $multplId = array();
            for ($j=0;$j<sizeof($keys[$actLvl]);$j++)
                $multplId[ $keys[$actLvl][$j]->xmlName ] = 0;
            
            $setspace = true;
            
            $cs = 0;
			for ($i=0;$i<$eintraege->length;$i++)
			{
//                if (isset( $eintraege->item($i)->tagName ) && $eintraege->item($i)->tagName == "name")
//                    $cs -= 1;
                
                // check for basic entries of the specific lvl
                for ($k=0;$k<sizeof( $keys[$actLvl] );$k++)
                {
                    // draw subnodes
                    if ( isset( $eintraege->item($i)->tagName )
                        && $eintraege->item($i)->tagName == $keys[$actLvl][$k]->xmlName )
                    {
                        if ( $setspace ) {
                            $cont['body'] .= "<div style='height:10px;'></div>";
                            $setspace = false;
                        }
 
                        $keyName = $keys[$actLvl][$k]->xmlName;
                        $drawEntry->draw($keys, $i, $keys[$actLvl][$k], $multplId[ $keyName ], $sel, $xmlStr, $cs);
                        
                        $multplId[ $keyName ]++;
                        $cs = ($cs+1) % 2;
                    }
                }
			}
            
			$cont['body'] .= '</form></div>';
		}
        
        function printLang(&$body, &$keys, $lang, $vOffs)
		{
			for ($i=0;$i<sizeof($keys['langs']);$i++)
			{
				$body .= '<div style="display:inline;margin-top:'.$vOffs.'px;">
				<input class="countrLogo" name="changeLang" value="'.$keys['langs'][$i].'" onclick="var path=document.getElementsByName(\''.$this->formName.'\')[0].action.replace(/lang=[a-z]{2}/, \'\');document.forms[0].action=path;document.getElementsByName(\''.$this->formName.'\')[0].action+=(\'lang='.$keys['langs'][$i].'&oldlang='.$lang.'\');document.getElementsByName(\''.$this->formName.'\')[0].submit();" type="image" src="pic/'.$keys['langs'][$i].'.jpg" alt="Absenden" style="';
				if ($lang == $keys['langs'][$i]) $body .= 'border:solid 1px black;';
				$body .= 'height:14px;margin-right:2px;margin-top:5px;">
				';
			}
		}
        
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {
            $postProc = new BEditorPostProc($post, $keys, $this->xt, $lang, $this->langPreset,
                                            $backup, $this->mysqlH);
            $postProc->proc();
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
    }
?>