<?php

	class KDivscroller extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "divscroller";
			$this->htmlName['de'] = "Bild-Scroller";
			$this->htmlName['es'] = "Imagen-Scroller";
			$this->htmlName['en'] = "Image-Scroller";
			$this->type = "image";
			$this->postId = "divscroller";
			$this->size = 76;
			$this->maxsize = 200;
			$this->uploadPath = "pic/slideshow";
			$this->uploadType = "image/*";
			$this->args = array("width", "height", "class");
			$this->argsStdVals =  array("", "", "");
			$this->subArgs = array("rw", "rh");
			$this->subArgsStdVal = array("", "");
			$this->isUplType = TRUE;
			$this->isMultType = TRUE;			
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}

		function addToEditor(&$gPar, &$cont) 
		{
			$df = new KEMultiPic($this, $cont, $gPar);
		}
	}
?>