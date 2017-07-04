<?php
		
	class KAStart extends KeyDef
	{
		public function __construct($stdVal=0)
		{
			$this->xmlName = "@isstart";
			$this->htmlName['de'] = "Start";
			$this->htmlName['es'] = "Inicio";
			$this->htmlName['en'] = "Start";
			$this->type = "button";
			$this->postId = "@isstart";
			$this->size = 1;
			$this->maxsize = 1;
			$this->initAtNewEntry = TRUE;
            $this->initVal = $stdVal;
			$this->isAttr = TRUE;
			$this->showShiftArrows = FALSE;
            $this->singleSel = TRUE;
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