<?php
    
    class KAClient extends KeyDef
    {
        public $sel;
        
        function __construct2($a1,$a2,$a3)
        {
            $this->xmlName = "@".$a1;
            $this->type = $a1;
            $this->postId = "@".$a1;
            $this->htmlName['de'] = $a2[0];
            $this->htmlName['es'] = $a2[1];
            $this->htmlName['en'] = $a2[2];
            $this->sel = $a3;
            
            $this->init();
        }
        
        function __construct4($a1,$a2,$a3,$a4)
        {
            $this->xmlName = "@".$a1;
            $this->type = $a1;
            $this->postId = "@".$a1;
            $this->htmlName['de'] = $a2[0];
            $this->htmlName['es'] = $a2[1];
            $this->htmlName['en'] = $a2[2];
            $this->sel = $a3;
            $this->initVal = $a4;
            $this->init();
        }
        
        public function init()
        {
            $this->initAtNewEntry = TRUE;
            $this->isAttr = TRUE;
            $this->showShiftArrows = FALSE;
            $this->hideInEditor = TRUE;
            $this->size = 20;
            $this->maxsize = 30;
        }
        
        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {}
        
        function addToEditor(&$gPar, &$cont) 
        {
            $df = new KEDropDown($this, $cont, $gPar, $this->sel);
        }
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {}
    }
    
    ?>