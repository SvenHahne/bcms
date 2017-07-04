<?php
    
	class KLPlainLowLvl extends KeyDef
	{
        protected $dd;
        protected $head;
        protected $cols;
        
		public function __construct()
		{
			$this->xmlName = "l";
			$this->htmlName['de'] = "Level";
			$this->htmlName['es'] = "Level";
			$this->htmlName['en'] = "Level";
			$this->type = "level";
			$this->postId = "level";
			$initAtNewEntry = TRUE;
            $this->cols = array();
            $this->cols['nrColsRetina'] = 3;
            $this->cols['nrColsStd'] = 3;
            $this->cols['nrColsIpad'] = 3;
            $this->cols['nrColsIphone'] = 2;
            $this->cols['nrColsOverlap'] = 3;
            $this->cols['nrColsSmartp'] = 1;
            
            $this->mason = new Masonry();
            $this->mason->setPaddingVert(20);
            $this->mason->setPaddingHori(10);
		}
		
        function addHead(&$head, &$dPar)
		{
            if ( $GLOBALS["imgLiquid"] == false )
            {
                $head['base'] .= '<script type="text/javascript" src="js/imgLiquid.js"></script>';
                $GLOBALS["imgLiquid"] = true;
            }
            $head['jqDocReady'] .= '$(".imgLiquidFill").imgLiquid({fill: true, horizontalAlign: "center", verticalAlign: "30%"});';
            
            //------------------------------------------------------------------------------------------------
            if ( !$GLOBALS["masonry"] ) {
                $head['base'] .= $this->mason->loadJs();
                $head['jqDocReady'] .= $this->mason->jInit();
                $GLOBALS["masonry"] = true;
            }

            //------------------------------------------------------------------------------------------------
//            $head['jqWinLoad'] .= '$("#mason-cont").masonry({ columnWidth: ".g-sizer", itemSelector: ".item", transitionDuration:0 });';
            
            //------------------------------------------------------------------------------------------------
            $head['jqWinResize'] .= '';
            
            
            $this->head = &$head;
        }
    
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $xt = &$dPar->xt;
            $node = &$xmlItem;
            $actLvl = $dPar->actLevel;
            $fromLvl = $dPar->actLevel;

            //--- wird nur gebraucht, um die menu zeile zu machen --------            
        
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
                
                // get $dPar->linkPath one level up
                $newLinkPath = substr($dPar->linkPath, 0, 5);
                
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
                    $bLink = $this->getHref($newLinkPath, "");
                    $bLink = explode("&", $bLink);
                    $bT = "";
                    for($j=0;$j<sizeof($bLink);$j++)
                        if ($j<sizeof($bLink)-1)
                            $bT .= $bLink[$j];
                    
                    $link = $this->getHref($newLinkPath, $list[$i]['short']);
                    
                    $body['sec1'] .= "<div class='subNav'><a ";
                    if ($i == $sel) $body['sec1'] .= "class='blue showIfSmall' ";
                    $body['sec1'] .= "href='".$bT."&sel=".$i."' target='_self'>".$list[$i]['name']."</a></div>";
                    if ($i != sizeof($list)-1)
                        $body['sec1'] .= "<div class='subNavSep'>|</div>";
                }
                
                $body['sec1'] .= "</div>";
                $body['sec1'] .= "</div>";
            }
            

            // search for next level with content
            $ind = 0;
            $subLvlStr = "l".($actLvl);
              
            switch ( $xmlItem['style'] )
            {
                case "info" :
                    $this->info($body['sec2'], $node, $dPar, $xmlItem);
                    break;
                case "fixInfo" :
            //        $this->fixInfo($body['sec2'], $node, $dPar, $xmlItem);
                    break;
                default : $this->standard($body['sec2'], $node, $dPar, $xmlItem);
                    break;
            }
        }

    
// --------------- draw methods ---------------------------------------------
    
        
        //------------------------------------------------------------
        
        function info(&$body, &$node, &$dPar, &$xmlItem)
        {
            $lang = (string)$dPar->lang;
            if (isset($xmlItem->name))
            {
                $name = $xmlItem->name->$lang;
                $name = $name[0];
            }
            
            $followText = array();
            $followText['de'] = "Ähnliche Projekte";
            $followText['en'] = "Related projects";
            $followText['es'] = "Proyectos relacionados";
            
            $refList = new KReflist();
            $refList->setCrush = false;
            
            // set nr of columns
            csscrush_set('options', array('vars' => array('nrColsRetina' => $this->cols['nrColsRetina'],
                                                          'nrColsStd' => $this->cols['nrColsStd'],
                                                          'nrColsIpad' => $this->cols['nrColsIpad'],
                                                          'nrColsIphone' => $this->cols['nrColsIphone'],
                                                          'nrColsSmartp' => $this->cols['nrColsSmartp'],
                                                          'itemHeightRet' => '200',
                                                          'itemHeightStd' => '200',
                                                          'itemHeightIpa' => '200',
                                                          'itemHeightIph' => '200',
                                                          'itemHeightSma' => '200') ) );
            
            $body .= '<div id="mason-cont" class="masonry">';
            $body .= '<div class="g-sizer"></div>';
            
            $body .= '<div class="masonry item col5_row1">';
            
            // draw all children
            $contCount = 0;
            $nodeName = "";
            $nodeCont = "";
            
            if (sizeof($node) > 1)
            {
                $nodeName = mb_strtoupper($node->name->$lang);
                $nodeCont = $node->cont->$lang;
                
                foreach ($node as $sub)
                {
                    $tagName = $sub->getName();
                    
                    if ( $tagName == "cont" )
                    {
                        if ($contCount>0) $className = "KText"; else $className = "";
                        $contCount++;
                    } else {
                        $className = "K".ucfirst($tagName);
                    }
                    
                    if ($className != "")
                    {
                        $obj = new $className;
                        $obj->addHead( $this->head, $dPar );
                        
                        // get all arguments
                        if (sizeof($sub->attributes()) > 0)
                        {
                            $dPar->args = (array) $sub->attributes();
                            $dPar->args = $dPar->args['@attributes'];
                        }
                        
                        $obj->draw($body, $sub, $dPar);
                    }
                }
            }
            $body .= '</div>';
            
            // info block
            $body .= '<div class="masonry item col5_row1">';
            
            // get all the arguments
            $body .= '<div class="info">';
            $body .= '<div class="fixInfoTextTop">'.$nodeName.'<br>'.mb_strtoupper($this->formatSubAttr($node));
            $body .= '<hr id="lowerLimit">';
            $body .= '</div>';
            
            $body .= '<div class="fixInfoText">'.$nodeCont.'</div>';
            $body .= '</div>';
            
            
            $body .= '<br><div class="nText_90">'.mb_strtoupper($followText[$lang]).'</div>';
            $body .= '<hr id="lowerLimit">';
            $body .= '</div>';
            
            $dPar->newMason = false;
            $refList->draw($body, $dPar->refList, $dPar);
            
            $body .= '</div>';
        }
        
        
        //------------------------------------------------
        
        
        function fixInfo(&$body, &$node, &$dPar, &$xmlItem)
        {
            $lang = (string)$dPar->lang;
            
            if (isset($xmlItem->name))
            {
                $name = $xmlItem->name->$lang;
                $name = $name[0];
            }

            $followText = array();
            $followText['de'] = "Ähnliche Projekte:";
            $followText['en'] = "Related projects:";
            $followText['es'] = "Proyectos relacionados:";
            
            $refList = new KReflist();
            $refList->setCrush = false;

            // set nr of columns
            csscrush_set('options', array('vars' => array('nrColsRetina' => $this->cols['nrColsRetina'],
                                                          'nrColsStd' => $this->cols['nrColsStd'],
                                                          'nrColsIpad' => $this->cols['nrColsOverlap'],
                                                          'nrColsIphone' => $this->cols['nrColsOverlap'],
                                                          'nrColsSmartp' => $this->cols['nrColsSmartp'],
                                                          'itemHeight' => '100') ) );
            
            $body .= '<h3 class="kLMListedSmall">'.$name.'</h3>';            
            $body .= '<div id="mason-cont" class="masonry">';
            $body .= '<div class="g-sizer"></div>';

            // info block
            $body .= '<div class="masonry item col1_klmLow">';
            //$body .= '<div class="fakeitem"></div>';
            

            // get all the arguments
            $body .= '<div class="fixInfo">';
            $body .= '<div class="fixInfoTextTop">'.$this->formatSubAttr($node).'</div><br>';
            $body .= '<div class="fixInfoText">'.$node->cont->$lang.'</div>';
            $body .= '</div>';
            $body .= '</div>';
            
            $body .= '<div class="masonry item col4_klmLow">';

            // draw all children
            $contCount = 0;
            $nodeName = "";
            $nodeCont = "";
            
            if (sizeof($node) > 1)
            {
                $nodeName = mb_strtoupper($node->name->$lang);
                $nodeCont = $node->cont->$lang;

                foreach ($node as $sub)
                {
                    $tagName = $sub->getName();
                    
                    if ( $tagName == "cont" ) {
                        if ($contCount>0) $className = "KText"; else $className = "";
                        $contCount++;
                    } else {
                        $className = "K".ucfirst($tagName);
                    }
                    
                    if ($className != "")
                    {
                        $obj = new $className;
                        $obj->addHead( $this->head, $dPar );
                        
                        // get all arguments
                        if (sizeof($sub->attributes()) > 0)
                        {
                            $dPar->args = (array) $sub->attributes();
                            $dPar->args = $dPar->args['@attributes'];
                        }
                        
                        $obj->draw($body, $sub, $dPar);
                    }
                }
            }
            
            $body .= '</div>';
            $body .= '</div>';
            $body .= '<hr id="lowerLimit">';
            
            $body .= '<div class="nText_90">'.$followText[$lang].'</div>';
            
            // get relevant projects ---- dirty test
            $str = "<?xml version='1.0'?>
<reflist style='list'><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0001']/l2[1]</ref><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0001']/l2[3]</ref><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0001']/l2[4]</ref><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0001']/l2[11]</ref><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0002']/l2[4]</ref><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0004']/l2[3]</ref><ref type='refL_col5_row1'>l0[@short='0002']/l1[@short='0000']/l2[1]</ref></reflist>";

            $xmlI = simplexml_load_string($str);
                        
            $refList->draw($body, $xmlI, $dPar, false);
        }


        function standard(&$body, &$node, &$dPar, &$xmlItem)
        {
            $lang = (string)$dPar->lang;

            // draw all children
            foreach ($node as $sub)
            {
                $tagName = $sub->getName();
                
                if ( $tagName == "cont" ) $className = "KText"; else $className = "K".ucfirst($tagName);

                $obj = new $className;
                $obj->addHead( $this->head, $dPar );
                
                // get all arguments
                if (sizeof($sub->attributes()) > 0)
                {
                    $dPar->args = (array) $sub->attributes();
                    $dPar->args = $dPar->args['@attributes'];
                }
                
                $obj->draw($body, $sub, $dPar);
            }
        }
        
// --------------- utility methods ---------------------------------------------

        function xmlStrToPath( $class, $xmlStr )
        {
            $path = $class->uploadPath."/";
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
            if (sizeof($getArg) > 1)
            {
            if ( $getArg['place'] != "" ) $text .= $getArg['place'];
//            if ( $getArg['city'] != "" || $getArg['size'] != "" || $getArg['year'] != "") $text .= ', ';
//            if ( $getArg['city'] != "" ) $text .= $getArg['city'];
//            if ( $getArg['size'] != "" || $getArg['year'] != "") $text .= ', ';
//            if ( $getArg['size'] != "" ) $text .= $getArg['size'].'m&sup2;';
//            if ( $getArg['year'] != "" ) $text .= ', '.$getArg['year'];
            }
            return $text;
        }

    }
?>