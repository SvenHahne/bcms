<?php
	
	class BEditorDrawPar
	{
        public $actLvl;
        public $argSize = 8;
        public $canRemove = true;
        public $cascMen;
        public $clientFolder;
        public $clientUsers;
        public $curUrl;
        public $drawTake = true;
        public $hasThumb = false;
        public $inpClass = "inp";
        public $inpArgClass = "";
        public $formName = "";

        public $isSubArg = false;
		public $keys;
        public $lang;
        public $medialist;
		public $noText = "df2";
        public $post_name;
        public $stylesPath;
        public $showVal = true;
        public $val;
        public $xmlStr;
        public $xt;
		
		function __construct(&$_keys)
        {
			$this->keys = $_keys;
		}
	}
?>