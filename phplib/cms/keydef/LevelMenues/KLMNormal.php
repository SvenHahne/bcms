<?php
    
    class KLMNormal
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
                $list[$i] = array();
                $list[$i]['name'] = $xIt[$i]->name->$lang;
                $list[$i]['short'] = $xIt[$i]->xpath("@short");
                $list[$i]['short'] = $list[$i]['short'][0];
            }

            if ( isset($_GET['sel']) ) {
                $sel = $_GET['sel'];
                $fromLvl++;
            } else {
                $sel = 0;
            }

            // make a navigation with the subentries
            $body['sec1'] .= "<div class='klmListHead'>";
            $body['sec1'] .= "<div class='lvlDropDownBig'>";

            for($i=0;$i<sizeof($list);$i++)
            {
                $nlink = $dPar->linkPath.$list[$i]['short'];
                
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
                
                $link = $this->getHref($nlink, $list[$i]['short']);
                $body['sec1'] .= "<div class='subNav'><a ";
                if ($i == $sel) $body['sec1'] .= "class='blue' ";
                $body['sec1'] .= "href='".$bT."&sel=".$i."' target='_self'>".$list[$i]['name']."</a></div>";
                if ($i != sizeof($list)-1)
                    $body['sec1'] .= "<div class='subNavSep'>|</div>";
            }
            
            $body['sec1'] .= "</div>";
            $body['sec1'] .= "</div>";
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