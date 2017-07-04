<?php
    
	class KLPlain extends KeyDef
	{
        protected $dd;
        protected $k_head;
        protected $mason;
        
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
            $this->mason = new Masonry();
		}
		
        
		function addHead(&$head, &$dPar)
		{
			$res = $this->getNextSubLvl($dPar);
            
            $node = $res[0];
            $xmlStr = $res[1];
            
			if ( $GLOBALS["imgLiquid"] == false )
            {
                $head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>';
                $GLOBALS["imgLiquid"] = true;
            }
            
            $lay = explode("_", $node['layout']);

            if ( !$GLOBALS["masonry"] )
            {            	
                if (isset($node[0]->attributes()['layout']) )
                {
                    $this->mason->setPaddingVert(0);
                    $this->mason->setPaddingHori(2);

                    if ($node[0]->attributes()['layout'] == "grid_9_7_5_4_2_h2_vl")
                        $this->mason->setVertLines(TRUE, "#989898");
                    
                    if(isset($node[0]->attributes()['paddingV']))
                        $this->mason->setPaddingVert( intval($node[0]->attributes()['paddingV']) );
                    
                    if(isset($node[0]->attributes()['paddingH']))
                        $this->mason->setPaddingHori( intval($node[0]->attributes()['paddingH']) );
                }
                
                $head['base'] .= $this->mason->loadJs();
                $head['jqDocReady'] .= $this->mason->jInit($dPar);

                $GLOBALS["masonry"] = true;
                $this->k_head = &$head;
            }
        }

        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $res = $this->getNextSubLvl($dPar);
            $node = $res[0];
            $xmlStr = $res[1];

            // check level menu
            $subMen = new KLMenu();
            $subMen->draw($head, $body, $xmlItem, $dPar);
            
            
            // interpret layoutStyle
            $lay = explode("_", $node['layout']);

            $heightCfg = array();
            $heightCfg['h0'] = array(280, 260, 240, 220, 200, 140);
            $heightCfg['h1'] = array(200, 200, 200, 200, 200, 200);
            $heightCfg['h2'] = array(140, 140, 140, 140, 140, 140);
            $heightCfg['h3'] = array(200, 200, 140, 140, 140, 120);
            
            $safety = '0.0';
                        
            // check layout
            if($lay[0] == "default" || $lay[0] == "info")
            {
                $lay = array('3', '3', '3', '3', '2', '1');
                setCrushOpts($this->mason, $dPar, $lay, $heightCfg, 'h0', '0.0');
                $this->drawDefault($head, $body['sec2'], $node, $xmlStr, $dPar);
            } else
            {
                if (sizeof($lay) > 1)
                    setCrushOpts($this->mason, $dPar, $lay, $heightCfg, $lay[6], '0.0');
                $this->drawGrid($head, $body['sec2'], $node, $xmlStr, $dPar);
            }
        }
        
        
        //--------------- draw methods ---------------------------------------------
        
        function drawGrid(&$head, &$body, &$node, $xmlStr, &$dPar)
        {
            $lang = $dPar->lang;
            
            $body .= '<div id="mason-cont" class="masonry">';
            $body .= '<div class="g-sizer"></div><div class="g-gutter"></div>';
            
           // $this->drawSubMenu($body, $node, $xmlStr, $dPar);
            
            // draw all children
            $i = 0;
            foreach ($node->children() as $sub)
            {
                $tagName = $sub->getName();
                
                if ( !(strlen( $tagName ) < 4 && $tagName[0] == "l") && $tagName != "name")
                {
                    $gStyle = "col1_row1";

                    if ( isset($sub->attributes()['gridStyle']) )
                        $gStyle = $sub->attributes()['gridStyle'];
                    
                    if ( isset($sub->attributes()['gridStyle2']) )
                        $gStyle .= " ".$sub->attributes()['gridStyle2'];

                    if ( $tagName == "cont" ) {
                        $className = "KText";
                    } else {
                        $className = "K".ucfirst($tagName);
                    }
                    
                    $obj = new $className;
                    $obj->addHead( $this->k_head, $dPar );
                    //                    $dPar->subLinkPath = $node['short']."/";
                    $dPar->subLinkPath = "";
                    $dPar->isFirstInRow = true;

                    // get all arguments
                    if (sizeof($sub->attributes()) > 0)
                    {
                        $dPar->args = (array) $sub->attributes();
                        $dPar->args = $dPar->args['@attributes'];
                    }

                    // check if the object uses the grid
                    $useGridInd = array_key_exists("useGrid", $dPar->args);
                    if ($useGridInd)
                    	$obj->useGrid = ($dPar->args["useGrid"] == "1") ? TRUE : FALSE;
                    
                    // draw the object inside the content flow
                    if (!$obj->isHidden)
                    {
                        $insert = false;

                        if( isset($sub->attributes()['insertCol']) && $sub->attributes()['insertCol'] != "col0")
                        {
                            // insert item
                            $insCol = $sub->attributes()['insertCol'];
                            $gsExp = explode("_", $insCol);
                            
                            if(sizeof($gsExp) > 1)
                            {
                                $insCol = substr($insCol, 0, strrpos($insCol, "_"));                                
                                if($gsExp[ sizeof($gsExp)-1 ] == "left") 
                                {
                                    $body .= '<div id="mIt_'.$i.'" class="';
                                    if ($obj->useGrid) $body .= 'masonry item ';
									$body .= $insCol.'" style="height:100%;">&nbsp</div>';
                                    $i++;
                                } else{
                                    $insert = true;
                                }
                            }
                        }

                        if (!$obj->isMultMasonItems) {
                        	$body .= '<div id="mIt_'.$i.'" class="';
                            if ($obj->useGrid) $body .= 'masonry item ';
                            $body .= $gStyle.'">';
                        }
                        
                        $obj->draw($head, $body, $sub, $dPar);
                        
                        if (!$obj->isMultMasonItems) $body .= '</div>';
                     
                        if($insert) 
                        {
                            $i++;
                            $body .= '<div id="mIt_'.$i.'" class="';
                            if ($obj->useGrid) $body .= 'masonry item ';
							$body .= $insCol.'">&nbsp</div>';
                        }
                        
                        $i++;
                    } else
                    {
                        // draw the object outside the content flow
                        $obj->add($body, $sub, $dPar);
                    }
                }
            }
            $body .= '</div>';
        }
        
        
        //----------------------------------------------------------

        function drawDefault(&$head, &$body, &$node, $xmlStr, &$dPar)
        {
            // draw all children
            foreach ($node->children() as $sub)
            {
                $tagName = $sub->getName();
                
                if ( !(strlen( $tagName ) < 4 && $tagName[0] == "l") && $tagName != "name"  )
                {
                    if ( $tagName == "cont" )
                    {
                        $className = "KText";
                    } else {
                        $className = "K".ucfirst($tagName);
                    }
                    
                    $obj = new $className;
                    $obj->addHead( $this->k_head, $dPar );
                    
                    // hier hack wegen inkonsequenter hierarchie?
//                    $dPar->subLinkPath = $node['short']."/";
                    
                    // get all arguments
                    if (sizeof($sub->attributes()) > 0)
                    {
                        $dPar->args = (array) $sub->attributes();
                        $dPar->args = $dPar->args['@attributes'];
                    }
                    
                    $obj->draw($head, $body, $sub, $dPar);
                }
            }
        }

        //----------------------------------------------------------

        function drawSubMenu(&$body, &$node, &$xmlStr, &$dPar)
        {
            // draw sub menu if there is one
            if((bool)($node->attributes()['submenu'] != ""))
            {
                $body .= '<div class="';
                if ($obj->useGrid) $body .= 'masonry item ';
                $body .= 'col1_2_2_1_1">';
                
                // Headline of Chapter
                $headName = $dPar->xt->xml->xpath($xmlStr);
                $headName = $headName[0]->xpath("parent::*/name/".$dPar->lang);
                if ( sizeof($headName) > 0 )
                	$headName = $headName[0];
                else $headName = "";
                
//                 if(strlen($headName[0]) != 0)
//                     $body .= '<h3 class="padSubHead">'.strtoupper( $headName[0] ).'</h3>';

				$body .= '<ul class="chaptMen">';
                
                for ($i=0;$i<sizeof($dPar->subNavList)-1;$i++)
                {
                    $body .= '<li class="chaptMen ';
                    if($i != sizeof($dPar->subNavList)-2)
                        $body .= 'paddR ';
                    $body .= '"><a class="lvlMen" href="'.$dPar->subNavList[$i]['link'].'">'.$dPar->subNavList[$i]['name'].'</a></li>';
                }
                
                $body .= '</ul>';

                $body .= '</div>';
            }
        }
        
        //----------------------------------------------------------

        function getNextSubLvl(&$dPar)
        {
            $xmlStr = $dPar->xmlStr;
            $actLvl = $dPar->actLevel;
            
            $node = $dPar->xt->xml->xpath($xmlStr);
            if (sizeof($node) > 0) $node = $node[0];

            // search for next level with content
            $ind = 0;
            $subLvlStr = "l".($actLvl);
            
            while (sizeof($node) != 0
                   && sizeof($node->children()) == 2
                   && sizeof($node->$subLvlStr) > 0
                   && sizeof($node->name) > 0
                   && ($actLvl + $ind) < $dPar->nrLevels )
            {
                if ( $ind == 0 && $xmlStr[ strlen($xmlStr) -1 ] == "]" )
                {
                    $xmlStr .= "/l".($actLvl + $ind);
                } else {
                    if ( $xmlStr[ strlen($xmlStr) -1 ] == "]" )
                        $xmlStr .= "/l".($actLvl + $ind);
                    
                    $shortQ = $xt->xml->xpath( $xmlStr."[1]/@short" );
                    $xmlStr .= "[@short='".$shortQ[0]."']";
                }
                
                $node = $dPar->xt->xml->xpath( $xmlStr );
                $node = $node[0];
                $subLvlStr = "l".($actLvl+$ind+1);
                $ind++;
            }
            return array($node, $xmlStr);
        }
    }
?>