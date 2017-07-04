<?php
		
	class KHead extends KeyDef
	{
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
			$this->xmlName = "head";
			$this->htmlName['de'] = "Ueberschrift";
			$this->htmlName['es'] = "Título";
			$this->htmlName['en'] = "Headline";
			$this->type = "text";
			$this->postId = "head";
			$this->size = 89;
			$this->maxsize = 90;
			$this->isLang = TRUE;
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
            if (isset($xmlItem['style']) && $xmlItem['style'] == "fromAttr")
            {
                $par = $xmlItem->xpath("parent::*");
                $par = $par[0];
                $lang = $dPar->lang;
                
                $nodeName = mb_strtoupper($par->name->$lang);
                
               // $body .= '<div class="info">';
                $body .= '<div class="fixInfoTextTop">'.$nodeName.'<br>'.mb_strtoupper($this->formatSubAttr($par));
                $body .= '<hr id="lowerLimit">';
                $body .= '</div>';
            }

            foreach ($xmlItem->children() as $sub)
            {
                if ( $sub->getName() == $dPar->lang )
                    $body .= '<h3 class="'.$dPar->args['style'].'">'.$sub.'</h3>';
            }
        }
        
        function formatSubAttr($getArg)
        {
            $text = "";
            if (sizeof($getArg) > 1)
            {
                if ( $getArg['place'] != "" ) $text .= $getArg['place'];
                //            if ( $getArg['city'] != "" || $getArg['size'] != "" || $getArg['year'] != "") $text .= ', ';
                //            if ( $getArg['city'] != "" ) $text .= $getArg['city'];
                //            if ( $getArg['size'] != "" || $getArg['year'] != "") $text .= ', ';
                //            if ( $getArg['size'] != "" ) $text .= $getArg['size'].'m&sup2;';
                //            if ( $getArg['year'] != "" ) $text .= ', '.$getArg['year'];
            }
            return $text;
        }
	}

?>