<?php
    
    class KLoginwindow extends KeyDef
    {
        protected $bLogin;
        
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
            $this->xmlName = "loginwindow";
            $this->htmlName['de'] = "Login";
            $this->htmlName['es'] = "Login";
            $this->htmlName['en'] = "Login";
            $this->type = "text";
            $this->postId = "loginwindow";
            $this->size = 89;
            $this->maxsize = 90;
        }
        
        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {}
        
        function addToEditor(&$gPar, &$cont)
        {
            $gPar->showVal = false;
            $gPar->drawTake = false;
            $df = new KEText($this, $cont, $gPar);
        }
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $dPar->loginHandler->draw($body, $dPar, false);
        }
    }
?>