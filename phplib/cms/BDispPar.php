<?php
	class BDispPar
	{
        public $actLevel;
        public $args;
        public $clientFolder;
        public $clientUsers;
        public $calledFromStart;
        public $changedToContent;
        public $curUrl;
        public $isFirstInRow = false;
        public $imgPrfx = "";
        public $doResizeImgs = false;
        public $keys;
        public $lang;
		public $linkPath;
        public $linkWithUsrPath;
        public $link;
        public $linkAttr;
        public $loginHandler;
        public $mysqlH;
        public $newMason = true;
        public $nrLevels;
        public $refList;
        public $style;
        public $subLinkPath;
        public $subNavList;
        public $xmlStr;
        public $xt;
        
		function __construct()
		{
			$this->args = array();
		}
	}
?>