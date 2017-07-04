<?php
	
	class KeyDef 
	{
        public $args = array();
        public $argsKeyDef = array();
        public $argsVals = array();
        public $argsShow = array();
        public $argsStdVal = array();
        public $element = array(); // encapsuled elements
        public $height;
        public $hideInEditor = FALSE;
		public $htmlName = array();
        public $id = "";
        public $initVal;
        public $initAtNewEntry = FALSE;
        public $isAttr = FALSE;
        public $isHidden = FALSE;
        public $isLang = FALSE;
        public $isMultMasonItems = FALSE;
        public $isMultType = FALSE;
        public $isUplType = FALSE;
		public $postId;
		public $maxsize = 0;
		public $multIsCDATA = FALSE;
        public $noTextField = FALSE;
        public $showShiftArrows = TRUE;
        public $singleSel = FALSE;
        public $size = 0;
        public $subKeys = array();
        public $subArgs = array();
        public $subArgsKeyDef = array();
        public $subArgsVals = array();
        public $subArgsStdVal = array();
        public $subArgsShow = array();
        public $type;
        public $usesThumbs = FALSE;
        public $useGrid = TRUE;
        public $uplArgOnly = FALSE;
        public $uploadPath = "";
        public $uploadType = "";
        public $xmlName;

        
        public function __construct()
        {
            $a = func_get_args();
            $i = func_num_args();
            if (method_exists($this,$f='__construct'.$i)) {
                call_user_func_array(array($this,$f),$a);
            }
        }
                
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}

		function addToEditor(&$gPar, &$cont) 
		{}

		function addHead(&$head, &$dPar) 
		{}

		function drawMen(&$menPar, &$dPar)
		{}

		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{}

        function add(&$body, &$xmlItem, &$dPar)
        {}
	}	
?>