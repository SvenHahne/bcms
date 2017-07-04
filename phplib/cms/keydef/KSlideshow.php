<?php
        
	class KSlideshow extends KeyDef
	{
        public $slideshowNr = 0;

        function __construct0() { $this->init(); }
        
        function __construct3($a1,$a2,$a3)
        {
            $this->subArgs = $a1;
            $this->subArgsKeyDef = $a2;
            $this->subArgsStdVal = $a3;
            $this->init();
        }

        function __construct6($a1,$a2,$a3,$a4,$a5,$a6)
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
            $this->subArgsVals = $a6;
            foreach ($this->subArgsVals as $val)
            {
                array_push($this->subArgsShow, true);
                if ( is_array($val) ) array_push($this->subArgsStdVal, $val[0]); else  array_push($this->subArgsStdVal, $val);
            }
            
            $this->init();
        }

		function init()
		{
			$this->xmlName = "slideshow";
			$this->htmlName['de'] = "Slideshow";
			$this->htmlName['es'] = "Slideshow";
			$this->htmlName['en'] = "Slideshow";
			$this->type = "slimage";
			$this->postId = "slideshow";
			$this->size = 40;
			$this->maxsize = 200;
			$this->uploadPath = "pic/slideshow";
			$this->uploadType = "image/*";
			$this->isUplType = TRUE;
			$this->isMultType = TRUE;
			
			$this->showSlideCounter = false;
			$this->showPlayButton = false;
			$this->showControls = true;
			$this->showSlideCaptions = true;
			$this->showThumbTray = false;
			$this->showNavigation = true;
			$this->fixed = false;
            $this->usesThumbs = true;
           	$this->useThumbs = false; // steuert das ersetzen der div der slideshow
        }
        
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
            if ( !$GLOBALS["filedrag"] )
            {
                $cont['footer'] .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>
                ';
                $GLOBALS["filedrag"] = true;
            }
            
            if ( array_search("KECascadingMenu", $this->subArgsKeyDef) !== false )
                $this->cascMen = new KECascadingMenu($this, $cont, $gPar, TRUE);
        }
		
        
		function addToEditor(&$gPar, &$cont) 
		{
            $temp = $gPar->post_name;
            
            if ( array_search("KECascadingMenu", $this->subArgsKeyDef) !== false )
            	$gPar->cascMen = $this->cascMen;
            
            $df = new KEMultiEntry($this, $cont, $gPar);
        }
		
        
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

            if ( !$GLOBALS["responsveSlides"] )
            {
                $head['base'] .= '
                    <link rel="stylesheet" href="'.$GLOBALS['root'].'style/responsiveslides.css" type="text/css" media="screen" />
                    <link rel="stylesheet" href="'.$GLOBALS['root'].'style/responsiveslides_cstm.css" type="text/css" media="screen" />
                    <link rel="stylesheet" href="'.$GLOBALS['root'].'style/responsiveslides_cstm_ao.css" type="text/css" media="screen" />
                    <script type="text/javascript" src="'.$GLOBALS['root'].'js/responsiveslides.js"></script>
                    <script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.event.swipe.js"></script>
                    <script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.event.move.js"></script>
                    <script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.fancybox.js"></script>
                    		<link rel="stylesheet" href="'.$GLOBALS['root'].'style/jquery.fancybox.css" type="text/css" media="screen" />
                    <script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.fancybox.js"></script>
                ';
                
                $GLOBALS["responsveSlides"] = true;
            }
			
			if (!$this->fixed)
			{
				$head['base'] .= '';
			}
                        
            $this->slideshowNr = $GLOBALS["slideShowCntr"];
            $GLOBALS["slideShowCntr"]++;
		}
		
                
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{			
            $files = array();
            $infText = array();
            $i = 0;

            foreach ($xmlItem->children() as $key => $sub)
            {
                // when there is no file, but a reference entry, resolve the reference
                if ($sub == "" && $sub['ref'] != "")
                {
                    // get the entries in the referenced with the specific key
                    if ($xmlItem['style'] == "flotante")
                    {
                        $list = resolveRef($dPar->xt, $sub['ref'], $sub['refkey'], $sub['refmode'], "@image");
                    } else
                    {
                        $list = resolveRef($dPar->xt, $sub['ref'], $sub['refkey'], $sub['refmode'], "");
                    }
                    
                    // iterate through the results
                    foreach($list as $k => $v)
                    {
                        $files[$i]['url'] = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$v['url'].$v['value'];
                        $infText[$i] = "".$v['value']['name']."<br>".$v['value']['place'];
                        $i++;
                    }

                } else
                {
                    // get the entries the normal way, by reading the database entries directly
                    $files[$i]['url'] = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$sub;
                    $infText[$i] = "".$sub['name']."<br>".$sub['place'];
                }
                
                $i++;
            }
                        
            // check for preview-image
            if ( isset($xmlItem['image']) && $xmlItem['image'] != "" )
            {
                $this->useThumbs = true;
                $xmlItem['image'] = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$xmlItem['image'];
            }

            switch ($xmlItem['style'])
            {
                case "full_row" :
                    $this->drawAsDiv($head, $body, $xmlItem, $files, $infText, $dPar);
                    break;
                case "overl" :
                    $this->drawAsDiv($head, $body, $xmlItem, $files, $infText, $dPar);
                    break;
                case "no_nav" :
                    $this->drawAsDiv($head, $body, $xmlItem, $files, $infText, $dPar);
                    break;
                case "arrows_outside" :
                    $this->drawArrowsOutside($head, $body, $xmlItem, $files, $infText, $dPar);
                    break;
                case "flotante" :
                    $this->drawAsIframe($head, $body, $xmlItem, $files, $infText, $dPar);
                    break;
                default:
                    break;
            }
        }
        
        //------------------------------------------------------------------------------------
        
        function drawArrowsOutside(&$head, &$body, &$xmlItem, &$files, &$infText, &$dPar)
        {
        	 
            $arrSize =   array(  "5",   "5",   "5",   "6",   "8",   "8"); // in %
            $marg =      array(  "1",   "1",   "1",   "1",   "1",   "1");
            $mTop =      array("150", "140", "140", "140", "140", "125");
            $navHeight = array( "70",  "50",  "50",  "50",  "50",  "40");
            
            $head['jqDocReady'] .= '$("#slideshow'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10);
            $head['jqDocReady'] .= '").responsiveSlides({';
            if (isset($xmlItem['start']) && $xmlItem['start'] == "manual") {
                $head['jqDocReady'] .= 'addPlayButton: true, ';
            }
            $head['jqDocReady'] .= 'auto: true, pager: true, speed: 4000, timeout:8000, navContainer:"#rs_contr'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10).'", infoHorOffs:8, nav:true, namespace:"rslides_ao", hideOnOverlayClick:false }); ';
            
            $body .= '<style>
            .rslides_ao_nav { position: absolute; -webkit-tap-highlight-color: rgba(0,0,0,0); top:52%; left:0; z-index:3; text-indent:-9999px; overflow:hidden; text-decoration:none; height:61px;width:'.($arrSize[0] - $marg[0]).'%;background-image:url("style/responsiveslides/themes.gif");margin-top: -45px;margin-left:0px;margin-right:0px;}
            .slidershow_base_ao { position:absolute; top:0px; left:0px; bottom:auto; }';
            
            for($i=0;$i<sizeof($dPar->keys['resolutions'])+1;$i++)
            {
                $body .= '
                @media screen and ';
                if ($i>0) $body .= '(max-width:'.$dPar->keys['resolutions'][$i-1].'px)';
                if ($i>0 && $i < sizeof($dPar->keys['resolutions'])) $body .= ' and ';
                if ($i < sizeof($dPar->keys['resolutions'])) $body .= '(min-width:'.$dPar->keys['resolutions'][$i].'px)';
                $body .= '{';
                $body .= '.rslides_ao_nav { margin-top:25%;height:'.$navHeight[$i].'px;width:'.($arrSize[$i] - $marg[$i]).'%; }';
                $body .= '.slideshow_base_ao { width:'.(100 - $arrSize[$i] *2 - $marg[$i]*2).'%;left:'.($arrSize[$i]+$marg[$i]).'%; }';
                $body .= '}';
            }
            $body .= '</style>';

            $body .= '<div class="slidshowContainer_ao">';

            $body .= '<ul class="slideshow_base_ao" id="slideshow'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10).'">';
            for ($i=0;$i<sizeof($files);$i++)
            {
                $body .= '<li><a href="#">';
                $body .= lazyloadImg($files[$i]['url']);
                $body .= '</a></li>';
            }
            $body .= '</ul>';
            $body .= '<div id="rs_contr'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10).'"></div>';
            $body .= '</div>';
        }
        
        //------------------------------------------------------------------------------------
        
        function drawAsDiv(&$head, &$body, &$xmlItem, &$files, &$infText, &$dPar)
        {
        	 
        	$hString = '$("#slideshow'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10);
        	//if (isset($xmlItem['corte']) && $xmlItem['corte'] == "vertical") $body .= '_vert';
        	$hString .= '").responsiveSlides({';
        	
        	if (isset($xmlItem['start']) && $xmlItem['start'] == "manual") {
        		$hString .= 'addPlayButton: true, ';
        	}
        	
        	$hString .= 'auto: true, pager: true, random: false, speed: 4000, timeout:8000, hideOnOverlayClick:false, ';
        	
        	if ( isset($xmlItem['style']) && $xmlItem['style'] != "no_nav" )
        		$hString .= 'navContainer:"#rs_contr'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10).'", ';
        	
        	if (isset($xmlItem['corte']) && $xmlItem['corte'] == "vertical") {
        		$hString .= 'infoHorOffs:15';
        	} else {
        		$hString .= 'infoHorOffs:8';
        	}
        	
        	if (isset($xmlItem['style']) && $xmlItem['style'] == "overl")
        	{
        		$hString .= ', nav:false, infoField:true, infoText: Array(';
        	
       			for ($i=0;$i<sizeof($infText);$i++) {
       				$hString .= '"'.$infText[$i].'"';
       				if ( $i != sizeof($infText)-1) $hString .= ', ';
       			}
        	
       			$hString .= '), infoWidth:20 ';
       		} else if ( isset($xmlItem['style']) && $xmlItem['style'] == "no_nav" )
       		{
       			$hString .= ', nav:false';
       		} else {
       			$hString .= ', nav:true';
       		}
        	
       		$hString .= '}); ';
        	
       		$head['jqDocReady'] = $hString.$head['jqDocReady'];
        	
        	
       		// add the div, which will be replaced with the slideshow
       		if (isset($xmlItem['corte']) && $xmlItem['corte'] == "vertical")
       		{
       			$body .= '<div id="slidshowContainer_vert"><ul id="slideshow_vert">';
       		} else {
       			$body .= '<div id="slidshowContainer">';
       			$body .= '<ul class="slideshow_base" id="slideshow'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10).'">';
       		}
        	
       		for ($i=0;$i<sizeof($files);$i++)
       		{       			
       			//$body .= '<li><a href="#"><div class="rslides_imgCont" style="background-image:url(\''.$files[$i]['url'].'\');"></div></a></li>';
       			$body .= '<li><a href="#">'.lazyLoadImg($files[$i]['url'], "rslides_lazyLoad").'</a></li>';
       		}
        	
       		$body .= '</ul>';
       		$body .= '</div>';
        	
       		$body .= ' <div id="rs_contr'.(floor($this->slideshowNr / 10)).($this->slideshowNr % 10).'"></div>
			';
        		
        }
        
        //------------------------------------------------------------------------------------
        
        function drawAsIframe(&$head, &$body, &$xmlItem, &$files)
        {        	 
            $head['jqDocReady'] .= '$(".fancybox").fancybox({
                                    arrows: true,
                                    helpers:  { overlay : { closeClick : false } }
                                    });
            $(function(){
              $(".fancyb_submen").click(function(e){
                                    e.preventDefault();
                                    $.fancybox.open([';
                                                      
         	for ($i=0;$i<sizeof($files);$i++)
         	{
            	$head['jqDocReady'] .= '{ href:"'.$files[$i]['url'].'", title:""}';
            	if ($i != sizeof($files)-1) $head['jqDocReady'] .= ',';
         	}

        	$head['jqDocReady'] .= ']) }); });';
        	
            
         	$body .= '<div class="fancyb_slidshow">';
         	for ($i=0;$i<sizeof($files);$i++)
         	{            	 
            	$body .= '<a class="fancybox fancyb" ';
                if ($i > 0) $body .= 'style="display:none;"';
                $body .= 'rel="group" href="'.$files[$i]['url'].'" >';
              
                if ($this->useThumbs) {
                    $body .= lazyLoadImg($xmlItem['image'], "fancyb_slidshow_thmb");
                } else 
                {
                    $body .= lazyLoadImg($files[$i]['url'], "fancyb_slidshow_thmb");                    
                }
              
                $body .= '</a>';
			}

			$body .= '</div>';
          
          // fake - bildselektionspunkte (damit es wie mit responsive_slideshow aussieht)
          $body .= '<div class="fancyb_men_but_cont">';
          $body .= '</div>';
        }
	}
?>