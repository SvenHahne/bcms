<?php
    
    class KLMOneup
    {
        protected $dd;
        protected $head;
        protected $cols;
        
        public function __construct()
        {}
        
        function addHead(&$head, &$dPar)
        {}
        
        function draw(&$body, &$xmlItem, &$dPar)
        {
            $xt = &$dPar->xt;
            $node = &$xmlItem;
            $actLvl = $dPar->actLevel;
            $fromLvl = $dPar->actLevel;
            
            $lang = (string)$dPar->lang;
            if ( isset($_GET['dstyle']) ) $this->dispStyle = $_GET['dstyle'];

            // go one level up
            $str = explode("/", $dPar->xmlStr);
            $lvlUpXmlStr = "";
            for ($i=0;$i<sizeof($str)-1;$i++)
            {
                $lvlUpXmlStr .= $str[$i];
                if ($i< sizeof($str)-2) $lvlUpXmlStr .= "/";
            }
            
            // cut short argument
            $str = explode("[@short=", $lvlUpXmlStr);
            $lvlUpXmlStr = "";
            for ($i=0;$i<sizeof($str)-1;$i++)
            {
                $lvlUpXmlStr .= $str[$i];
                if ($i< sizeof($str)-2) $lvlUpXmlStr .= "[@short=";
            }
            
            if ($lvlUpXmlStr != "")
            {
                $subLvls = $xt->xml->xpath($lvlUpXmlStr);
                
                // get $dPar->linkPath two levels up,
                // dirty hack: take only first entry
                $newLinkPath = substr($dPar->linkPath, 0, 5);
                
                $dPar->subNavList = array();
                for ($i=0;$i<sizeof($subLvls);$i++)
                {
                    $dPar->subNavList[$i] = array();
                    $dPar->subNavList[$i]['name'] = $subLvls[$i]->name->$lang;
                    $dPar->subNavList[$i]['short'] = $subLvls[$i]->xpath("@short");
                    $dPar->subNavList[$i]['short'] = $dPar->subNavList[$i]['short'][0];
                }
                
                if ( isset($_GET['sel']) ) {
                    $sel = $_GET['sel'];
                    $fromLvl++;
                } else {
                    $sel = -1;
                }
                
                for($i=0;$i<sizeof($dPar->subNavList);$i++)
                {
                    // get basis link zum selben eintrag
                    $bLink = $this->getHref($newLinkPath, "");
                    $bLink = explode("&", $bLink);
                    $bT = "";
                    for($j=0;$j<sizeof($bLink);$j++)
                        if ($j<sizeof($bLink)-1)
                            $bT .= $bLink[$j];
                    
                    $link = $this->getHref($newLinkPath, $dPar->subNavList[$i]['short']);

                    $dPar->subNavList[$i]['link'] = $link;
                }
                
                $dPar->subNavList['sel'] = $sel;
                
                $dPar->subNavList['selLvls'] = explode("/", $dPar->linkPath);
                if ($dPar->subNavList['selLvls'][ sizeof($dPar->subNavList['selLvls'])-1 ] == "")
                    array_pop($dPar->subNavList['selLvls']);
            }
        }
        
        //------------------------------------------------------------
        
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
    }
?>