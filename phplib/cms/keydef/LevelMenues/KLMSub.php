<?php
    
    class KLMSub
    {
        protected $dd;
        protected $head;
        protected $cols;
        
        public function __construct()
        {
        }
        
        function addHead(&$head, &$dPar)
        {
        }
        
        function draw(&$body, &$xmlItem, &$dPar)
        {
            $xt = &$dPar->xt;
            $node = &$xmlItem;
            $actLvl = $dPar->actLevel;
            $fromLvl = $dPar->actLevel;
            
            $lang = (string)$dPar->lang;
            if ( isset($_GET['dstyle']) ) $this->dispStyle = $_GET['dstyle'];
            
            $xIt = $xmlItem->xpath("l".$dPar->actLevel);

            $list = array();
            for ($i=0;$i<sizeof($xIt);$i++)
            {
                $dPar->subNavList[$i] = array();
                $dPar->subNavList[$i]['name'] = $xIt[$i]->name->$lang;
                $dPar->subNavList[$i]['short'] = $xIt[$i]->xpath("@short");
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
                $nlink = $dPar->linkPath.$dPar->subNavList[$i]['short'];
                
                // get basis link zum selben eintrag
                $bLink = $this->getHref($nlink, "");
                $bLink = explode("&", $bLink);
                
                $bT = "";
                for($j=0;$j<sizeof($bLink);$j++)
                {
                    if ($j<sizeof($bLink)-1)
                    {
                        $bT .= $bLink[$j];
                        if ($j != sizeof($bLink)-2) $bT .= "&";
                    }
                }
                
                //$dPar->subNavList[$i]['link'] = $this->getHref($nlink, $dPar->subNavList[$i]['short']);
                $dPar->subNavList[$i]['link'] = $bT;
            }
            
            $dPar->subNavList['sel'] = $sel;
            
            $dPar->subNavList['selLvls'] = explode("/", $dPar->linkPath);
            if ($dPar->subNavList['selLvls'][ sizeof($dPar->subNavList['selLvls'])-1 ] == "")
                array_pop($dPar->subNavList['selLvls']);
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