<?php
		
	class KSlideshowSS extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "slideshowss";
			$this->htmlName['de'] = "SlideshowSS";
			$this->htmlName['es'] = "SlideshowSS";
			$this->htmlName['en'] = "SlideshowSS";
			$this->type = "image";
			$this->postId = "slideshowss";
			$this->size = 76;
			$this->maxsize = 200;
			$this->uploadPath = "pic/slideshow";
			$this->uploadType = "image/*";
			$this->args = array();
			$this->argsStdVal = array();
			$this->isUplType = TRUE;
			$this->isMultType = TRUE;
			
			$this->showSlideCounter = false;
			$this->showPlayButton = false;
			$this->showControls = true;
			$this->showSlideCaptions = true;
			$this->showThumbTray = false;
			$this->showNavigation = true;
			$this->fixed = false;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
			$df = new KEMultiPic($this, $cont, $gPar);
		}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

			$head['base'] .= '<link rel="stylesheet" href="'.$GLOBALS['root'].'style/supersized.css" type="text/css" media="screen" />
			<link rel="stylesheet" href="'.$GLOBALS['root'].'style/supersized/theme/supersized.shutter.css" type="text/css" media="screen" />
			<script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.easing.min.js"></script>
			<script type="text/javascript" src="'.$GLOBALS['root'].'js/supersized.3.2.7.js"></script>
			<script type="text/javascript" src="'.$GLOBALS['root'].'style/supersized/theme/supersized.shutter.min.js"></script>
			';
			
			if (!$this->fixed)
			{
				$head['base'] .= '<style>#supersized{position:relative;} #supersized li {position:absolute;} .headSpacer{display:none}</style>
				';
			}
		}
		
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
			$files = array();
            
            $i = 0;
            foreach ($xmlItem->children() as $key => $sub)
            {
                $files[$i]['url'] = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$sub;
                $i++;
            }

			$body .= '<script type="text/javascript">
			jQuery(function($){
			$.supersized({addToBody:0,fit_portrait:1,fit_landscape:0,slide_interval:6000,transition:1,transition_speed:2000,slide_links:"blank",slides:[
			';
			
			for ($i=0;$i<sizeof($files);$i++)
			{
				$body .= '{image: "'.$GLOBALS['root'].$files[$i]['url'].'", title:"", thumb:"", url:"" }';
				if ( $i != sizeof($files) -1 ) $body .= ",";
			}
			
			$body .= ']
			});
			$( window ).scroll(function() {
				if ( $(window).scrollTop() >= 10 )
				{
					$("#controls-wrapper").hide();
				} else {
					$("#controls-wrapper").show();
				}
			});
			});
			</script>
			';
			
			// add the div, which will be replaced with the slideshow
			$body .= '<div id="supersized-loader"></div><ul id="supersized"></ul>
			';
			// Control Bar
			$body .= '<div id="controls-wrapper" class="load-item">';
			
			if ( $this->showControls )
			{
				$body .= '<div id="controls">';
			
				if ( $this->showPlayButton )
					$body .= '<a id="play-button"><img id="pauseplay" src="img/pause.png"/></a>';
				
				if ( $this->showSlideCounter )
				{
					// Slide counter
					$body .= '
					<div id="slidecounter">
					<span class="slidenumber"></span> / <span class="totalslides"></span>
					</div>
					';
				}
				
				// Slide captions displayed here
				if ( $this->showSlideCaptions )
					$body .= '<div id="slidecaption"></div>';
				
				//Thumb Tray button
				if ( $this->showThumbTray )
					$body .= '<a id="tray-button"><img id="tray-arrow" src="img/button-tray-up.png"/></a>';
				
				// Navigation
				if ( $this->showNavigation )
					$body .= '<ul id="slide-list"></ul>
					';
				
				$body .= '</div>';
			}
			
			$body .= '</div>
			';
		}
	}
?>