<?php
    
    class KClient extends KeyDef
    {
        public $argsDef = array();
        public $argsMysqlDef = array();
        
        function __construct0() { $this->init(); }
        
        function __construct3($a1,$a2,$a3)
        {
            $this->args = $a1;
            $this->argsKeyDef = $a2;
            $this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) )
                    array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
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
            $this->xmlName = "client";
            $this->htmlName['de'] = "Klient";
            $this->htmlName['es'] = "Cliente";
            $this->htmlName['en'] = "Client";
            $this->type = "user";
            $this->postId = "client";
            $this->isMultType = TRUE;
            $this->showShiftArrows = FALSE;

            $this->args = array("name", "passw", "folder", "rights");
            $this->argsKeyDef = array("", "", "", "KEDropDown");
            $this->argsVals = array("", "", "", array("usr", "all"));
            $this->argsStdVal = array("", "", "", "usr");
            // date("Y-m-d G:i:s") TIMESTAMP
            $this->argsMysqlDef = array('VARCHAR(20)', 'VARCHAR(10)', 'VARCHAR(20)', 'VARCHAR(3)');
            $this->argsShow = array(true, true, false, true);
            
            $this->subArgs = array("name", "email", "phone", "task", "client", "userId");
            $this->subArgsKeyDef = array("", "", "", "", "", "");
            $this->subArgsVals = array("", "", "", "", "", "");
            $this->subArgsMysqlDef = array('VARCHAR(20)', 'VARCHAR(20)', 'VARCHAR(20)', 'VARCHAR(128)', 'VARCHAR(20)', 'VARCHAR(4)');
            $this->subArgsShow = array(true, true, true, true, false, false);
        }
        
        
        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {}
        
        function addToEditor(&$gPar, &$cont)
        {
            $df = new KEMultiText($this, $cont, $gPar);
        }
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {}
    }
?>