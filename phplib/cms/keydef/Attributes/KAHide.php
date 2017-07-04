<?php
		
	class KAHide extends KeyDef
	{
		public function __construct($stdVal=0)
		{
			$this->xmlName = "@hide";
			$this->htmlName['de'] = "Unsichtbar";
			$this->htmlName['es'] = "Invisible";
			$this->htmlName['en'] = "Hide";
			$this->type = "button";
			$this->postId = "@hide";
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