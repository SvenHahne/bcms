<?php
    
	class KLSearchResult extends KeyDef
	{
        protected $dd;
        protected $dispText;
        protected $dispStyle;
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
			$this->subKeys = array(new KAShort(), new KAImage(), new KName(), new KAStyle(array()));
            $this->dispText = array("place", "city");
            $this->mason = new Masonry();
            $this->msgs = array();
            $this->msgs['de'] = array("SUCHERGEBNIS", "Leider wurde nichts gefunden für die Suchanfrage:", "Leere Suchanfrage", "-> zurück", "Projekte mit ");
            $this->msgs['es'] = array("EL RESULTADO DE LA BÚSQUEDA", "Lo sentimos, pero no se ha encontrado nada para:", "La consulta está vacía", "-> anterior", "Proyectos relacionados con ");
            $this->msgs['en'] = array("SEARCH RESULT", "Sorry, no results were found for:", "Empty search request", "-> go back", "Projects related with ");
		}
		
        function addHead(&$head, &$dPar)
		{
            if ( !$GLOBALS["masonry"] )
            {
                $this->mason->setPaddingVert(2);
                $this->mason->setPaddingHori(2);

                $head['base'] .= $this->mason->loadJs();
                $head['jqDocReady'] .= $this->mason->jInit($dPar);
                
                $GLOBALS["masonry"] = true;
                $this->head = &$head;
            }
            
            if ( $GLOBALS["klm"] == false ) {
                $head['base'] .= '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/kLevelStd.css" />';
                $GLOBALS["klm"] = true;
            }        
        }
    
    
        function draw(&$head, &$body, &$nodes, &$dPar)
        {
        	$xt = &$dPar->xt;
            $lang = (string)$dPar->lang;
           
            $dPar->subNavList['selLvlShort'] = "";
            $dPar->subNavList[0]['link'] = "";
            $dPar->subNavList[0]['short'] = "";
                                    
            if ( isset($_GET['isLink']) )
            {
                $dPar->subNavList[0]['name'] = $this->msgs[$dPar->lang][4]." ".$_GET['search'];
            } else {
                $dPar->subNavList[0]['name'] = $this->msgs[$dPar->lang][0];
            }
            
            
//print_r( $dPar->subNavList[0]['name']);

			if (sizeof($nodes) == 2 && !is_a($nodes[0], "DOMElement") && !is_a($nodes[0], "DOMNodeList") )
			{
				if ( $nodes[1] != "" )
				{
					$body['sec1'] .= "<div class='nText_90'>".$this->msgs[$dPar->lang][1]." \"".$nodes[1]."\"</div><br>";
				} else {
					$body['sec1'] .= "<div class='nText_90'>".$this->msgs[$dPar->lang][2]."</div>";
				}
			} else {
                $this->drawBlocks($nodes, $body['sec1'], $lang, $dPar);
			}
            

            // go back button
            $body['sec1'] .= '<br><a class="lang blue" href="';
            
            if (isset($_GET['from_url'])) $body['sec1'] .= rewriteLink($_GET['from_url'], $dPar->xt, $dPar->keys);
            
            $body['sec1'] .= '">'.$this->msgs[$dPar->lang][3].'</a>';
        }
    
        
        function drawBlocks(&$nodes, &$body, $lang, &$dPar)
        {
            $lay = array('3', '3', '3', '3', '2', '1');
            $heightCfg = array();
            $heightCfg['h0'] = array('200', '200', '200', '200', '200', '200');
            setCrushOpts($this->mason, $dPar, $lay, $heightCfg, 'h0', '0.0');
            $useFixedAspectRatio = true;

            // draw all children
            $body .= '<div id="mason-cont" class="masonry">';
            $body .= '<div class="g-sizer"></div><div class="g-gutter"></div>';

            foreach ($nodes as $sub)
            {
                $name = $sub->getElementsByTagName('name')->item(0)->getElementsByTagName($lang)->item(0)->nodeValue;
                $short = $sub->getAttribute("short");
                $linkAttr = ""; foreach ($dPar->linkAttr as $k => $v ) $linkAttr .= "&".$k."=".$v;
                
                if ( $sub->getAttribute('image') == "" )
                {
                	$pic = $this->getGalleryPic($dPar, $sub);
                } else {
                	$pic = $sub->getAttribute('image');
                }
                
                if ( $name != "" && file_exists($dPar->clientFolder.$pic))
                {
                	$link = $this->getHref($sub);
                    $link .= $linkAttr;
                    
                    $body .= '<div class="masonry item col1">';
                        $body .= '<a class="mItem" href="'.rewriteLink($link, $dPar->xt, $dPar->keys).'">';
                            $body .= '<div class="refListItemCont">';
                                $body .= '<div class="refListItem" style="position:absolute;">';
                                    $body .= '<div class="refListHover">';
                                        $body .= '<div class="refListHoverPos">';
                                            $body .= '<div class="refListHoverText">'.$name.'</div>';
                                            $body .= '<hr class="refListHoverHr">';
                                            $body .= '<div class="refListHoverText">'.$sub->getAttribute('place').'</div>';
                                        $body .= '</div>';
                                    $body .= '</div>';
  
                                    // images are displayed with a fixed proportionality (16:9)
                                    // get the size of the image and calculate and offset
                                    $body .= imgFluid($GLOBALS['root'].$dPar->clientFolder.$pic);
                                $body .= '</div>';
                            $body .= '</div>';
                        $body .= '</a>';
                    $body .= '</div>';
                }
            }

            $body .= '</div>';
        }
        
        
        function drawList(&$nodes, &$body, $lang, &$dPar)
        {
            // set nr of columns
            $lay = array('1', '1', '1', '1', '1', '1');
            $heightCfg = array();
            $heightCfg['h0'] = array('160', '100', '100', '100', '100', '100');
            setCrushOpts($this->mason, $dPar, $lay, $heightCfg, 'h0', '0.2');

            $body .= '<div id="mason-cont" class="masonry">';
            $body .= '<div class="g-sizer"></div>';
            
            foreach ($nodes as $sub)
            {
                $name = $sub->getElementsByTagName('name')->item(0)->getElementsByTagName($lang)->item(0)->nodeValue;
                $short = $sub->getAttribute("short");
                $linkAttr = ""; foreach ($dPar->linkAttr as $k => $v ) $linkAttr .= "&".$k."=".$v;

                if ( $name != "" )
                {
                    $link = $this->getHref($sub);
                    $link .= $linkAttr;

                    $body .= '<div class="masonry item col1_row1">';
                        
                    $body .= '<a class="lvlMen" href="'.rewriteLink($link, $dPar->xt, $dPar->keys).'">';
                    
                    $body .= '<div class="klmListItem">';
                        
                    $body .= '<div class="klmListItemCellImg">';
                    $body .= '<div class="imgLiquidFill imgThumb">';

                    if ( $sub->getAttribute('image') == "" )
                    {
                        $pic = $this->getGalleryPic($dPar, $sub);
                    } else {
                        $pic = $sub->getAttribute('image');
                    }
                    
                    $body .= '<img src="'.$GLOBALS['root'].$pic.'">';

                    $body .= '</div>';
                    $body .= '</div>';
                        
                    $body .= '<div class="klmListItemCell">'.$name.'</div>';
                    $body .= '<div class="klmListItemCell klmListItemCellRight klmListHeadSmall">';

                    if ( $sub->hasAttributes() )
                    {
                        $text = "";
                        $text .= $sub->getAttribute('place').', '.$sub->getAttribute('city').', '.$sub->getAttribute('size').'m&sup2;, '.$sub->getAttribute('year');
                        
                        $body .= $text;
                    }

                    $body .= '</div>';
                    $body .= '</div>';

                    $body .= '</a>';
                    $body .= '</div>';
                }
            }
            $body .= '</div>';            
        }
    
        function getGalleryPic(&$dPar, &$sub)
        {
            $path = "";
            $node = $sub;
                
            while ($node->tagName != "data")
            {
                if ( $path != "" ) {
                    $path = $node->getAttribute("short")."/".$path;
                } else {
                    $path = $node->getAttribute("short").$path;
                }
                $node = $node->parentNode;
            }
            
            // remove user entry if user levels are in use
            if (isset($GLOBALS["xmlClientLvl"]) && $GLOBALS["xmlClientLvl"] == 0)
            {
                $path = substr($path, strpos($path, "/")+1, strlen($path));
                while (strpos($path, "//") !== false)
                    $path = str_replace("//", "/", $path);
            }
            
            $galPicPath = "pic/slideshow/".$path."/";
            
            $gal = $sub->getElementsByTagName('slideshow');

            // if there is a gallery select the first item
            if ( $gal->length > 0 )
            {
                $img = $gal->item(0)->getElementsByTagName('slimage');
                if ( $img->length > 0 ) $galPicPath .= $img->item(0)->nodeValue;
            }

            return $galPicPath;
        }
    
        function getHref(&$sub)
        {
            $link = "";
            $node = $sub;
            
            while ($node->tagName != "data")
            {
                if ( $link != "" ) {
                    $link = $node->tagName."=".$node->getAttribute("short")."&".$link;
                } else {
                    $link = $node->tagName."=".$node->getAttribute("short").$link;
                }
                $node = $node->parentNode;
            }
            
            $link = $GLOBALS['root']."index.php?".$link;

            return $link;
        }
    }
?>