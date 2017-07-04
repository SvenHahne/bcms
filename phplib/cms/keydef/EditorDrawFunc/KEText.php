<?php
    
    class KEText
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
                            $gPar->post_name = $pName."_@".$actKey->args[$ind];
                        	$gPar->drawTake = false;
                            $gPar->val = $val[ $actKey->args[$ind] ];
                            $aKey = new $actKey->argsKeyDef[$ind]($actKey, $cont, $gPar, $actKey->argsVals[$ind]);
                        } else
                        {
                            $cont['body'] .=  "<input class='".$gPar->inpArgClass."' name='change_".$pName."_@".$actKey->args[$ind]."' type='text' ";
                        	$cont['body'] .=  "size='".$gPar->argSize."' maxlength='200' value='".$val[ $actKey->args[$ind] ]."'>";
                        }
                        
                        if ($ind != sizeof($actKey->args) -1 - $offs) $cont['body'] .=  "<br>"; // platzhalter";
                    } else {
                        $offs++;
                    }
                }
            }

            $gPar->val = $val;
            
            if ( sizeof($actKey->args) > 0 ) $cont['body'] .=  "<br>"; // platzhalter";
            
            if ( $gPar->canRemove )
                $cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$pName."\" value='entfernen'>";
            
            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->post_name = $pName;
        }
    }
    ?>