<?php
	
	class KLevel extends KeyDef
	{		
		public function __construct()
		{
			$this->xmlName = "l";
			$this->htmlName['de'] = "Level";
			$this->htmlName['es'] = "Level";
			$this->htmlName['en'] = "Level";
			$this->type = "level";
			$this->postId = "level";
			$initAtNewEntry = TRUE;
			// the KAShort() entry is essential, since with this entry all the
			// directory entries are made
			$this->subKeys = array();			
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);
		}
		
        function draw(&$head, &$body, &$xmlItem, &$dPar)
		{}
	}
	
?>