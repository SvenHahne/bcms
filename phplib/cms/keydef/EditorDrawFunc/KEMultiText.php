<?php

	class KEMultiText
	{
		function __construct(&$actKey, &$cont, &$gPar)
        {
            $pName = $gPar->post_name;
            $val = $gPar->val;
            
            if ( $gPar->showVal )
            {
                $cont['body'] .=  "<input class='".$gPar->inpClass."' name='change_".$pName."' size='".$actKey->size."' maxlength='".$actKey->maxsize."' value='".$val."' ";
                
                if ( !$actKey->noTextField )
                {
                    $cont['body'] .= "type='text'>";
                } else {
                    $cont['body'] .= "type='hidden'>";
                }
            }
            
            if ( $gPar->drawTake && !$actKey->noTextField)
                $cont['body'] .=  "<input type='submit' class='hook but shiftUp2' name='subm' value=''><br>";
            
            for ($k=0;$k<(sizeof($actKey->args) / 3);$k++)
            {
                for ($l=0;$l<min(sizeof($actKey->args) - $k*3, 3);$l++)
                {
                    $ind = ($k * 3 + $l);
                    $offs = 0;
                    
                    // skip argument option, don´t show it!!
                    if ( !(isset($actKey->argsShow) && !$actKey->argsShow[$ind]) )
                    {
                        //if ( $l == 0 && $k > 0 ) $cont['body'] .=  "<br>"; // platzhalter";
                        $cont['body'] .=  "<div style='width:60px;display:inline-block;'>".$actKey->args[$ind].":</div>";
                        
                        if ( sizeof($actKey->argsKeyDef) != 0 && $actKey->argsKeyDef[$ind] != "" )
                        {
                            // schmutzig, name wird als referenz übergeben und muss später wieder korrigiert werden...
                            $gPar->post_name = $pName."_".$actKey->args[$ind];
                            $gPar->drawTake = false;
                            $gPar->val = $val[ $actKey->args[$ind] ];
                            $aKey = new $actKey->argsKeyDef[$ind]($actKey, $cont, $gPar, $actKey->argsVals[$ind]);
                        } else
                        {
                            $cont['body'] .=  "<input class='".$gPar->inpArgClass."' name='change_".$pName."_".$actKey->args[$ind]."' type='text' ";
                            $cont['body'] .=  "size='".$gPar->argSize."' maxlength='200' value='".$val[ $actKey->args[$ind] ]."'>";
                        }
                        
                        if ($ind != sizeof($actKey->args) -1 - $offs) $cont['body'] .=  "<br>"; // platzhalter";
                    } else {
                        $offs++;
                    }
                }
            }
            
            $gPar->val = $val;
            

            // print subnodes
            $cont['body'] .= "<div style='width:100%;'>";
            
            $cont['body'] .= "<div style='margin:10px 0px 5px 10px;'><b>".$actKey->type."s</b>";
            $cont['body'] .= "<input style='margin-left:8px;margin-bottom:0px;' class='but plus shiftUp2' type='submit' name='newMultKeyEntry_$pName' value=''>";
            $cont['body'] .= "</div>";
            
            for ($l=0;$l<sizeof($gPar->medialist);$l++)
            {
                $id = "".(floor($l/10)).($l%10);
                
                $cont['body'] .=  "<div style='display:block;margin-left:10px;margin-bottom:5px;background-color:rgba(0, 0, 0, 0.1);padding: 2px 5px 5px 5px;border-radius:5px;'>";

                // bau die subargumente
                if (sizeof($actKey->subArgs) != 0)
                {
                    $cont['body'] .= "<div style='display:inline-block;'>";
                    
                    for ($i=0;$i<sizeof($actKey->subArgs);$i++)
                    {
                        if ($actKey->subArgsShow[$i])
                        {
                            $cont['body'] .=  "<div style='margin-right:5px;display:inline-block;'>";
                            $cont['body'] .=  "<div style='display:inline-block;width:55px;'>".$actKey->subArgs[$i].": </div>";
                            
                            $gPar->drawTake = false;
                            $gPar->post_name = $pName.'_'.$l.'_'.$actKey->subArgs[$i];
                            
                            if ( $actKey->subArgsKeyDef[$i] == "" )
                            {
                                $cont['body'] .=  "<input class='inp3' name='change_".$pName."_".$l."_".$actKey->subArgs[$i]."' type='text' size='8' maxlength='200' value='".$gPar->medialist[$l][$actKey->subArgs[$i]]."'>";
                                
                            } else
                            {
                                $argMen[$i] = new $actKey->subArgsKeyDef[$i]($actKey, $cont, $gPar, $actKey->subArgsVals[$i]);
                            }
                            
                            $cont['body'] .=  "</div>";
                        } else {
                            // add as hidden argument
                            $cont['body'] .=  "<input name='change_".$pName."_".$l."_".$actKey->subArgs[$i]."' type='hidden' value='".$gPar->medialist[$l][$actKey->subArgs[$i]]."'>";
                        }
                    }
                    
                    $cont['body'] .= "</div>";
                }
                
                if ($actKey->showShiftArrows)
                {
                    $cont['body'] .=  "<input class='but down'  type='submit' name=\"shiftDown_".$pName."_".$l."\" value=''>";
                    $cont['body'] .=  "<input class='but up'  type='submit' name=\"shiftUp_".$pName."_".$l."\" value=''>";
                }
                
                $cont['body'] .=  "<input class='but trash'  type='submit' name=\"remove_".$pName."_".$l."\" value=''>";
                
                $cont['body'] .=  "</div>";
            }

            $cont['body'] .=  "</div>";

            
            if ( $gPar->canRemove )
                $cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$pName."\" value='entfernen'>";
            
            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->post_name = $pName;
        }
	}
?>