<?php
		
	class KPlaylist extends KeyDef
	{
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
			$this->xmlName = "playlist";
			$this->htmlName['de'] = "Playlist";
			$this->htmlName['es'] = "Playlist";
			$this->htmlName['en'] = "Playlist";
			$this->type = "media";
			$this->postId = "playlist";
			$this->uploadPath = "media";
            //$this->uploadType = "video/*";
            $this->usesThumbs = TRUE;

            $this->subArgs = array("title", "artist");
			$this->subArgsStdVal = array("", "");
			
            $this->isUplType = TRUE;
			$this->isMultType = TRUE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
			$df = new KEMultiEntry($this, $cont, $gPar);
		}
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $urlPrefix = $dPar->clientFolder."media/".$dPar->linkPath;
            $fileList = [];
            
            foreach($xmlItem[0] as $k => $e)
            {
                $e[0] = $urlPrefix.$e[0];                
                array_push($fileList, $e);
            }
            
            $jplayer = new JPlayer('playlist',
                                   'style/skin/blue.monday/css/jplayer.blue.monday.css',
                                   $GLOBALS["jplayerCounter"],
                                   $fileList,
                                   "jplayerBase");
            $jplayer->addJquerySrc($head, $body);
            $jplayer->addHtml($body);
            
            $GLOBALS["jplayerCounter"]++;
        }
	}

?>