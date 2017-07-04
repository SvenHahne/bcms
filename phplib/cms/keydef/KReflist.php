<?php
		
	class KReflist extends KeyDef
	{
		protected $cascMen;
		protected $argMen = array();
        protected $dispText;
        public $setCrush = true;
        protected $maxNrCols = 3;
        protected $packery;
        protected $verticalAlign = 35; // in percent
        
        function __construct0() { $this->init(); }

		function __construct3($a1,$a2,$a3)
		{
			$this->subArgs = $a1;
			$this->subArgsKeyDef = $a2;
			$this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
            $this->init();
		}

        function __construct4($a1,$a2,$a3,$a4)
		{
			$this->subArgs = $a1;
			$this->subArgsKeyDef = $a2;
			$this->argsVals = $a3;
            $this->argsStdVal = $a4;
            foreach ($this->argsVals as $val)
                array_push($this->argsShow, true);
            $this->init();
		}

        function __construct6($a1,$a2,$a3, $a4,$a5,$a6)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
			$this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
			$this->subArgs = $a4;
			$this->subArgsKeyDef = $a5;
			$this->subArgsStdVal = $a6;
            $this->init();
		}
        
        function init()
		{
			$this->xmlName = "reflist";
			$this->htmlName['de'] = "Verweis-Liste";
			$this->htmlName['es'] = "Lista de Referencia";
			$this->htmlName['en'] = "Reference-List";
			$this->type = "ref";
			$this->postId = "reflist";
			$this->isMultType = TRUE;
			$this->multIsCDATA = TRUE;
            $this->dispText = array("place", "city");
            
            $this->packery = new Packery();
            $this->packery->setPaddingVert(20);
            $this->packery->setPaddingHori(10);
        }
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
			$this->cascMen = new KECascadingMenu($this, $cont, $gPar, TRUE);
		}

		function addToEditor(&$gPar, &$cont) 
		{
			$basePName = $gPar->post_name;
            $val = $gPar->val;

            $this->cascMen->addToEditor();

            $cont['body'] .= "<input class='but plus' type='submit' name='newMultKeyEntry_".$basePName."' value=''>";
			
			$cont['body'] .= "<div class='table' style='table-layout:auto;'>";			
			$cont['body'] .= '<div class="row">';

			// make inner table for dropdown menus
			$cont['body'] .= '<div class="cell"><div class="table">';
				
			for ($l=0;$l<sizeof( $gPar->medialist );$l++) 
			{
				$id = "".(floor($l/10)).($l%10);				
				// create fake dropdown menu, which will be replaced onclick			
				$itemName = "";
				if ( $gPar->medialist[$l] != "" )
				{
					$q = $gPar->xt->xpath->query( $gPar->medialist[$l]."/name/".$gPar->lang );
					if ( $q->length > 0 ) $itemName = $q->item(0)->nodeValue;
				}
                
                // name wird von KECascadingMenu benötigt um die auswahl korrekt zum formular zu übergeben
                $gPar->post_name = $basePName.'_'.$l;
                if (strlen($itemName) > 16)
                	$str = substr($itemName, 0, 16);
                else
                	$str = $itemName;
                
                	
                $cont['body'] .= '<div class="row">';
                	
                
                $cont['body'] .= '<div class="cell">';
				$cont['body'] .= '<select name="change_'.$gPar->post_name.'" class="ddmen" id="ddmen_sel_'.$this->cascMen->id.'_'.$id.'">';
				$cont['body'] .= '<option>'. $str.'</option>';
				$cont['body'] .= '</select>';
				$cont['body'] .= '</div>';

				
				$argMen[$l] = array();
				for ($i=0;$i<sizeof($this->subArgs);$i++) 
				{
					$cont['body'] .= '<div class="cell">';
						
					if ($this->subArgsKeyDef[$i] == "KEDropDown")
					{
						$gPar->drawTake = false;
						$gPar->post_name = $basePName.'_'.$l.'_@'.$this->subArgs[$i];
						$q = $gPar->xt->xpath->query($gPar->xmlStr."/".$this->type."[".($l+1)."]/@".$this->subArgs[$i] );
						if ( $q->length > 0 ) $gPar->val = $q->item(0)->nodeValue;
						$argMen[$l][$i] = new $this->subArgsKeyDef[$i]($this, $cont, $gPar, $this->subArgsStdVal[$i]);
					}
					
					$cont['body'] .= '</div>';
				}
				
				
				$cont['body'] .= '<div class="cell" style="vertical-align:middle">';
				// hidden input feld to submit the value
				$cont['body'] .= '<input type="hidden" name="change_'.$basePName.'_'.$l.'" value="'.$gPar->medialist[$l].'">';
				$cont['body'] .= '<input class="up but shiftUp2" type="submit" name="shiftUp_'.$basePName.'_'.$l.'" value="">';
				$cont['body'] .= '<input class="down but shiftUp2" type="submit" name="shiftDown_'.$basePName.'_'.$l.'" value="">';
				$cont['body'] .= '<input class="but trash shiftUp2"  type="submit" name="remove_'.$basePName.'_'.$l.'" value="">';				
				$cont['body'] .= '</div>';	// end cell				
				
				
				$cont['body'] .= '</div>';	// end row
			}
            
			$cont['body'] .= '</div>'; // close inner table
			$cont['body'] .= '</div>'; // close cell
			$cont['body'] .= '</div>'; // close row
							
            $gPar->val = $val;
            $gPar->post_name = $basePName;
            
            
            $cont['body'] .= '<div class="row"><div class="cell"><div class="table">';
            
            // draw arguments
            for ($k=0;$k<(sizeof($this->args) / 3);$k++)
			{
				for ($l=0;$l<min(sizeof($this->args) - ($k*3), 3);$l++)
				{
					$ind = $k *3 + $l;
                    
                    $cont['body'] .= '<div class="row">';
                    $cont['body'] .= '<div class="cell">'.$this->args[$ind].':</div><div class="cell">';
                    					
                    if( sizeof($this->argsKeyDef) != 0 && $this->argsKeyDef[$ind] != "" )
                    {
                        // schmutzig, name wird als referenz übergeben und muss später wieder korrigiert werden...
                        $gPar->post_name = $basePName."_@".$this->args[$ind];
                        $gPar->drawTake = false;
                        $gPar->val = $val[ $this->args[$ind] ];
                        $aKey = new $this->argsKeyDef[$ind]($this, $cont, $gPar, $this->argsVals[$ind]);
                        
                    } else
                    {
                        $cont['body'] .=  "<input name='change_".$basePName."_@".$this->args[$ind]."' type='text' ";
                        $cont['body'] .=  "size='45' maxlength='200' value='".$val[ $this->args[$ind] ]."'>";
                    }
                    
                    $cont['body'] .= '</div>';	// end cell
                    $cont['body'] .= '</div>';  // end row
				}
			}

			$cont['body'] .= '</div>'; // close inner table
			$cont['body'] .= '</div>'; // close cell
			$cont['body'] .= '</div>'; // close row

			$gPar->post_name = $basePName;
            $gPar->val = $val;

            
			$cont['body'] .=  "</div>"; // end table

		}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

//             if ( $GLOBALS["imgLiquid"] == false )
// 			{
// 				$head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>';                                
// 				$GLOBALS["imgLiquid"] = true;
// 			}
//             $head['jqDocReady'] .= '$(".imgLiquidFill").imgLiquid({fill: true, horizontalAlign: "center", verticalAlign: "'.($this->verticalAlign).'%", useBackgroundSize: "false"});';


// 			if ( !$GLOBALS["packery"] )
// 			{
                $head['base'] .= $this->packery->loadJs();
                $head['jqDocReady'] .= $this->packery->jInit($dPar);
				$GLOBALS["packery"] = true;
//			}

        }
		
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
        	$ind = 0;
        	$notMax = true;
        	$maxNrResults = 9;
        	$getNrResults = 12; // sollte immer etwas mehr sein, damit das vorkommen
        	// des eintrages, von dem aus gesucht wird herausgefiltert werden kann
        
        	if ($xmlItem['style'] == "circle")
        	{
        		$dPar->newMason = false;
        		$body .= '<div class="circleLayout">';
        
        		foreach( $xmlItem->children() as $key => $value )
        		{
        			if ( $value != "" && $notMax)
        			{
        				$q = $dPar->xt->xml->xpath($value."/name/".$dPar->lang);
        
        				$body .= '<div class="circWrapper">';
        				$body .= '<div class="circMain lightGrayBack"><div class="circMainTable">';
        				$body .= '<span class="circlesInner">'.mb_strtoupper($q[0]).'</span></div>';
        				$body .= '</div>';
        				$body .= '</div>';
        
        				if ( isset($xmlItem['max']) && $xmlItem['max'] <= ($ind+1) ) $notMax = false;
        				$ind++;
        			}
        		}
        
        		$body .= '</div>';
        	
        	} elseif($xmlItem['style'] == "text-list")
        	{
        		foreach( $xmlItem->children() as $key => $value )
        		{
        			if ( $value != "" && $notMax)
        			{
        				$q = $dPar->xt->xml->xpath($value."/name/".$dPar->lang);
        		
        				$body .= '<a class="lvlMen" href="';
        				$body .= rewriteLink( getHrefFromXmlStr($dPar->xt, "".$value, $dPar->lang), $dPar->xt, $dPar->keys).'">';
        				$body .= '<div class="refListTextListItem">';
        				$body .= mb_strtoupper($q[0]);
        				$body .= '</div>';
        				$body .= '</a>';
        		
        				if ( isset($xmlItem['max']) && $xmlItem['max'] <= ($ind+1) ) $notMax = false;
        				$ind++;
        			}
        		}
        		
        	} else
        	{
        		$body .= '<div class="nText_90">'.mb_strtoupper($xmlItem['head-'.$dPar->lang]).'</div>';
        		$body .= '<hr class="refList" id="lowerLimit">';
        
        		if ($dPar->newMason)
        		{
        			$body .= '<div id="mason-cont" class="masonry">';
        			$body .= '<div class="g-sizer"></div><div class="g-gutter"></div>';
        		}
        
        		// zeichen das zur referenz gehörige Bild
        		// entweder das erste bild einer galerie des verweises
        		// oder das image-attribute des levels
        		$ind = 0;
        		
        		foreach( $xmlItem->children() as $key => $value )
        		{
        			 
        			if ($value != "")
        				$this->getRefCont($body, $xmlItem, $dPar, $value, $value['refmode'], $notMax, $ind);
        
        				
        				// wenn keine konrekter Pfad gesetzt ist, aber die Option keywords angewählt ist
        				// führe eine suche durch
        				if ($value == "" || $value['refmode'] == "keywords")
        				{
        					// if only keywords are selected, get the keywords from the current level and do the search
        					$bSearch = new BDispSearch($dPar->xt, $dPar->keys, $dPar->lang, $getNrResults, "string");
        
        					$searchString = "";
        					$parent = $xmlItem->xpath("parent::*");
        					$searchString .= " ".$parent[0]['keywords']; // has to be string
        					$searchString .= " ".$parent[0]['keywords']; // add the keyword two times for higher priority
        
        					// look also for
        					$searchAlso = array("name", "place", "city", "size", "year");
        					foreach($searchAlso as $strVal)
        					{
        						if ($strVal != "name" )
        						{
        							$searchString .= " ".$parent[0][$strVal];
        						} else {
        							$name = $parent[0]->xpath("name/".$dPar->lang);
        							$searchString .= " ".$name[0];
        						}
        					}
        
        					// restict search according to $GLOBALS["searchRestrict"]
        					// input with @short attributes
        					$res = $bSearch->proc($searchString, $GLOBALS["searchRestrict"], $dPar->xmlStr);
        
        					// build an xmlelement with the results
        					$cnt = 0;
        					foreach($res as $val)
        					{
        						if ( $cnt < $maxNrResults )
        						{
        							$newXML = new SimpleXMLElement("<ref></ref>");
        							$newXML->addAttribute('refmode', 'sameOne');
        							$newXML->addAttribute('type', 'col1');
        							$newXML[0] = $val;
                							 
        							$this->getRefCont($body, $xmlItem, $dPar, $newXML, "sameOne", $notMax, $ind);
        							$cnt++;
        						}
        					}
        				}
        		}
        
        		if ($dPar->newMason) $body .= '</div>';
        	}
        }
        
        //------------------------------------------------------------------------------
        // nimmt einen xmlPfad als $value und durchsucht den zugehörigen Eintrag
        // nach Slideshows. Extrahiert die Bilder und Informationen
        
        function getRefCont(&$body, &$xmlItem, &$dPar, &$value, $refMode, &$notMax, &$ind)
        {
        	 
        	$lang = $dPar->lang;
        
        	// first look for slideshows
        	$rKey = "KSlideshow";
        	$inst = new $rKey();
        	$up = $inst->uploadPath."/";
        	$list = resolveRef($dPar->xt, $value, $rKey, $refMode, "@image", $lang);
        
        	if ($refMode == "image") $up = "pic/";
        
        	// if nothing found look for @image attributes
        	if (sizeof($list) == 0)
        	{
        		$rKey = "";
        		$up = "pic/";
        		$list = resolveRef($dPar->xt, $value, $rKey, $refMode, "@image", $lang);
        	}
        
        	
        	foreach($list as $item)
        	{
        		if ($notMax)
        		{
        			$item['url'] = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$up.$item['url'].$item['value'];
        			$item['value'] = (array) $item['value'];
        			$item['value']['place'] = "";
        
        			// get name and place from the level attributes
        			if ($refMode == "image")
        			{
        				$item['value']['name'] = (string) $item['xmlElm'][0]->name->$lang;
        			} else {
        				$par = $item['xmlElm'][0]->xpath("parent::*");
        				$item['value']['name'] = $par[0]->name->$lang;
        				$p = $par[0]->xpath("@place");
        				if(sizeof($p) > 0) $item['value']['place'] = $p[0];
        			}
        
        			// href fehlt noch für link...
        			switch($xmlItem['style'])
        			{
        				case "block" :
        					$this->asBlock($body, $value, $dPar, $item);
        					break;
        				case "list" :
        					$this->asList($body, $value, $dPar, $item);
        					break;
        				case "circle" :
        					$this->asCircle($body, $value, $dPar, $item);
        					break;
        			}
        
        			if (isset($xmlItem['max']) && $xmlItem['max'] <= ($ind+1) ) $notMax = false;
        			$ind++;
        		}
        	}
        }
        
        // --------------- draw methods ---------------------------------------------
        
        
        function asCircle(&$body, &$value, &$dPar, &$item)
        {
        	$blockType = $value->attributes()['type'];
        
        	$body .= '<div class="masonry item '.$value['type'].'">';
        	$body .= '<a href="'.rewriteLink($item['href'], $dPar->xt, $dPar->keys).'">';
        	$body .= '<div class="refListItem imgLiquidFill">';
        
        	if ($blockType != "col1_row050")
        	{
        		$body .= '<div class="refListHover">';
        	} else {
        		$body .= '<div class="refListHover05">';
        	}
        
        	$body .= '<div class="refListHoverPos">';
        	if ($blockType != "col1_row050")
        	{
        		$body .= '<div class="refListHoverText">'.$item['value']['name'].'</div>';
        		$body .= '<hr class="refListHoverHr">';
        		$body .= '<div class="refListHoverText">'.$item['value']['place'].'</div>';
        	}
        	$body .= '</div>';
        
        	$body .= '</div>';
        
        	$body .= '<img src="'.$item['url'].'" width="100%" height="auto">';
        	$body .= '</div>';
        	$body .= '</a>';
        	$body .= '</div>';
        }
        
        
        function asBlock(&$body, &$value, &$dPar, &$item)
        {
        	$blockAspW = 16;
        	$blockAspH = 9;
        
        	$blockType = $value->attributes()['type'];
        	$useFixedAspectRatio = false;
        	
        	if (sizeof(explode("_", $blockType)) == 1 || $blockType == "col1_050")
        		$useFixedAspectRatio = true;
        
        		$body .= '<div class="masonry item '.$value['type'].'">';
        
        		$body .= '<a class="mItem" href="'.rewriteLink($item['href'], $dPar->xt, $dPar->keys).'">';
        
        		if ($useFixedAspectRatio)
        		{
        			if( $blockType == "col1_050")
        			{
        				$blockAspH /= 2;
        				$body .= '<div class="refListItemCont050">';
        			} else {
        				$body .= '<div class="refListItemCont">';
        			}
        		}
        
        		$body .= '<div class="refListItem"';
        
        		// for gridStyles without height definition, use special style to maintain aspect ratio
        		if ($useFixedAspectRatio)
        			$body .= 'style="position:absolute;"';
        
        		$body .= '>';
        
        		if ($blockType != "col1_row050" && $blockType != "col1_050")
        		{
        			$body .= '<div class="refListHover">';
       			} else {
       				$body .= '<div class="refListHover05">';
       			}
        
       			$body .= '<div class="refListHoverPos">';
        
       			if ($blockType != "col1_row050" && $blockType != "col1_050")
       			{
       				$body .= '<div class="refListHoverText">'.$item['value']['name'].'</div>';
       				$body .= '<hr class="refListHoverHr">';
       				$body .= '<div class="refListHoverText">'.$item['value']['place'].'</div>';
       			}
       			$body .= '</div>';
        
       			$body .= '</div>';
        
        			
       			// images are displayed with a fixed proportionality (16:9)
       			// get the size of the image and calculate and offset
       			$body .= imgFluid($item['url'], $blockAspW, $blockAspH);
        			
       			$body .= '</div>';
        
       			if ($useFixedAspectRatio)
       				$body .= '</div>';
        
        			$body .= '</a>';
        			$body .= '</div>';
        }
        
        
        function asList(&$body, &$value, &$dPar, &$item)
        {
        	$body .= '<div class="masonry item refL_col5_row1">';
        
        	$body .= '<a class="lvlMen" href="'.rewriteLink($item['href'], $dPar->xt, $dPar->keys).'">';
        
        	$body .= '<div class="klmListItem">';
        
        	$body .= '<div class="klmListItemCellImg">';
        	$body .= '<div class="imgThumb">';
        	//            $body .= '<div class="imgLiquidFill imgThumb">';
        
        	$body .= '<img src="'.$item['url'].'" style="width:100%;margin-top:-'.$heightDiff.'%" >';
        	$body .= '</div>';
        	$body .= '</div>';
        
        	$body .= '<div class="klmListItemCell">'.$item['value']['name'].'</div>';
        	$body .= '<div class="klmListItemCell klmListItemCellRight klmListHeadSmall">';
        
        	$getArg = $dPar->xt->xml->xpath($value);
        	$getArg = $getArg[0];
        	$text = "";
        	$text .= $getArg['place'].', '.$getArg['city'].', '.$getArg['size'].'m&sup2;, '.$getArg['year'];
        
        	$body .= $text.'</div>';
        	$body .= '</div>';
        
        	$body .= '</a>';
        	$body .= '</div>';
        }
    }
?>