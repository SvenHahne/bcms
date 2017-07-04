<?php
	
	// this shouldn´t be used for lowest levels
	
	class KName extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "name";
			$this->htmlName['de'] = "Name";
			$this->htmlName['es'] = "Nombre";
			$this->htmlName['en'] = "Name";
			$this->type = "text";
			$this->postId = "name";
			$this->size = 76;
			$this->height = 1;
			$this->maxsize = 100;
			$this->initAtNewEntry = TRUE;
			$this->isLang = TRUE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
			$gPar->canRemove = false;
			$df = new KEText($this, $cont, $gPar);
		}
	}

?>