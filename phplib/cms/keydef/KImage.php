<?php
	
	class KImage extends KeyDef
	{
		public $styles;

        function __construct0() { $this->init(); }
        
		function __construct3($a1,$a2,$a3)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
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
			$this->args = $a1;
			$this->argsKeyDef = $a2;
            $this->argsVals = $a3;
			$this->argsStdVal = $a4;
            foreach ($this->argsVals as $val)
                array_push($this->argsShow, true);
			$this->init();
		}
        
        function init()
		{
			$this->xmlName = "image";
			$this->htmlName['de'] = "Bild";
			$this->htmlName['es'] = "Imagen";
			$this->htmlName['en'] = "Image";
			$this->type = "image";
			$this->postId = "image";
			$this->uploadPath = "pic";
			$this->uploadType = "image/*";
			$this->isUplType = TRUE;
			$this->styles = array();
			$this->maxSize = 0;
		}
        
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
			if ( !$GLOBALS["filedrag"] )
			{
				$cont['footer'] .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>
				';
				$GLOBALS["filedrag"] = true;
			}
		}
		
		function addToEditor(&$gPar, &$cont) 
		{
            $gPar->hasThumb = TRUE;
			$df = new KESingleUpload($this, $cont, $gPar);
		}

        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

			if ( $GLOBALS["imgLiquid"] == false )
			{
				$head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>
				';
				$GLOBALS["imgLiquid"] = true;
			}
            
            if ( $GLOBALS["image"] == false )
            {
                $head['jqDocReady'] .= '$(".imgLiquidFill").imgLiquid({fill: true,horizontalAlign: "center",verticalAlign: "40%"});';
                $GLOBALS["image"] = true;
            }
		}
		
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
            $filename = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$xmlItem;

            if (isset($dPar->args['link']) && $dPar->args['link'] != "")
            	$body .= '<a href="'.$dPar->args['link'].'" target="_new">';
            
            if ($dPar->args['style'] == "gridImg" || $dPar->args['style'] == "gridImgS"
                || $dPar->args['style'] == "gridImgXS" || $dPar->args['style'] == "gridImgB"
                || $dPar->args['style'] == "gridImgOvr")
            {
                $body .= '<div class="mason_over">'.mb_strtoupper($xmlItem->attributes()['text']);
                $body .= '<hr class="white">';
                $body .= mb_strtoupper($xmlItem->attributes()['subtext']).'</div>';
            }
            
            if ( $dPar->args['style'] == "fixedHeight" )
            {
                $body .= '<div class="fixedHeight">';
                $body .= '<div class="fixedHeightImgCont"><span class="fixedHeightVCentHelper"></span>';
                $body .= lazyLoadImg($filename, $dPar->args['style']);
                $body .= '</div>';
                if ( $dPar->args['text'] != "" && $dPar->args['subtext'] != "" )
                	$body .= '<div class="nText"><b>'.$dPar->args['text'].'</b><br>'.$dPar->args['subtext'].'</div>';
                $body .= '</div>';
                
            } else
            {
				if ($dPar->args['style'] == "gridImg" || $dPar->args['style'] == "gridImgS" || $dPar->args['style'] == "div_cover")
				{
					$body .= '<div class="'.$dPar->args['style'].'" style="background-image: url('.$GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$xmlItem.');">';
					$body .= '</div>';
	
				} else
				{
					if (strpos($dPar->args['style'], "vCent") !== false)
						$body .= '<div style="display:table;width:100%;height:100%;"><div style="display:table-cell;vertical-align:middle;">';
				   
					$body .= '<div class="'.$dPar->args['style'];
					if ( $dPar->args['fill'] ) $body .= ' imgLiquidFill';
					$body .= '">';
			
					$body .= lazyLoadImg($filename, $dPar->args['style']);
	                if ( $dPar->args['text'] != "" && $dPar->args['subtext'] != "" )
	                	$body .= '<div class="nText"><b>'.$dPar->args['text'].'</b><br>'.$dPar->args['subtext'].'</div>';
					$body .= '</div>';
					
					if (strpos($dPar->args['style'], "vCent") !== false)
						$body .= '</div></div>';
			   }
           }
           
            if (isset($dPar->args['link']) && isset($dPar->args['link']) != "")
            	$body .= '</a>';
		}
	}
?>