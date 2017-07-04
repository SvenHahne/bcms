<?php
		
	class KLevelMenItem extends KeyDef
	{
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
//            //            $this->mason = new Masonry();
            $this->mason = new Packery();
        }
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{}
		
        function addHead(&$head, &$dPar)
		{
			if ( $GLOBALS["imgLiquid"] == false )
			{
				$head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>';
				$GLOBALS["imgLiquid"] = true;
			}

            if ( !$GLOBALS["masonry"] )
			{
                $head['base'] .= $this->mason->loadJs();
                $head['jqDocReady'] .= $this->mason->jInit($dPar);
                $GLOBALS["masonry"] = true;
            }
        
			if ( $GLOBALS["kLevelStd"] == false )
			{
				$head['base'] .= '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/kLevelStd.css" />';
				$head['jqDocReady'] .= '
                    $(".imgLiquidFill").imgLiquid({ fill: true, horizontalAlign: "center", verticalAlign: "20%" });
                    $(".imgLF2").imgLiquid({ fill: true, horizontalAlign: "center", verticalAlign: "70%" });
                ';
				$GLOBALS["kLevelStd"] = true;
			}
		
		}

		function drawMen(&$menPar, &$dPar)
		{
            if ( $menPar['style'] == "col1_row1" || $menPar['style'] == "col2_row1"
                || $menPar['style'] == "col2_row2" || $menPar['style'] == "col1_row2" )
			{
                if ( $menPar['i'] == 0 )
                {
                    $menPar['body']['sec1'] .= '<div id="mason-cont" class="masonry">';
                    $menPar['body']['sec1'] .= '<div class="g-sizer"></div><div class="g-gutter"></div>';
                }
                
                $ind = 0;

                $menPar['body']['sec1'] .= '<div class="masonry item '.$menPar['style'].'">';
                $menPar['body']['sec1'] .= '<div class="refListItem imgLiquidFill">';
                $menPar['body']['sec1'] .= '<a class="lvlMen" href="'.rewriteLink($menPar['linkPath'], $dPar->xt, $dPar->keys).'">';

                $menPar['body']['sec1'] .= '<div class="refListHover"></div>';
                $menPar['body']['sec1'] .= '<div class="refListHeadFrame"><div class="refListHead">'.$menPar['name'].'</div></div>';
                $menPar['body']['sec1'] .= '<img src="'.$GLOBALS['root'].$menPar['image'].'">';

                $menPar['body']['sec1'] .= '</a>';
                $menPar['body']['sec1'] .= '</div>';
                $menPar['body']['sec1'] .= '</div>';
                
                if ( $menPar['i'] == ($menPar['nrItems']-1) ) $menPar['body']['sec1'] .= '</div>';
            
			} else if ( $menPar['style'] == "nopic" || $menPar['style'] == "" )
			{
				if ( $menPar['i'] == 0 )
					$menPar['body']['sec1'] .= '<div class="lvlMenBase">';
				
				$menPar['body']['sec1'] .= '<a class="lvlMen" href="'.rewriteLink($menPar['linkPath'], $dPar->xt, $dPar->keys).'">';
				$menPar['body']['sec1'] .= '<div class="kls_nopic">'.$menPar['name'].'</div>';
				$menPar['body']['sec1'] .= '</a>';
				
				if ( $menPar['i'] == ($menPar['nrItems']-1) ) $menPar['body']['sec1'] .= '</div>';
				
			} else if ( $menPar['style'] == "smallpic" )
			{
				if ( $menPar['i'] == 0 )
					$menPar['body'] .= '<div class="smShiftTable"><div class="smShiftCell"><div class="smallpic">';

				$menPar['body']['sec1'] .= '<div class="smHover"><a class="smallpicLink" href="'.rewriteLink($menPar['linkPath'], $dPar->xt, $dPar->keys).'">';
				$menPar['body']['sec1'] .= '<div class="imgLF2 kls_smallpic">';
				$menPar['body']['sec1'] .= '<img src="'.$GLOBALS['root'].$menPar['image'].'">';
				$menPar['body']['sec1'] .= '</div>';
				$menPar['body']['sec1'] .= '<div class="smallpic_text">'.$menPar['name'].'</div>';
				$menPar['body']['sec1'] .= '</a></div>';
				
				if ( $menPar['i'] == ($menPar['nrItems']-1) ) $menPar['body']['sec1'] .= '</div></div></div>';
				
			} else if ( $menPar['style'] == "bigpic" ) 
			{
				if ( $menPar['i'] == 0 )
					$menPar['body'] .= '<div class="bigpic">';

				$menPar['body']['sec1'] .= '<a class="smallpicLink" href="'.rewriteLink($menPar['linkPath'], $dPar->xt, $dPar->keys).'"><div class="imgLiquidFill bg_img_brd">';
				$menPar['body']['sec1'] .= '<img src="'.$GLOBALS['root'].$menPar['image'].'">';
				$menPar['body']['sec1'] .= '</div>';				
				$menPar['body']['sec1'] .= '<div class="bg_subtext">'.$menPar['name'].'</div></a>';
				
				if ( $menPar['i'] == ($menPar['nrItems']-1) ) $menPar['body']['sec1'] .= '</div>';
			}
		}
	}

?>