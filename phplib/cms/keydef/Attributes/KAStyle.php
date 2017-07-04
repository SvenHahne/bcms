<?php
		
	class KAStyle extends KeyDef
	{
		public $styles;

        function __construct0()
        {
            $this->styles = array();
            $this->init();
        }
        
        function __construct1($a1)
        {
            $this->styles = array();
            if ( is_array($a1) && sizeof($a1) > 0 )
            {
                $this->initVal = $a1[0];
                for($i=0;$i<sizeof($a1);$i++)
                    array_push( $this->styles, $a1[$i]);
            }
            $this->init();
        }
        
		function __construct2($a1,$a2)
		{
			$this->styles = array();
			for($i=0;$i<sizeof($a1);$i++)
				array_push( $this->styles, $a1[$i]);
            $this->initVal = $a2;
            $this->init();
		}
        
        
		public function init()
		{
			$this->xmlName = "@style";
			$this->htmlName['de'] = "Stil";
			$this->htmlName['es'] = "Estilo";
			$this->htmlName['en'] = "Style";
			$this->type = "style";
			$this->postId = "@style";
			$this->initAtNewEntry = TRUE;
			$this->isAttr = TRUE;
			$this->showShiftArrows = FALSE;
			$this->size = 20;
			$this->maxsize = 30;

		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
			$df = new KEDropDown($this, $cont, $gPar, $this->styles);		
		}
		
        function addHead(&$head, &$dPar)
		{}
		
        function draw(&$head, &$body, &$xmlItem, &$dPar)
		{}
	}

?>