<?php
    
    class KProjectInfo extends KeyDef
    {
        public $labels = array();
        public $argsDraw = array();
        
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
            $this->xmlName = "projectinfo";
            $this->htmlName['de'] = "ProjektInfo";
            $this->htmlName['es'] = "InfoProyecto";
            $this->htmlName['en'] = "ProjectInfo";
            $this->type = "contacto";
            $this->postId = "projectinfo";
            $this->isLang = TRUE;
            $this->isMultType = TRUE;
            $this->showShiftArrows = FALSE;
            $this->size = 58;
            $this->maxsize = 200;

            $this->labels['contPers']['de'] = "Ansprechpartner";
            $this->labels['contPers']['es'] = "Personas de contacto";
            $this->labels['contPers']['en'] = "Contact persons";

            $this->labels['proj']['de'] = "Projekt";
            $this->labels['proj']['es'] = "Proyecto";
            $this->labels['proj']['en'] = "Project";

            $this->labels['date']['de'] = "Beginn";
            $this->labels['date']['es'] = "Inicio";
            $this->labels['date']['en'] = "Begin";

            $this->labels['deadline']['de'] = "Deadline";
            $this->labels['deadline']['es'] = "Deadline";
            $this->labels['deadline']['en'] = "Deadline";

            $this->labels['location']['de'] = "Ort";
            $this->labels['location']['es'] = "Ubicación";
            $this->labels['location']['en'] = "Location";

            $this->args = array("date", "deadline", "location", "gridStyle", "visible");
            $this->argsKeyDef = array("", "", "", "KEDropDown", "KEButton");
            $this->argsVals = array("", "", "", array("col1_row2", "col1_row1", "col2_row1", "col3_row1", "col4_row1", "col10_row1"), "1");
            foreach ($this->argsVals as $val)
                if (is_array($val)) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            $this->argsShow = array(true, true, true, true, true);
            $this->argsDraw = array(true, true, true, false, false);

            $this->subArgs = array("name");
            $this->subArgsKeyDef = array("");
            $this->subArgsVals = array("");
            $this->subArgsStdVal = array("");
        }
        
        
        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {}
        
        function addToEditor(&$gPar, &$cont)
        {
            $gPar->drawTake = false;
            $gPar->inpArgClass = "inp2";
            $df = new KEText($this, $cont, $gPar);

            $basePName = $gPar->post_name;

            $cont['body'] .=  "<b>".$this->labels['contPers'][$gPar->lang]."</b>&nbsp;";
            $cont['body'] .=  "<input class='but plus' type='submit' name='newMultKeyEntry_".$basePName."' value=''><br>";

            // draw contact persons
            // get all users of the actual client folder
            $opts = array_column($gPar->clientUsers, 'name');
            $gPar->showShiftArrows = true;
            
            for ($i=0;$i<sizeof($gPar->medialist);$i++)
            {
                for ($j=0;$j<sizeof($this->subArgs);$j++)
                {
                    $gPar->post_name = $basePName.'_'.$i.'_@'.$this->subArgs[$j];
                    $gPar->val = $gPar->medialist[$i][$this->subArgs[$j]];
                    $df = new KEDropDown($this, $cont, $gPar, $opts);
                }
                
                $cont['body'] .=  "<input class='but down'  type='submit' name=\"shiftDown_".$basePName."_".$i."\" value=''>";
                $cont['body'] .=  "<input class='but up'  type='submit' name=\"shiftUp_".$basePName."_".$i."\" value=''>";
                $cont['body'] .=  "<input class='but trash'  type='submit' name=\"remove_".$basePName."_".$i."\" value=''>";
                $cont['body'] .=  "<br>";
            }
        }
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            $lang = $dPar->lang;
            $type = $this->type;
            
            if ($xmlItem['visible'] == 1)
            {
                $body .= "<h3 class='projectInf'>".$this->labels['proj'][$lang].": ".$xmlItem->$lang."</h3><br>";

                $ind = 0;
                $body .= "<div class='ktable_cont_flex'>";
                
                foreach($xmlItem->attributes() as $key => $val)
                {
                    if ($this->argsDraw[$ind])
                    {
                        $body .= "<div class='ktable_row'>";
                        $body .= "<div class='ktable_cell_left nText3'><b>".$this->labels[$key][$lang].":&nbsp;&nbsp;</b></div>";
                        $body .= "<div class='ktable_cell_left nText3'>".$val."</div>";
                        $body .= "</div>";
                    }
                    $ind++;
                }
                
                if (sizeof($xmlItem->$type) > 0)
                {
                    $body .= "<div class='ktable_row'>";
                    $body .= "<div class='ktable_cell_left nText3'><b>".$this->labels['contPers'][$lang].":&nbsp;&nbsp;</b> </div>";
                    $body .= "<div class='ktable_cell_left nText3'>";
                }

                // zeichne den zur referenz gehörigen text und die
                // zugehörige Headline aus den Info Attributen
                foreach( $xmlItem->$type as $key => $value )
                {
                    $usrId = array_search($value['name'], array_column($dPar->clientUsers, 'name'));
                    $inf = $dPar->clientUsers[$usrId];
                    if ($value['name'] != "") $body .= $value['name']; else $body .= $inf['name'];
                    $body .= ", <a href='mailto:".$inf['email']."'>".$inf['email']."</a>, ".$inf['phone'].", ".$inf['task']."<br>";
                }
                
                if (sizeof($xmlItem->$type) > 0)
                    $body .= "</div></div>";

                $body .= "</div><br>";
            }
        }
    }
    ?>