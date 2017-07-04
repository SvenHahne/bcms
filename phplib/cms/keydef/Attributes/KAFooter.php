<?php
		
	class KAFooter extends KeyDef
	{
		public function __construct($stdVal=0)
		{
			$this->xmlName = "@footer";
			$this->htmlName['de'] = "To Footer";
			$this->htmlName['es'] = "To Footer";
			$this->htmlName['en'] = "To Footer";
			$this->type = "button";
			$this->postId = "@footer";
			$this->size = 1;
			$this->maxsize = 1;
			$this->initAtNewEntry = TRUE;
            $this->initVal = $stdVal;
			$this->isAttr = TRUE;
			$this->showShiftArrows = FALSE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
	
		function addToEditor(&$gPar, &$cont)
		{
			$gPar->canRemove = false;
			$df = new KEButton($this, $cont, $gPar);
		}
	}

?>