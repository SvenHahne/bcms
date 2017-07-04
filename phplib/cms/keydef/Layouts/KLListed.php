<?php
    
	class KLListed extends KeyDef
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
			$this->subKeys = array(new KAShort(), new KAImage(), new KName(),
                                   new KAGenSelect("style", array("Stil", "Estilo", "Style"), array()));
            $this->dispText = array("place", "city");
            $this->dispStyle = 0;

            $this->dd = new KEJDropDown();
            $this->mason = new Masonry();
            $this->mason->setPaddingVert(20);
            $this->mason->setPaddingHori(10);
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
        
            $this->dd->addHead($head);
        }
    
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {            
            $xt = &$dPar->xt;
            $lang = (string)$dPar->lang;
            if ( isset($_GET['dstyle']) ) $this->dispStyle = $_GET['dstyle'];
            
            // make a dropdown menu
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
                        
            if ( isset($_GET['sel']) ) $sel = $_GET['sel']; else $sel = 0;
            
            // make a headline with the name of the Entry
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
                if ($i == $sel) $body['sec1'] .= "class='blue' ";
                $body['sec1'] .= "href='".$bT."&sel=".$i."' target='_self'>".$list[$i]['name']."</a></div>";
                if ($i != sizeof($list)-1)
                    $body['sec1'] .= "<div class='subNavSep'>|</div>";
            }
            
            $body['sec1'] .= "</div>";
            $body['sec1'] .= "</div>";
            
            
            // print entries
            $selShort = "";
            if ( isset($_GET['sel']) )
            {
                $selShort = $list[$_GET['sel']]['short'];
            } else {
                $selShort = $list[0]['short'];
            }
            
            $dPar->xmlStr .= "/l".$dPar->actLevel."[@short='".$selShort."']";
            $dPar->linkPath .= $selShort."/";
            $xmlItem = $xmlItem->xpath("l".$dPar->actLevel."[@short='".$selShort."']");
            $xmlItem = $xmlItem[0];
            
            $this->drawList($xmlItem, $body['sec1'], $lang, $dPar);
        }
    
        function drawList(&$xmlItem, &$body, $lang, &$dPar)
        {
            // style switching temporarly deactivated
            $this->dispStyle = 1;
            
            // set nr of columns
            switch ( $this->dispStyle == 0 )
            {
                case 0:                    
                    csscrush_set('options', array('vars' => array('nrColsRetina' => '3',
                                                                  'nrColsStd' => '3',
                                                                  'nrColsIpad' => '3',
                                                                  'nrColsIphone' => '2',
                                                                  'nrColsSmartp' => '1',
                                                                  'itemHeightRet' => '320',
                                                                  'itemHeightStd' => '230',
                                                                  'itemHeightIpa' => '190',
                                                                  'itemHeightIph' => '170',
                                                                  'itemHeightSma' => '220',
                                                                  'safety' => '0.0') ) );
                    break;
                case 1:
                    csscrush_set('options', array('vars' => array('nrColsRetina' => '1',
                                                                  'nrColsStd' => '1',
                                                                  'nrColsIpad' => '1',
                                                                  'nrColsIphone' => '1',
                                                                  'nrColsSmartp' => '1',
                                                                  'itemHeightRet' => '100',
                                                                  'itemHeightStd' => '100',
                                                                  'itemHeightIpa' => '100',
                                                                  'itemHeightIph' => '100',
                                                                  'itemHeightSma' => '100',
                                                                  'safety' => '0.0') ) );
                    break;
            }
            
            $body .= '<div id="mason-cont" class="masonry">';
            $body .= '<div class="g-sizer"></div>';
            
            foreach ($xmlItem->children() as $sub)
            {
                if ( isset($sub->name) )
                {
                    $link = $this->getHref($dPar->linkPath, $sub['short']);

                    $linkAttr = ""; foreach ($dPar->linkAttr as $k => $v ) $linkAttr .= "&".$k."=".$v;

                    switch ( $this->dispStyle == 0 )
                    {
                        case 0:
                            $body .= '<div class="masonry item col1_row1">';
                            $body .= '<div class="refListItem imgLiquidFill">';
                            $body .= '<a class="lvlMen" href="'.$link.$linkAttr.'">';
                            
                            $body .= '<div class="refListHover">';
                    
                                $body .= '<div class="refListHoverPos">';
                                    $body .= '<div class="refListHoverText">'.$sub->name->$lang;
                                    $body .= '<hr class="refListHoverHr">';
                                    $body .= '<div class="refListHeadSmall">';
                                    $body .= $this->formatSubAttr($sub->attributes());
                                    $body .= '</div>';
                                $body .= '</div>';
                            $body .= '</div>';
                            
                            $body .= '</div>';
                            
                            
                            if ( !isset($sub['image']) || $sub['image'] == "" ){
                                $pic = $this->getSlideshowPic($dPar, $sub);
                            } else {
                                $pic = $sub['image'];
                            }
                            
                            $body .= '<img src="pic/placeholder.png" data-original="'.$GLOBALS['site'].$GLOBALS['backupUrl'].$pic.'">';
                            
                            $body .= '</a>';
                            $body .= '</div>';
                            $body .= '</div>';
                            
                            break;
                            
                        case 1:
                            $body .= '<div class="masonry item col1_row1">';
                            
                            $body .= '<a class="lvlMen" href="'.$link.$linkAttr.'">';

                           // $body .= '<div class="klmListHover"></div>';

                            $body .= '<div class="klmListItem">';
                            
                            $body .= '<div class="klmListItemCellImg">';
                            $body .= '<div class="imgLiquidFill imgThumb">';
                            if ( !isset($sub['image']) || $sub['image'] == "" ) {
                                $pic = $this->getSlideshowPic($dPar, $sub);
                            } else {
                                $pic = $sub['image'];
                            }
                            $body .= '<img src="pic/placeholder.png" data-original="'.$GLOBALS['site'].$GLOBALS['backupUrl'].$pic.'">';
                            
                            $body .= '</div>';
                            $body .= '</div>';
                            
                            $body .= '<div class="klmListItemCell">'.$sub->name->$lang.'</div>';
                            $body .= '<div class="klmListItemCell klmListItemCellRight klmListHeadSmall">';

                            $body .= $this->formatSubAttr($sub->attributes());
                            $body .= '</div>';
                            $body .= '</div>';

                            $body .= '</a>';
                            $body .= '</div>';
                            break;
                    }
                }
            }

            $body .= '</div>';            
        }
    
        function getGalleryPic(&$dPar, &$sub)
        {
            $galPicPath = "pic/gallery/".$GLOBALS['userUrl'].$dPar->linkPath.$sub['short']."/";
            
            // if there is a gallery select the first item
            if ( isset($sub->gallery) && count($sub->gallery) > 0 )
                $galPicPath .= $sub->gallery->image;
            
            return $galPicPath;
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
    }
?>