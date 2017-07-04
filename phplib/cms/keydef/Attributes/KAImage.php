<?php
		
	class KAImage extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "@image";
			$this->htmlName['de'] = "Bild";
			$this->htmlName['es'] = "Imagen";
			$this->htmlName['en'] = "Image";
			$this->type = "image";
			$this->postId = "@image";
			$this->uploadPath = "pic";
			$this->uploadType = "image/*";
			$this->initAtNewEntry = TRUE;
			$this->isAttr = TRUE;
			$this->showShiftArrows = FALSE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
			if ( !$GLOBALS["filedrag"] )
			{
				$cont['footer'] .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>
				';
				$GLOBALS["filedrag"] = true;
			}
		}
		
		function addToEditor(&$gPar, &$cont)
		{	
			$gPar->canRemove = false;
			$df = new KESingleUpload($this, $cont, $gPar);
		}
	}

?>