<?php
		
	class KDate extends KeyDef
	{
		public function __construct()
		{
			$this->xmlName = "date";
			$this->htmlName['de'] = "Termin";
			$this->htmlName['es'] = "Fecha";
			$this->htmlName['en'] = "Date";
			$this->type = "text";
			$this->postId = "date";
			$this->size = 76;
			$this->maxsize = 200;
			$this->args = array("date", "start", "ende", "link", "location", "ort");
			$this->argsStdVal = array("00.00.00", "00:00", "00:00", "", "", "");
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
			$body .= "<div class='date'>";
			$body .= "<div class='date_date'>".$xmlItem->getAttribute('date')."</div>";
			$body .= "<div class='date_name'>".$xmlItem->nodeValue;
			$body .= "<div class='date_loc'>";
			if ( $xmlItem->getAttribute('ort') != "" ) $body .= $xmlItem->getAttribute('ort');
			if ( $xmlItem->getAttribute('location') ) {
				if ( $xmlItem->getAttribute('ort') != "" ) $body .= ", ";
				$body .= $xmlItem->getAttribute('location');
			}
			$body .= "</div></div>";
			$body .= "</div>";

			
//			for ( $i=0;$i<$xmlItem->childNodes->length;$i++ )
//				if ( $xmlItem->childNodes->item($i)->tagName == $lang )
//					$body .= '<div class="nText">'.$xmlItem->childNodes->item($i)->nodeValue.'</div>';
		}
	}
?>