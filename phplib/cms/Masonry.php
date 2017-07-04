<?php

    // wrapper klasse für jquery Masonry
    class Masonry
    {
        protected $pvSize = 0;
        protected $phSize = 0;
        protected $vertLines = FALSE;
        protected $vertLinesColor = "#000";
        protected $scaleFact = array(1.0, 1.0, 1.0, 1.2, 1.4, 2.0);
        
        function __construct()
        {}

        function loadJs()
        {
            return '<script type="text/javascript" src="'.$GLOBALS['root'].'js/masonry.pkgd.min.js"></script>';
        }
        
        function jInit(&$dPar)
        {
            $outStr = "";
            
            if ( !$GLOBALS["masonryJInitSet"] )
            {
                $GLOBALS["masonryJInitSet"] = TRUE;
                
                $outStr .= '
                $("#mason-cont").masonry({ columnWidth: ".g-sizer", itemSelector: ".item", gutter: ".g-gutter", transitionDuration:0 });                   
                $("#mason-cont").masonry("on", "layoutComplete", function(msnryInstance, laidOutItems) {';
                                   
                // trick for vertical lines
                if ($this->vertLines == TRUE)
                {
                    $outStr .= '
                    for (var i=0;i<laidOutItems.length;i++) {
                        if (laidOutItems[i].element.style.left != "0px") {
                            laidOutItems[i].element.style.borderLeft = "solid 1px '.$this->vertLinesColor.'";
                        } else {
                            laidOutItems[i].element.style.borderLeft = "0px";
                        }
                    }
                    ';
                }
                if (!$dPar->doResizeImgs)
                {
                    $outStr .= 'lazyLoad([';
                    for($i=0;$i<sizeof($dPar->keys['resolutions']);$i++)
                    {
                        $outStr .= $dPar->keys['resolutions'][$i];
                        if ($i!=sizeof($dPar->keys['resolutions'])-1)
                            $outStr .= ',';
                    }
                    $outStr .= ']); 
                    		//checkImgVisible();';
                }
                
                $outStr .= '
                });
                $("#mason-cont").masonry(); // do it!
                ';
            }

            return $outStr;
        }
    
        // set padding, padding-left is not applied to the most left item
        // padding-right is not applied to the most right item
        // thereby the hole div stays aligned
        function setPaddingVert($pvSize)
        {
            $this->pvSize = $pvSize;
        }

        function setPaddingHori($phSize)
        {
            $this->phSize = $phSize;
        }

                                     
        // vertical lines separating the items.
        // vertical lines don´t appear at the most left item lefts side
        // and the most right items right side
        function setVertLines($set, $col)
        {
            $this->vertLines = $set;
            $this->vertLinesColor = $col;
        }
                                             
        function getVPad($ind)
        {
            $sf = 1.0;
            //if (sizeof($this->scaleFact) -1 >= $ind) $sf = $this->scaleFact[$ind];
            return $this->pvSize * $sf;
        }

        function getHPad($ind)
        {
            $sf = 1.0;
            //if (sizeof($this->scaleFact) -1 >= $ind) $sf = $this->scaleFact[$ind];
            return $this->phSize * $sf;
        }
    }
?>