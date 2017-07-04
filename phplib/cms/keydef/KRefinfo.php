<?php
		
	class KRefinfo extends KeyDef
	{
		protected $cascMen;
		protected $argMen = array();
        protected $dispText;
        public $setCrush = true;
        protected $maxNrCols = 3;
        protected $mason;
        
        function __construct0() { $this->init(); }

		function __construct3($a1,$a2,$a3)
		{
			$this->subArgs = $a1;
			$this->subArgsKeyDef = $a2;
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
			$this->subArgs = $a1;
			$this->subArgsKeyDef = $a2;
			$this->argsVals = $a3;
            $this->argsStdVal = $a4;
            foreach ($this->argsVals as $val)
                array_push($this->argsShow, true);
            $this->init();
		}

        function __construct6($a1,$a2,$a3, $a4,$a5,$a6)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
			$this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
			$this->subArgs = $a4;
			$this->subArgsKeyDef = $a5;
			$this->subArgsStdVal = $a6;
            $this->init();
		}
        
        function init()
		{
			$this->xmlName = "refinfo";
			$this->htmlName['de'] = "Info aus Verweis";
			$this->htmlName['es'] = "Info de Referencia";
			$this->htmlName['en'] = "Info from Reference";
			$this->type = "ref";
			$this->postId = "refinfo";
			$this->isMultType = TRUE;
			$this->multIsCDATA = TRUE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
			$this->cascMen = new KECascadingMenu($this, $cont, $gPar, TRUE);
		}

		function addToEditor(&$gPar, &$cont) 
		{
			$basePName = $gPar->post_name;
            $val = $gPar->val;
            
			$cont['body'] .=  "<input class='but plus' type='submit' name='newMultKeyEntry_".$basePName."' value=''>";
			$cont['body'] .=  "<div style='width:470px;height:".((sizeof($gPar->medialist) + sizeof($this->subArgs)) * 22)."px;'>";

			$this->cascMen->addToEditor();
            
			for ($l=0;$l<sizeof( $gPar->medialist );$l++) 
			{
				$id = "".(floor($l/10)).($l%10);
				$cont['body'] .= '<div>';
				
				// create fake dropdown menu, which will be replaced onclick			
				$itemName = "";
				if ( $gPar->medialist[$l] != "" )
				{
					$q = $gPar->xt->xpath->query( $gPar->medialist[$l]."/name/".$gPar->lang );
					if ( $q->length > 0 ) $itemName = $q->item(0)->nodeValue;
				}
                
                // name wird von KECascadingMenu benötigt um die auswahl korrekt zum formular zu übergeben
                $gPar->post_name = $basePName.'_'.$l;
				$cont['body'] .= '<select name="change_'.$gPar->post_name.'" class="ddmen" id="ddmen_sel_'.$this->cascMen->id.'_'.$id.'"><option>'.$itemName.'</option></select>';
				
				$argMen[$l] = array();
				for ($i=0;$i<sizeof($this->subArgs);$i++) 
				{
					if ($this->subArgsKeyDef[$i] == "KEDropDown")
					{
						$gPar->drawTake = false;
						$gPar->post_name = $basePName.'_'.$l.'_@'.$this->subArgs[$i];
						$q = $gPar->xt->xpath->query($gPar->xmlStr."/".$this->type."[".($l+1)."]/@".$this->subArgs[$i] );
						if ( $q->length > 0 ) $gPar->val = $q->item(0)->nodeValue;
						$argMen[$l][$i] = new $this->subArgsKeyDef[$i]($this, $cont, $gPar, $this->subArgsStdVal[$i]);
					}
				}
				
				// hidden input feld to submit the value
				$cont['body'] .= '<input type="hidden" name="change_'.$basePName.'_'.$l.'"';
				$cont['body'] .= 'value="'.$gPar->medialist[$l].'">';
				$cont['body'] .= '<input class="up but shiftUp2" type="submit" name="shiftUp_'.$basePName.'_'.$l.'" value="">';
				$cont['body'] .= '<input class="down but shiftUp2" type="submit" name="shiftDown_'.$basePName.'_'.$l.'" value="">';
				$cont['body'] .= '<input class="but trash shiftUp2"  type="submit" name="remove_'.$basePName.'_'.$l.'" value="">';
				$cont['body'] .= '</div>';
			}
            
            $gPar->val = $val;
            $gPar->post_name = $basePName;

            // draw arguments
            for ($k=0;$k<(sizeof($this->args) / 3);$k++)
			{
				for ($l=0;$l<min(sizeof($this->args), 3);$l++)
				{
					$ind = $k *3 + $l;
                    
                    if ( $l == 0 && $k > 0 ) $cont['body'] .=  "<br>"; // platzhalter";
                    $cont['body'] .=  "<div style='width:60px;display:inline-block;'>".$this->args[$ind].":</div>";
					
                    if( sizeof($this->argsKeyDef) != 0 && $this->argsKeyDef[$ind] != "" )
                    {
                        // schmutzig, name wird als referenz übergeben und muss später wieder korrigiert werden...
                        $gPar->post_name = $basePName."_@".$this->args[$ind];
						$gPar->drawTake = false;
                        $gPar->val = $val[ $this->args[$ind] ];
                        $aKey = new $this->argsKeyDef[$ind]($this, $cont, $gPar, $this->argsVals[$ind]);
                        
                    } else
                    {
                        $cont['body'] .=  "<input name='change_".$basePName."_@".$this->args[$ind]."' type='text' ";
						$cont['body'] .=  "size='8' maxlength='200' value='".$val[ $this->args[$ind] ]."'>";
                    }
				}
			}
            
			$gPar->post_name = $basePName;
            $gPar->val = $val;

			$cont['body'] .=  "</div>";
		}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);

            /*
            if ( $GLOBALS["imgLiquid"] == false )
			{
				$head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>';                                
				$GLOBALS["imgLiquid"] = true;
			}
            $head['jqDocReady'] .= '$(".imgLiquidFill").imgLiquid({fill: true, horizontalAlign: "center", verticalAlign: "50%"});';
             */

        }
		
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
            // zeichne den zur referenz gehörigen text und die
            // zugehörige Headline aus den Info Attributen
			foreach( $xmlItem->children() as $key => $value )
			{
                if ( $value != "" )
				{
                    // first look for slideshows
                    $list = resolveRef($dPar->xt, $value, "KText", $value['refmode'], "", $dPar->lang);

                    if (sizeof($list) > 0)
                    {
                        $par = $list[0]['xmlElm'][0]->xpath("parent::*");
                        $par = $par[0];
                        $lang = $dPar->lang;
                        
                        $nodeName = mb_strtoupper($par->name->$lang);
                        $body .= '<div class="fixInfoTextTop">'.$nodeName.'<br>'.mb_strtoupper($this->formatSubAttr($par));
                        $body .= '<hr id="lowerLimit">';
                        $body .= '</div>';
                         
                        $body .= '<div class="'.$dPar->args['style'].'">'.$list[0]['value'].'</div>';
                    }
                }
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