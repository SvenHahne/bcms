<?php
		
	class KDateList extends KeyDef
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
			$this->xmlName = "datelist";
			$this->htmlName['de'] = "Termin";
			$this->htmlName['es'] = "Fecha";
			$this->htmlName['en'] = "Date";
			$this->type = "date";
			$this->postId = "datelist";
			$this->size = 56;
			$this->maxsize = 200;
            $this->isMultType = TRUE;
            $this->noTextField = TRUE;
            
            $this->subArgs = array("date", "start", "ende", "link", "location", "ort");
            $this->subArgsKeyDef = array("", "", "", "", "", "");
            $this->subArgsVals = array("00:00:00", "00:00:00", "00:00:00", "", "", "");
            $this->subArgsShow = array(true, true, true, true, true, true);
        }
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}

		function addToEditor(&$gPar, &$cont) 
		{
            //$gPar->showVal = FALSE;
			$df = new KEMultiEntry($this, $cont, $gPar);
		}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);
		}
		
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
            $i = 0;
            
            foreach ($xmlItem->children() as $key => $sub)
            {
                $body .= "<div class='date' style='background-color:".($i%2 == 0 ? "#CCCCCC" : "#EEEEEE")."'><div style='display:table-row;'>";
                $body .= "<div class='date_date'>".$sub['date']."</div>";
                
                $body .= "<div class='date_name'>"."".$sub." - ";
                
                $body .= $sub['ort'];
                
                if ( $sub['ort'] != "" && $sub['location'] != "" ) $body .= ", ";
                $body .= $sub['location'];

                if ( ($sub['location'] != "" && $sub['link'] != "" ) || ($sub['ort'] != "" && $sub['link'] != "") )
                    $body .= ", ";
                
                $search = ["www.", "http://", "https://"];
                $replace = ["", "", ""];
                $cleanUrlStr = str_replace ( $search, $replace, $sub['link']);
                $cleanUrlStrExp = explode('/', $cleanUrlStr);
                if(sizeof($cleanUrlStrExp) > 1 ) {
                    $cleanUrlStr = $cleanUrlStrExp[0];
                }
                
                $body .= "&nbsp;<a class='date_href' href='".$sub['link']."' target='_new'>".$cleanUrlStr."</a>";

                $body .= "</div>";
                $body .= "</div></div>";

                $i++;
            }
		}
	}
?>