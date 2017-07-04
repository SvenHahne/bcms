<?php
		
	class KAudioplaylist extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "audioplaylist";
			$this->htmlName['de'] = "Audio-Playlist";
			$this->htmlName['es'] = "Audio-Playlist";
			$this->htmlName['en'] = "Audio-Playlist";
			$this->type = "media";
			$this->postId = "audioplaylist";
			$this->uploadPath = "media";
			$this->args = array("class");
			$this->argsStdVals = array("");
			$this->subArgs = array("title", "artist");
			$this->subArgsStdVal = array("", "");
			$this->isUplType = TRUE;
			$this->isMultType = TRUE;			
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}

		function addToEditor(&$gPar, &$cont) 
		{
			$df = new KEPlaylist($this, $cont, $gPar);
		}
	}

?>