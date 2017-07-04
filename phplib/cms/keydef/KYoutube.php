<?php
		
	class KYoutube extends KeyDef
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
                if ( is_array($val) )
                    array_push($this->argsStdVal, $val[0]);
                else
                    array_push($this->argsStdVal, $val);
            }
            $this->init();
        }

		function init()
		{
            $this->xmlName = "youtube";
            $this->htmlName['de'] = "Youtube";
            $this->htmlName['es'] = "Youtube";
            $this->htmlName['en'] = "Youtube";
            $this->type = "youtube";
            $this->postId = "youtube";
            $this->size = 12;
            $this->maxsize = 12;
            $this->isLang = TRUE;
            
            $this->uploadPath = "pic";
            $this->uplArgOnly = TRUE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
           // $df = new KEText($this, $cont, $gPar);
            
            $gPar->hasThumb = true;
            $gPar->drawTake = false;
            $df = new KESingleUpload($this, $cont, $gPar);

		}
        
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

            if ( !$GLOBALS["youTubePopUp"] )
            {
                $head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.youtubepopup.min.js"></script>
                <link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/jquery-ui.css" />
                <script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery-ui.min.js"></script>';
                $GLOBALS["youTubePopUp"] = true;
            }
            $head['jqDocReady'] .= '$("a.youtube").YouTubePopup({ autoplay:0, useYouTubeTitle:false, showBorder: false });';
        }

		function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $lang = $dPar->lang;

            $body .= "<div class='ytpFrame'>";
			$body .= "<a class='youtube' href='#' rel='".$xmlItem->$lang."'>";
		
            // voransichts bild vom attribut
            $body .= "<div class='ytpImgFit' style='background-image:url(\"".$GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$xmlItem['image']."\");'>";
           // $body .= "<img class='youtube' src='".$GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$xmlItem['image']."' />";
            $body .= "</div>";
			$body .= "</a>";
			$body .= "<div class='playBut'></div>";
            $body .= "</div>";
            
            
            // mittlere spalte
            // title
            $body .= "<div class='ytpTitelFit'>";
            $body .= "<h3 class='ytpTitle'>".mb_strtoupper($xmlItem['title'])."</h3>";
            $body .= "<div class='ytpText'>".$xmlItem['text']."</div>";
            $body .= "</div>";
        }
	}
?>