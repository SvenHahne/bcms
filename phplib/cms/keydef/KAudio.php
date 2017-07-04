<?php
		
	class KAudio extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "audio";
			$this->htmlName['de'] = "Audio";
			$this->htmlName['es'] = "Audio";
			$this->htmlName['en'] = "Audio";
			$this->type = "audio";
			$this->postId = "audio";
			$this->size = 76;
			$this->maxsize = 0;
			$this->uploadPath = "audio";
			$this->args = array("text");
			$this->argsStdVals = array("");
			$this->isUplType = TRUE;			
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
			$df = new KESingleUpload($this, $cont, $gPar);
		}
	}
?>