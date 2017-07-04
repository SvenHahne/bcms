<?php
    include_once ('ContentModule/BFormObj.php' );
    include_once ('ContentModule/BEditorPostProc.php' );

    class BEditorModule
    {
        protected $moduleName = "";
        protected $formName = "";
        protected $lang = "";
        protected $langPreset = "";
        protected $xt;
        protected $mysqlH;
      
        function __construct()
        {}
            
        function addHeadEditor(&$cont)
		{}
        
		function printForm(&$cont, &$keys, &$sel, $scroll)
        {}
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {}        
    }
?>