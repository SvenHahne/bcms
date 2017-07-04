<?php
		
	class KAShort extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "@short";
			$this->htmlName['de'] = "Kürzel";
			$this->htmlName['es'] = "Abreviatura";
			$this->htmlName['en'] = "Shortname";
			$this->type = "text";
			$this->postId = "@short";
			$this->size = 4;
			$this->maxsize = 4;
			$this->initAtNewEntry = TRUE;
			$this->isAttr = TRUE;
			$this->hideInEditor = TRUE;
			$this->showShiftArrows = FALSE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
			if ( !$GLOBALS["footerScript"] )
			{
				$cont['footer'] .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>
				';
				$GLOBALS["footerScript"] = true;
			}
		}
	
		function addToEditor(&$gPar, &$cont)
		{
			$gPar->lang = FALSE;
			$gPar->canRemove = false;
			$df = new KEText($this, $cont, $gPar);
		}
	}

?>