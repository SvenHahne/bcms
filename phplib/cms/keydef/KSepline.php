<?php
		
	class KSepline extends KeyDef
	{
        function __construct0() { $this->init(); }

        function __construct3($a1,$a2,$a3)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
			$this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
            $this->init();
		}
        
        function __construct4($a1,$a2,$a3,$a4)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
            $this->argsVals = $a3;
			$this->argsStdVal = $a4;
            foreach ($this->argsVals as $val)
                array_push($this->argsShow, true);
			$this->init();
		}
		function init()
		{
			$this->xmlName = "sepline";
			$this->htmlName['de'] = "Linie";
			$this->htmlName['es'] = "Línea";
			$this->htmlName['en'] = "Line";
			$this->type = "sepline";
			$this->postId = "sepline";
			$this->size = 40;
			$this->maxsize = 200;
            $this->noTextField = true;
		}

        
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
            $df = new KEText($this, $cont, $gPar);
		}
        
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);
		}
		
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
			$body .= '<hr class="'.$dPar->args['style'].'">';
		}
	}

?>