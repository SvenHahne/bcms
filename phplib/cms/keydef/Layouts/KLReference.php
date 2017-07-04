<?php
    
    class KLReference extends KeyDef
    {
        protected $dd;
        protected $dispText;
        protected $dispStyle;
        protected $mason;
        protected $refEntryDisp;
       
        public function __construct()
        {
            $this->xmlName = "l";
            $this->htmlName['de'] = "Level";
            $this->htmlName['es'] = "Level";
            $this->htmlName['en'] = "Level";
            $this->type = "level";
            $this->postId = "level";
            $initAtNewEntry = TRUE;
            // the KAShort() entry is essential, since with this entry all the
            // directory entries are made
            $this->subKeys = array(new KAShort(), new KAImage(), new KName(),
                                   new KAGenSelect("style", array("Stil", "Estilo", "Style"), array()));
            $this->dispText = array("place", "city");
            $this->dispStyle = 0;
            
            $this->mason = new Masonry();
            $this->mason->setPaddingVert(20);
            $this->mason->setPaddingHori(10);
            
            $this->refEntryDisp = new KLMPlainLowLvl();

        }
        
        function addHead(&$head, &$dPar)
        {
            if ( $GLOBALS["imgLiquid"] == false ) {
                $head['base'] .= '<script type="text/javascript" src="js/imgLiquid.js"></script>';
                $GLOBALS["imgLiquid"] = true;
            }
            
            if ( !$GLOBALS["masonry"] ) {
                $head['base'] .= $this->mason->loadJs();
                $head['jqDocReady'] .= $this->mason->jInit();
                $GLOBALS["masonry"] = true;
            }
            
            if ( $GLOBALS["klm"] == false ) {
                $head['base'] .= '<link rel="stylesheet" type="text/css" href="style/kLevelStd.css" />';
                $head['jqDocReady'] .= '
                $(".imgLiquidFill").imgLiquid({ fill: true, horizontalAlign: "center", verticalAlign: "70%" });
                $(".imgLF2").imgLiquid({ fill: true, horizontalAlign: "center", verticalAlign: "70%" });';
                $GLOBALS["klm"] = true;
            }
            
            $this->refEntryDisp->addHead($head, $dPar);
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $xt = &$dPar->xt;
            $lang = (string)$dPar->lang;
            if ( isset($_GET['dstyle']) ) $this->dispStyle = $_GET['dstyle'];
            
            $fromLvl = $dPar->actLevel;
            $lvl = "l".$dPar->actLevel;
            $subLvls = $xmlItem->xpath($lvl);
            $list = array();
            for ($i=0;$i<sizeof($subLvls);$i++)
            {
                $list[$i] = array();
                $list[$i]['name'] = $subLvls[$i]->name->$lang;
                $list[$i]['short'] = $subLvls[$i]->xpath("@short");
                $list[$i]['short'] = $list[$i]['short'][0];
            }
            
            if ( isset($_GET['sel']) ) {
                $sel = $_GET['sel'];
                $fromLvl++;
            }   else {
                $sel = 0;
            }
            
            // make a navigation with the subentries
            $body['sec1'] .= "<div class='klmListHead'>";
            $body['sec1'] .= "<div class='lvlDropDownBig'>";
            
            for($i=0;$i<sizeof($list);$i++)
            {
                // get basis link zum selben eintrag
                $bLink = $this->getHref($dPar->linkPath, "");
                $bLink = explode("&", $bLink);
                $bT = "";
                for($j=0;$j<sizeof($bLink);$j++)
                    if ($j<sizeof($bLink)-1)
                        $bT .= $bLink[$j];
                
                $link = $this->getHref($dPar->linkPath, $list[$i]['short']);
                
                $body['sec1'] .= "<div class='subNav'><a ";
                if ($i == $sel) $body['sec1'] .= "class='blue showIfSmall' ";
                $body['sec1'] .= "href='".$bT."&sel=".$i."' target='_self'>".$list[$i]['name']."</a></div>";
                if ($i != sizeof($list)-1)
                    $body['sec1'] .= "<div class='subNavSep'>|</div>";
            }
            
            $body['sec1'] .= "</div>";
            $body['sec1'] .= "</div>";
            

            // check refentry - as is or from the $_GET['sel']
            $refEntr = $xmlItem['refentry'];
            $selShort = "";
            if ( isset($_GET['sel']) )
            {
                $selShort = $list[$_GET['sel']]['short'];
                $refEntr = $dPar->xt->xml->xpath($dPar->xmlStr."/l".$dPar->actLevel."[@short='".$selShort."']");
                $refEntr = $refEntr[0]['refentry'];

            } else {
                $selShort = $list[0]['short'];
            }
            
            // get reference entry
            // get the level of the reference entry
            $spl = explode("/", $refEntr);
            $spl = $spl[sizeof($spl)-1];
            $spl = explode("[", $spl);
            $dPar->actLevel = $spl[0][1]+1;

            $xmlItem2 = $dPar->xt->xml->xpath($refEntr);
            if (sizeof($xmlItem2) > 0) $xmlItem2 = $xmlItem2[0];
            $xmlItem2['style'] = "info";

            // check if all indices are 'shorts'
            $lp = $refEntr;
            $lp = explode("/", $lp);

            $rLp = "";
            if (isset($xmlItem2['short']))
            {
            for ($i=0;$i<sizeof($lp);$i++)
            {
                if ($i != sizeof($lp)-1){
                    $rLp .= $lp[$i];
                } else {
                    $tlp = explode("[", $lp[$i]);
                    $rLp .= $tlp[0]."[@short='".$xmlItem2['short']."']";
                }
                if ($i != sizeof($lp)-1) $rLp .= "/";
            }
            }
            $dPar->linkPath = $this->xmlStrToPath($rLp);
          
            // make a list of referential projects
            $str = "<?xml version='1.0'?><reflist style='block'>";
            switch($fromLvl)
            {
                // if the reference comes from the l0, show the other reference projects of
                // the other l0s
                case 1:
                    $q = $dPar->xt->xml->xpath($dPar->xmlStr."/l".$fromLvl."/@refentry");
                    for ($i=0;$i<sizeof($q);$i++)
                        $str .= "<ref type='col1_row1'>".$q[$i]['refentry']."</ref>";
                    break;
                // if the reference comes from the l0 and a sublevel in the subnav was selected
                case 2:
                    $q = $dPar->xt->xml->xpath($dPar->xmlStr."/l".($fromLvl-1)."[@short='".$selShort."']/l".$fromLvl);
                    for ($i=0;$i<sizeof($q);$i++)
                    {
                        $str .= "<ref type='col1_row1'>".$dPar->xmlStr."/l".($fromLvl-1)."[@short='".$selShort."']/l".$fromLvl."[".($i+1)."]</ref>";

                    }
                    break;
                default:
                    break;
            }
            $str .= "</reflist>";
            $xmlI = simplexml_load_string($str);
            $dPar->refList = $xmlI;

            // draw the referenced item as PlainLowLevel
            $this->refEntryDisp->draw($body, $xmlItem2, $dPar);
        }


        function getSlideshowPic(&$dPar, &$sub)
        {
            
            $slPicPath = "pic/slideshow/".$GLOBALS['userUrl'].$dPar->linkPath.$sub['short']."/";
     
            // if there is a gallery select the first item
            if ( isset($sub->slideshow) && count($sub->slideshow) > 0 )
                $slPicPath .= $sub->slideshow->slimage;
            
            return $slPicPath;
        }
        
        
        function getHref($linkPath, $short)
        {
            $link = "index.php?";
            $ls = explode("/", $linkPath);
            
            $ind = 0;
            for($i=0;$i<sizeof($ls);$i++)
            {
                if ($ls[$i] != "")
                {
                    if ($i > 0) $link .= "&";
                    $link .= "l".($i)."=".$ls[$i];
                }
                $ind = $i;
            }
            
            $link .= "&l".($ind)."=".$short;
            return $link;
        }
        
        function formatSubAttr($getArg)
        {
            $text = "";
            if ( $getArg['place'] != "" ) $text .= $getArg['place'];
            if ( $getArg['city'] != "" || $getArg['size'] != "" || $getArg['year'] != "") $text .= ', ';
            if ( $getArg['city'] != "" ) $text .= $getArg['city'];
            if ( $getArg['size'] != "" || $getArg['year'] != "") $text .= ', ';
            if ( $getArg['size'] != "" ) $text .= $getArg['size'].'m&sup2;';
            if ( $getArg['year'] != "" ) $text .= ', '.$getArg['year'];
            return $text;
        }
        
        function xmlStrToPath( $xmlStr )
        {
            $path = "";
            $spl = explode( "/", $xmlStr );
            
            for ( $i=0;$i<sizeof($spl);$i++)
            {
                $valArg = explode( "[", $spl[$i] );
                
                // wenn type
                if ( sizeof($valArg) > 1 )
                {
                    $subPath = explode("'", $valArg[1]);
                    
                    // if is short arg
                    if (sizeof($subPath) > 1 ) $path .= $subPath[1]."/";
                }
            }
            
            return $path;
        }
        
    }
?>