<?php
		
	class KHeadFreePos extends KeyDef
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
			$this->xmlName = "headfreepos";
			$this->htmlName['de'] = "Ueberschrift";
			$this->htmlName['es'] = "Título";
			$this->htmlName['en'] = "Headline";
			$this->type = "text";
			$this->postId = "headfreepos";
			$this->size = 89;
			$this->maxsize = 90;
			$this->isLang = TRUE;
            $this->isHidden = TRUE;
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
		{}
        
		function add(&$body, &$xmlItem, &$dPar)
		{
            if (isset($xmlItem['style']))
            {
                switch((string)$xmlItem['style'])
                {
                    case "navi":
                        // make a headline with the name of the Entry
                        $body .= '<script>var infoText = $(".changeLang").append("<div class=\'infoAtSubMenu\'>';
                        
                        foreach ($xmlItem->children() as $sub)
                            if ( $sub->getName() == $dPar->lang )
                                $body .= mb_strtoupper($sub);
                        
                        $body .= '</div>");</script>';
                        break;
                    default:
                        foreach ($xmlItem->children() as $sub)
                            if ( $sub->getName() == $dPar->lang )
                                $body .= '<h3 class="'.$dPar->args['style'].'">'.$sub.'</h3>';
                        break;
                }
            }
        }
	}

?>