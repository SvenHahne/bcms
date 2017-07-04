<?php
		
	class KAGenText extends KeyDef
	{
        function __construct2($a1,$a2)
        {
			$this->xmlName = "@".$a1;
			$this->type = $a1;
			$this->postId = "@".$a1;
			$this->htmlName['de'] = $a2[0];
			$this->htmlName['es'] = $a2[1];
			$this->htmlName['en'] = $a2[2];
            $this->init();
        }
        
		function __construct3($a1,$a2,$a3)
		{
			$this->xmlName = "@".$a1;
			$this->type = $a1;
			$this->postId = "@".$a1;
			$this->htmlName['de'] = $a2[0];
			$this->htmlName['es'] = $a2[1];
			$this->htmlName['en'] = $a2[2];
            $this->initVal = $a3;
            $this->init();
		}
        
        function init()
		{
			$this->size = 40;
			$this->maxsize = 200;
			$this->initAtNewEntry = TRUE;
			$this->isAttr = TRUE;
			$this->showShiftArrows = FALSE;
		}
        
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont)
		{
			$gPar->lang = FALSE;
			$gPar->canRemove = false;
			$df = new KEText($this, $cont, $gPar);
		}
	}

?>