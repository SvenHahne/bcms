<?php
    
    class KAGenButton extends KeyDef
    {
        public $sel;
        
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
        
        function __construct4($a1,$a2,$a3,$a4)
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
        
        public function init()
        {
            $this->initVal = "1";
            $this->initAtNewEntry = TRUE;
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
        
        function addHead(&$head, &$dPar)
        {}
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {}
    }
?>