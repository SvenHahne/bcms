<?php
    
    function getActLvlFromXml($path)
    {
        $lvl = -1;
        $str = explode("/", $path);
        $str = explode("[", $str[ sizeof($str)-1 ]);
        $str = explode("l", $str[0]);
        // add one to the actual level = sublevel
        if (sizeof($str) > 1) $lvl = intval($str[1]);
        return $lvl;
    }
    
    
    //---------------------------------------------------------
    
    // resolves and xml Path into the corresponding url
    function getUrlFromPath(&$xt, $path)
    {
        $url = "";
        $str = (string)$path;
        $str = explode("/", $str);
        
        $xmlSearchPath = "";
        foreach($str as $p)
        {
            if (strpos($p, "@short") === false)
            {
                // if there´s no short entry, look for it
                $xmlSearchPath .= $p;
                $q = $xt->xml->xpath($xmlSearchPath."/@short");
                $pLvl = explode("[", $p);
                if (sizeof($pLvl) > 0) $pLvl = $pLvl[0]; else $pLvl = $p;
                if (sizeof($q) > 0 && !(isset($GLOBALS["xmlClientLvl"]) && "l".$GLOBALS["xmlClientLvl"] == $pLvl) )
                    $url .= $q[0]['short']."/";
            } else
            {
                $xmlSearchPath .= $p;
                
                // if there´s a short entry, get it
                $sh = explode("@short='", $p);
                $sh = $sh[ sizeof($sh)-1];
                $sh = explode("']", $sh);
                $sh = $sh[0];
                if( !(isset($GLOBALS["xmlClientLvl"]) && "l".$GLOBALS["xmlClientLvl"] == substr($p, 0, 2)) )
                    $url .= $sh."/";
            }
            
            $xmlSearchPath .= "/";
        }
        
        return $url;
    }

    
    // --------------- get xml path from actual SimpleXMLElement ---------------------------

    function getXmlPathFromElem(&$xt, $elem, $maxNrLevels)
    {
        $path = "";
        $loopEl = $elem;
        $shorts = array();
        $shortPath = "";
        
        while ( sizeof( $loopEl->xpath("parent::*") ) != 0 )
        {
            $loopEl = $loopEl[0]->xpath("parent::*");

            if (sizeof($loopEl) >= 1)
            {
                if ( isset($loopEl[0]['short']) )
                    array_unshift($shorts, "".$loopEl[0]['short']);
            } else {
                break;
            }

            $loopEl = $loopEl[0];
        }
        
        // get path with "short" attributes
        $lev = 0;
        $indSearchPath = "";
        foreach($shorts as $val)
        {
            $shortPath .= "l".$lev."[@short='".$val."']";
            if ($lev != $maxNrLevels-1) $shortPath .= "/";
         
            $indSearchPath .= "l".$lev;
            
            $q = $xt->xml->xpath($indSearchPath);
            if (sizeof($q) > 0)
            {
                $sInd = 0;
                while(isset($q[$sInd]['short']) && $q[$sInd]['short'] != $val )
                    $sInd++;

                $path .= "l".$lev."[".($sInd+1)."]";
                if ($lev != $maxNrLevels-1) $path .= "/";
            }
            
            $indSearchPath .= "[@short='".$val."']/";
            
            $lev++;
        }
        
        return array($path,$shortPath);
    }
 
    
    // --------------- get xml path from actual DomElement ---------------------------
    
    function getXmlPathFromDomElem(&$xt, $elem, $maxNrLevels)
    {
        $path = "";
        $loopEl = $elem;
        $shorts = array();
        $shortPath = "";
        
        while ( $loopEl->parentNode )
        {
            if ( $loopEl->nodeType == XML_ELEMENT_NODE && $loopEl->hasAttribute('short') )
                array_unshift($shorts, "".$loopEl->getAttribute('short'));

            $loopEl = $loopEl->parentNode;
        }
        
        // get path with "short" attributes
        $lev = 0;
        $indSearchPath = "";

        foreach($shorts as $val)
        {
            $shortPath .= "l".$lev."[@short='".$val."']";
            if ($lev != $maxNrLevels-1) $shortPath .= "/";
            
            $indSearchPath .= "l".$lev;
            
            $q = $xt->xml->xpath($indSearchPath);
            if (sizeof($q) > 0)
            {
                $sInd = 0;
                while(isset($q[$sInd]['short']) && $q[$sInd]['short'] != $val )
                    $sInd++;
                
                $path .= "l".$lev."[".($sInd+1)."]";
                if ($lev != $maxNrLevels-1) $path .= "/";
            }

            $indSearchPath .= "[@short='".$val."']/";
            
            $lev++;
        }
        
        return array($path,$shortPath);
    }

    
    // --------------- build a link for use in a href from a xml path ---------------------------
    
    function getHrefFromXmlStr($xt, $path, $lang)
    {
        $link = $GLOBALS['root']."index.php?";
        
        // separate path
        $str = explode("/", $path);
        
        if (strlen($str[0]) == 0)
            array_splice($str, 0, 1);
        
        $xmlSearchPath = "";
        $lvlInd = 0;
        for($i=0;$i<sizeof($str);$i++)
        {
            if (strpos($str[$i], "@short") === false)
            {
                // if there is no short entry, get the short with a xpath query
                if ($str[$i] == "data") $xmlSearchPath .= "/";
                $xmlSearchPath .= $str[$i];
                $q = $xt->xml->xpath($xmlSearchPath."/@short");
                if (sizeof($q) > 0) $link .= "l".$lvlInd."=".$q[0]['short'];
            } else
            {
                $xmlSearchPath .= $str[$i];
                $rpStr = preg_replace("/\[@short='/", "=", $str[$i] );
                $rpStr = preg_replace("/\']/", "", $rpStr);
                $link .= $rpStr;
            }
            
            $xmlSearchPath .= "/";

            if ($str[$i] != "data")
            {
                if ($i != sizeof($str)-1) $link .= "&";
                $lvlInd++;
            }
        }
        
        $link .= "&lang=".$lang;
        
        return $link;
    }
    
    
    //--- rewrite links ------------------------------------------------------
    
    function rewriteLink($link, &$xt=0, &$keys=0 )
    {    	
        $outLink = $link;
        $lang = $keys['langs'][0];
        $xStr = "";
        
        if ($GLOBALS["rewriteLinks"])
        {
            $lSpl = explode("?", $link);

            if (sizeof($lSpl) > 1)
            {
                $outLink = substr($lSpl[0], 0, strpos($lSpl[0], "index"));
                if (strlen($outLink) > 0 && $outLink[strlen($outLink)-1] == "/")
                    $outLink = substr($outLink, 0, strlen($outLink)-1);
                
                // extract user folder
                $found = -1;
                if (isset($GLOBALS["xmlClientLvl"]))
                {
                    preg_match("/[0-9]{4}/i", $lSpl[1], $found);
                    if (sizeof($found) > 0) $found = $found[0];
                }
                
                // get lang
                if (strpos($lSpl[1], "lang") !== false) {
                    $lang = substr($lSpl[1], strpos($lSpl[1], "lang")+5, 2);
                } else {
                    $lang = $keys['langs'][0];
                }
                
                $shorts = explode("&", $lSpl[1]);

                //remove lang from string
                if(strpos($outLink, "lang") !== false)
                    $outLink = substr($outLink, 0, strpos($outLink, "lang")-1);
                
                // remove double "//" but not the "//" after "http://"
                if (strrpos($outLink, "//") !== false &&  strrpos($outLink, "//") != 5)
					$outLink = substr($outLink, 0, strrpos($outLink, "//")-2).substr($outLink, strrpos($outLink, "//")+1, strlen($outLink)-1);

                // get levels
                $ind = 0;
                for($i=0;$i<sizeof($shorts);$i++)
                {
                    $parSpl = explode("=", $shorts[$i]);
                                        
                    if (strlen($parSpl[0]) == 2 && sizeof($parSpl) > 1)
                    {
                        $preXStr = $xStr;
                        $xStr .= $parSpl[0]."[@short='".$parSpl[1]."']";
                        $preXStr .= $parSpl[0];
                        $q = $xt->xml->xpath( $xStr."/name/".$lang );

                        if (sizeof($q) > 0)
                        {
                            $name = (string)$q[0];

                            //check if the nodeValue is explicit add the index of the search result
                            $q2 = $xt->xpath->query($preXStr."/name/".$lang."[text()='".$name."']");

                            // if not explicit add an attribute to make it explizit
                            //if ($q2->length > 1 && $keys != 0 && sizeof($keys['base'.$i]) > 2 )
                            if ($q2->length > 1)
                            {
                           		$q3 = $xt->xml->xpath($xStr."/@".$GLOBALS["rewriteLinksExplAttr"]);
                            		
  								if (sizeof($q3) > 0)
                                {
                                    $cleanStr = str_replace(array(" ", ".", ",", ")", "(", "-"), "", $q3[0]);
                                    $cleanStr = remove_accents($cleanStr);
                                    $cleanStr = strtolower($cleanStr);
                                    $name .= "_".$cleanStr;
                                }
                            }
                           
                            $cleanStr = str_replace(array(" ", ".", ",", ")", "(", "-", "/"), "", $name);
                            $cleanStr = remove_accents($cleanStr);
                            $cleanStr = strtolower($cleanStr);

                            if( !(isset($GLOBALS["xmlClientLvl"])
                                  && $i == $GLOBALS["xmlClientLvl"]
                                  && $found == $GLOBALS["xmlPubClientShort"])
                               )
                            {
                                
                                if ($i>0)       // das hier ist noch schraeg... war ein hack???
                                    $outLink .= "/";
                                $outLink .= $cleanStr;
                            }
                        }
                        
                        $xStr .= "/";
                    }
                }
            }
        }
        
        if (strrpos($outLink, "//") !== false &&  strrpos($outLink, "//") != 5)
            $outLink = substr($outLink, 0, strrpos($outLink, "//")).substr($outLink, strrpos($outLink, "//")+1, strlen($outLink)-1);

        if ($GLOBALS["setLangGet"])
           	$outLink .= "&lang=".$lang;
                
        return $outLink;
    }
    
    
    //---------------------------------------------------------
    
    // get entries and a corresponding url from an xmlpath depending on a key
    // needs a xmltool instance
    
    function resolveRef(&$xt, $xmlPath, $key, $mode, $attr, $lang)
    {
        $list = array();
        $listInd = 0;
        
        $subKeyName = "";
        $isMult = false;
        
        // get the xmlName from the key
        if ($key != "")
        {
            $class = (string)$key;
            $instKey = new $class();
            $subKeyName = "/".$instKey->xmlName;
            $isMult = $instKey->isMultType;
        }
        
        $actLevel = getActLvlFromXml($xmlPath);
        $link = "";
        $q = array();
        
        // spezial fall keywords        
        switch($mode)
        {
            case "image" : $q = $xt->xml->xpath($xmlPath);
                break;
            case "same" : $q = $xt->xml->xpath($xmlPath);
                break;
            case "sameOne" : $q = $xt->xml->xpath($xmlPath);
                break;
            // get all entries from one level under the same
            case "sub" :
                // sub = go one level down
                $actLevel++;
                // get all entries of the sublevel
                $q = $xt->xml->xpath($xmlPath."/l".$actLevel);
                break;
            case "subOne" : $actLevel++; $q = $xt->xml->xpath($xmlPath."/l".$actLevel);
                break;
            // get all reference entries from the sub level
            case "subRef" : $actLevel++; $q = $xt->xml->xpath($xmlPath."/l".$actLevel);
                break;
            case "subRefOne" : $actLevel++; $q = $xt->xml->xpath($xmlPath."/l".$actLevel);
                break;
                // get the reference entry of the same level and resolve it
            case "levelRef" : $path = $xmlPath."/@refentry"; $q = $xt->xml->xpath($path);
                break;
            case "levelRefOne" : $path = $xmlPath."/@refentry"; $q = $xt->xml->xpath($path);
                break;
            default:
                break;
        }
        
        // iterate through all sublevels
        foreach($q as $k => $v)
        {
            $q2 = array();
            
            switch($mode)
            {
                case "image" :
                    $newLinkP = getUrlFromPath($xt, $xmlPath);
                    $link = getHrefFromXmlStr($xt, $xmlPath, $lang);
                    $q2 = $xt->xml->xpath($xmlPath);
                    break;
                case "same" :
                    $newLinkP = getUrlFromPath($xt, $xmlPath);
                    $link = getHrefFromXmlStr($xt, $xmlPath, $lang);
                    $q2 = $xt->xml->xpath($xmlPath.$subKeyName);
                    break;
                case "sameOne" :
                    $newLinkP = getUrlFromPath($xt, $xmlPath);
                    $link = getHrefFromXmlStr($xt, $xmlPath, $lang);
                    $q2 = $xt->xml->xpath($xmlPath.$subKeyName);                    
                    break;
                case "sub" :
                    $newLinkP = getUrlFromPath($xt, $xmlPath."/l".$actLevel."[@short='".$v['short']."']");
                    $link = getHrefFromXmlStr($xt, $xmlPath."/l".$actLevel."[@short='".$v['short']."']", $lang);
                    $q2 = $xt->xml->xpath($xmlPath."/l".$actLevel."[@short='".$v['short']."']".$subKeyName);
                   break;
                case "subOne" :
                    $newLinkP = getUrlFromPath($xt, $xmlPath."/l".$actLevel."[@short='".$v['short']."']");
                    $link = getHrefFromXmlStr($xt, $xmlPath."/l".$actLevel."[@short='".$v['short']."']", $lang);
                    $q2 = $xt->xml->xpath($xmlPath."/l".$actLevel."[@short='".$v['short']."']".$subKeyName);
                    break;
                case "subRef" :
                    $newLinkP = getUrlFromPath($xt, $v['refentry']);
                    $link = getHrefFromXmlStr($xt, $v['refentry'], $lang);
                    if ($v['refentry'] != "")
                        $q2 = $xt->xml->xpath($v['refentry'].$subKeyName);
                    break;
                case "subRefOne" :
                    $newLinkP = getUrlFromPath($xt, $v['refentry']);
                    $link = getHrefFromXmlStr($xt, $v['refentry'], $lang);
                    if ($v['refentry'] != "")
                        $q2 = $xt->xml->xpath($v['refentry'].$subKeyName);
                    break;
                case "levelRef" :
                    $newLinkP = getUrlFromPath($xt, $v);
                    $link = getHrefFromXmlStr($xt, $v, $lang);
                    $q2 = $xt->xml->xpath($v.$subKeyName);
                    break;
                case "levelRefOne" :
                    $newLinkP = getUrlFromPath($xt, $v);
                    $link = getHrefFromXmlStr($xt, $v, $lang);
                    $q2 = $xt->xml->xpath($v.$subKeyName);
                    break;
                default:
                    break;
            }
            
            if (sizeof($q2) > 0)
            {
                $found = false;
                
                // if there´s an attribute set, first try to get it
                // this was a solution for the problem with slideshows and their image attribute
                if ($attr != "")
                {
                    $valid = false;
                    
                    // check if it´s an argument or subnode
                    if ($attr[0] == "@")
                    {
                        $aVal = $q2[0]->xpath($attr);
                        if (sizeof($aVal) > 0 && $aVal[0] != "")
                        {
                            $valid = true;
                            $aVal = $aVal[0];
                        }
                    } else
                    {
                        $aVal = $q2[0]->$attr;
                        $valid = (sizeof($aVal) > 0 && $aVal != "");
                    }
                    
                    if ($valid)
                    {
                        array_push($list, array());
                        $list[$listInd]['url'] = $newLinkP;
                        $list[$listInd]['xmlElm'] = $q2;
                        $list[$listInd]['value'] = $aVal;
                        $list[$listInd]['href'] = $link;                        
                        $listInd++;
                        $found = true;
                    }
                }
                
                // if the attribute in question wasn´t set try to get the content of the key itself
                if (!$found)
                {
                    // if the key has sublevels get them
                    if ($isMult)
                    {
                        $mKey = $instKey->type;
                        $mKeys = $q2[0]->$mKey;

                        if ($mode == "subRefOne" || $mode == "subOne" || $mode == "sameOne" || $mode == "levelRefOne")
                        {
                            array_push($list, array());
                            $list[$listInd]['url'] = $newLinkP;
                            $list[$listInd]['xmlElm'] = $q2;
                            $list[$listInd]['value'] = $mKeys[0];
                            $list[$listInd]['href'] = $link;
                            $listInd++;
                        } else
                        {
                            foreach($mKeys as $val)
                            {
                                array_push($list, array());
                                $list[$listInd]['url'] = $newLinkP;
                                $list[$listInd]['xmlElm'] = $q2;
                                $list[$listInd]['value'] = $val;
                                $list[$listInd]['href'] = $link;
                                $listInd++;
                            }
                        }
                    }
                }
            }
        }

        return $list;
    }
    
    
    //---------------------------------------------------------

    function setCrushOpts(&$mason, &$dPar, &$lay, &$heightCfg, $heightCfgSel, $safety)
    {        
        csscrush_set('options', array('vars' => array('nrColsUlt' => ''.$lay[1],
                                                      'nrColsRet' => ''.$lay[1],
                                                      'nrColsStd' => ''.$lay[2],
                                                      'nrColsIpa' => ''.$lay[3],
                                                      'nrColsIph' => ''.$lay[4],
                                                      'nrColsSma' => ''.$lay[5],
                                                      'itemHeightUlt' => ''.$heightCfg[$heightCfgSel][0],
                                                      'itemHeightRet' => ''.$heightCfg[$heightCfgSel][1],
                                                      'itemHeightStd' => ''.$heightCfg[$heightCfgSel][2],
                                                      'itemHeightIpa' => ''.$heightCfg[$heightCfgSel][3],
                                                      'itemHeightIph' => ''.$heightCfg[$heightCfgSel][4],
                                                      'itemHeightSma' => ''.$heightCfg[$heightCfgSel][5],
                                                      'gSizeSafety' => ''.$safety,
        											  'itemPadUlt' => ''.$mason->getHPad(0),
                                                      'itemPadRet' => ''.$mason->getHPad(1),
                                                      'itemPadStd' => ''.$mason->getHPad(2),
                                                      'itemPadIpa' => ''.$mason->getHPad(3),
                                                      'itemPadIph' => ''.$mason->getHPad(4),
                                                      'itemPadSma' => ''.$mason->getHPad(5),
                                                      'itemMargBotUlt' => ''.$mason->getVPad(0),
                                                      'itemMargBotRet' => ''.$mason->getVPad(1),
                                                      'itemMargBotStd' => ''.$mason->getVPad(2),
                                                      'itemMargBotIpa' => ''.$mason->getVPad(3),
                                                      'itemMargBotIph' => ''.$mason->getVPad(4),
                                                      'itemMargBotSma' => ''.$mason->getVPad(5),
                                                      'ultra' => 'screen and (min-width:'.$dPar->keys['resolutions'][0].'px)',
                                                      'retina' => 'screen and (min-width:'.$dPar->keys['resolutions'][1].'px) and (max-width:'.$dPar->keys['resolutions'][0].'px)',
                                                      'stdbrws' => 'screen and (max-width:'.$dPar->keys['resolutions'][1].'px) and (min-width:'.$dPar->keys['resolutions'][2].'px)',
                                                      'ipad' => 'screen and (max-width:'.$dPar->keys['resolutions'][2].'px) and (min-width:'.$dPar->keys['resolutions'][3].'px)',
                                                      'iphone' => 'screen and (max-width:'.$dPar->keys['resolutions'][3].'px) and (min-width:'.$dPar->keys['resolutions'][4].'px)',
                                                      'smartphone' => 'screen and (max-width:'.$dPar->keys['resolutions'][4].'px)'
                                                      )
                                      )
                     );
    }


    //---------------------------------------------------------

    function seems_utf8($str)
    {
        $length = strlen($str);
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; # 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; # Does not match any model
            for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    function remove_accents($string)
    {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;
        
        if (seems_utf8($string)) {
            $chars = array(
                           // Decompositions for Latin-1 Supplement
                           chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
                           chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
                           chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
                           chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
                           chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
                           chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
                           chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
                           chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
                           chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
                           chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
                           chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
                           chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
                           chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
                           chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
                           chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
                           chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
                           chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
                           chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
                           chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
                           chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
                           chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
                           chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
                           chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
                           chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
                           chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
                           chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
                           chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
                           chr(195).chr(191) => 'y',
                           // Decompositions for Latin Extended-A
                           chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
                           chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
                           chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
                           chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
                           chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
                           chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
                           chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
                           chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
                           chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
                           chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
                           chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
                           chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
                           chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
                           chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
                           chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
                           chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
                           chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
                           chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
                           chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
                           chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
                           chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
                           chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
                           chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
                           chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
                           chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
                           chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
                           chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
                           chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
                           chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
                           chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
                           chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
                           chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
                           chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
                           chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
                           chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
                           chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
                           chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
                           chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
                           chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
                           chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
                           chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
                           chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
                           chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
                           chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
                           chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
                           chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
                           chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
                           chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
                           chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
                           chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
                           chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
                           chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
                           chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
                           chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
                           chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
                           chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
                           chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
                           chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
                           chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
                           chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
                           chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
                           chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
                           chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
                           chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
                           // Euro Sign
                           chr(226).chr(130).chr(172) => 'E',
                           // GBP (Pound) Sign
                           chr(194).chr(163) => '');
            
            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
            .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
            .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
            .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
            .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
            .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
            .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
            .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
            .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
            .chr(252).chr(253).chr(255);
            
            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
            
            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }
        
        return $string;
    }

    //---------------------------------------------------------

    function debug_to_console( $data )
    {
        if ( is_array( $data ) )
            $output = "<script>console.log( 'Debug Objects: ".implode(',', $data)."' );</script>";
        else
            $output = "<script>console.log( 'Debug Objects: ".$data."' );</script>";
    
        echo $output;
    }
    
    //---------------------------------------------------------

    function deletePath($path)
    {
        if (is_dir($path) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
            
            foreach ($files as $file)
            {
                if (in_array($file->getBasename(), array('.', '..')) !== true)
                {
                    if ($file->isDir() === true)
                    {
                        rmdir($file->getPathName());
                    }
                    
                    else if (($file->isFile() === true) || ($file->isLink() === true))
                    {
                        unlink($file->getPathname());
                    }
                }
            }
            
            return rmdir($path);
        }
        
        else if ((is_file($path) === true) || (is_link($path) === true))
        {
            return unlink($path);
        }
        
        return false;
    }
    
    //---------------------------------------------------------

    function getClientFolder(&$xt, $xmlStr, &$mysqlH)
    {
        $usrFold = "";
        
        // if there is a user level set, try to get user path for file access
        if (isset($GLOBALS["xmlClientLvl"]))
        {
            $str = substr( $xmlStr, strpos($xmlStr, "l".$GLOBALS["xmlClientLvl"]), strpos($xmlStr, "]") - strpos($xmlStr, "l".$GLOBALS["xmlClientLvl"]) +1 );
            $client = $xt->xpath->query( $str."/@client" );
            if ($client->length > 0) {
                $client = $client->item(0)->nodeValue;
            } else {
                $client = $GLOBALS["pubClientName"];
            }
            
            // get user folder
            $usrFold = $mysqlH->getClientArgByName($client, "folder");
            $usrFold .= "/";
        }
        
        return array($usrFold, $client);
    }
    
    //---------------------------------------------------------

    function urlToServerAbsPath($path)
    {
        $outPath = "";
        
        // get absolute server path without script name
        $servPath = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
        $servPath = substr($servPath, 0, strrpos($servPath, "/")+1);
        $replaceWith = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], "/")+1);
        
        $outPath = str_replace($servPath, $replaceWith, $path);
        
        return $outPath;
    }
    
    //---------------------------------------------------------

    function imgFluid($path, $aspW = 16, $aspH = 9)
    {
        $outStr = "";
        $verticalAlign = 35;

        // mit absoluter url gibt getimagesize einen 404 raus, deshalb server pfad...
        list($width, $height, $type, $attr) = getimagesize( urlToServerAbsPath($path) );        
        $prop = $width / $height;
        $setWidth = "100%"; $setHeight = "auto"; $heightDiff = "0"; $widthDiff = "0";
        
        if ($prop < ($aspW / $aspH))
        {
            $heightDiff = ($height - $width * ($aspH / $aspW)) / $height;
            $heightDiff *= $verticalAlign;
            
        } else
        {
            // if the image is wider than 16/9
            $widthDiff = ($width - $height * ($aspW / $aspH)) / $width;
            $widthDiff *= 50;
            
            $setWidth = "auto";
            $setHeight = "100%";
        }

        // first load a 1x1 placeholder and set the real img path as data-src for later replacement
        $outStr = '<img style="width:100%;height:100%" data-width="'.$setWidth.'" data-height="'.$setHeight.'" data-margin-top="'.$heightDiff.'" data-margin-left="'.$widthDiff.'" src="pic/placeholder.png" data-src="'.$path.'">';
//        $outStr = '<img style="width:'.$setWidth.';height:'.$setHeight.';margin-top:-'.$heightDiff.'%;margin-left:-'.$widthDiff.'%" src="'.$path.'">';
 
        return $outStr;
    }

    //---------------------------------------------------------
    // first load a 1x1 placeholder and set the real img path as data-src for later replacement
    
    function lazyLoadImg($path, $class="", $alt="")
    {    
        // mit absoluter url gibt getimagesize einen 404 raus, deshalb server pfad...
        list($width, $height, $type, $attr) = getimagesize( urlToServerAbsPath($path) );        
        $marginBottom = ($height / $width * 100) - 100;
        		
    	$outStr = "";
    	$outStr .= '<img ';
     	if ($class != "") $outStr .= 'class="'.$class.'" style="margin-bottom:'.$marginBottom.'%;" ';
     	$outStr .= 'src="pic/placeholder.png" alt="'.$alt.'" data-src="'.$path.'" />';
    	return $outStr;
    }
?>