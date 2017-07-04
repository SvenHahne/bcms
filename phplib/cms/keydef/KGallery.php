<?php
		
	class KGallery extends KeyDef
	{        
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
			$this->xmlName = "gallery";
			$this->htmlName['de'] = "Foto-Galerie";
			$this->htmlName['es'] = "Álbum de foto";
			$this->htmlName['en'] = "Foto-Gallery";
			$this->type = "image";
			$this->postId = "gallery";
			$this->uploadPath = $GLOBALS['root']."pic/gallery";
			$this->uploadType = "image/*";
			$this->isUplType = TRUE;
			$this->isMultType = TRUE;
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
			$df = new KEMultiPic($this, $cont, $gPar);
		}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

			if ( $GLOBALS["gallery"] == false )
			{				
				$head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.fancybox.js?v=2.1.5"></script>
				<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/jquery.fancybox.css?v=2.1.5" media="screen" />
				<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>
				';
				$GLOBALS["gallery"] = true;
			}
           
            //------------------------------------------------------------------------------------------------
            $head['jqGlobVar'] .= 'function setGalleryHeight() {
                $(".gal-full").each(function(index){
                                    var imgProp = $(this).children();
                                    imgProp = imgProp[0].title;
                                    imgProp = imgProp.split("_");
                                    $(this).css({"height":($(this).width() / imgProp[0] * imgProp[1])+"px"});
                                    });
            }';
        
            //------------------------------------------------------------------------------------------------
            $head['jqDocReady'] .= '$(".fancybox").fancybox();
                $(".imgLiquidFill").imgLiquid({ fill: true, horizontalAlign:"center", verticalAlign:"30%" });
            ';
        
            //------------------------------------------------------------------------------------------------
            $head['jqWinResize'] .= '
               // setGalleryHeight();
            ';
        
            //------------------------------------------------------------------------------------------------
            $head['jqWinLoad'] .= '
                //setGalleryHeight();
            ';
		}
		
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
            switch ($dPar->args['style'])
            {
                case "full_row" :
                    $this->fullRow($body, $xmlItem, $dPar);
                    break;
                default :
                    $this->drawStandard($body, $xmlItem, $dPar);
                    break;
            }            
		}
        
        
//------------------------------------ drawing methods ------------------------------------


        function fullRow(&$body, &$xmlItem, &$dPar)
		{
			$ind = 0;

            $body .= "<div id='gallery' class='gallery-cont'>";

            foreach( $xmlItem as $key => $value )
            {
                $body .= "<div class='gal-full'>";
                //$body .= "<div class='gal-full imgLiquidFill'>";
                $body .= "<img class='pic-gal-full' src='".$GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath."/".$value."' title='".$value['rw']."_".$value['rh']."'>";
                $body .= "</div>";
                
                $ind++;
            }

            $body .= "</div>";
        }

        
        function drawStandard(&$body, &$xmlItem, &$dPar)
		{
            $body .= "<div id='gallery' class='gallery-cont'>";
            
			$ind = 0;
            //			foreach( $xmlItem->childNodes as $key => $value )
            foreach( $xmlItem as $key => $value )
            {

                $body .= "<div class='gallery-div imgLiquidFill'>";
                $body .= "<a href='".$GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath."/".$value."' class='fancybox' data-fancybox-group='gallery'>";
                $body .= "<img src='".$GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath."/".$value."'>";
                $body .= "</a></div>";
                    
                $ind++;
            }
        }
	}
?>