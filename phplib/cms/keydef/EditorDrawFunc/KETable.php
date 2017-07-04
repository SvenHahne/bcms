<?php
    class KETable
    {
        function addJs(&$cont, &$gPar)
        {
            if ( $GLOBALS["tableEditWin"] == false )
            {
                $cont['js'] .='
                function openEditWin(id)
                {
                    var modalWin = document.getElementById("openModal"+id);
                    modalWin.style.opacity = 1;
                    modalWin.style.pointerEvents = "auto";
                }
                function closeEditWin(id)
                {
                    var modalWin = document.getElementById("openModal"+id);
                    modalWin.style.opacity = 0;
                    modalWin.style.pointerEvents = "none";
                }';
                
                $GLOBALS["tableEditWin"] = true;
            }
        }
        
        function __construct(&$actKey, &$cont, &$gPar)
        {
            $pName = $gPar->post_name;
            $val = $gPar->val;
            
            $this->addJs($cont, $gPar);
            
            // ----- draw table elements ------------------------------------------------------------
            
            $cont['body'] .= "<div style='display:table;width:90%;'>";
            
            for ($y=0;$y<intval($val["nrRows"]);$y++)
            {
                $cont['body'] .= "<div style='display:table-row;border:solid 1px black;'>";
                
                for ($x=0;$x<intval($val["nrCols"]);$x++)
                {
                    $l = $y * intval($val["nrCols"]) + $x;
                    $id = "".(floor($l/10)).($l%10);

                    $cont['body'] .= "<div style='display:table-cell;border:solid 1px black;'>";

                    
                    // ---- bau die subargumente ----------------------------------------------------
                    
                    $argMen = array();
                    
                    if (sizeof($actKey->subArgs) != 0)
                    {
                        $cont['body'] .= "<div style='display:inline-block;'>";
                        for ($i=0;$i<sizeof($actKey->subArgs);$i++)
                        {
                            $cont['body'] .=  "<div>";
                            $cont['body'] .=  "<div style='display:inline-block;width:55px;'>".$actKey->subArgs[$i].": </div>";
                            
                            $gPar->drawTake = false;
                            $gPar->post_name = $pName.'_'.$l.'_@'.$actKey->subArgs[$i];
                            $q = $gPar->xt->xpath->query($gPar->xmlStr."/".$actKey->type."[".($l+1)."]/@".$actKey->subArgs[$i] );
                            if ( $q->length > 0 ) $gPar->val = $q->item(0)->nodeValue;
                            
                            if ( $actKey->subArgsKeyDef[$i] == "" )
                            {
                                $cont['body'] .=  "<input class='inp2' name='change_".$pName."_".$l."_@".$actKey->subArgs[$i]."' type='text' size='8' maxlength='200' value='".$gPar->val."'>";
                            	
                                // hackerei, sollte allgemein gehen dass nur noch die Klasse intanziert werden muss
                            } else if ($actKey->subArgsKeyDef[$i] == "KECascadingMenu" && isset($gPar->cascMen))
                            {
                                $cont['body'] .= '<select name="change_'.$gPar->post_name.'" class="ddmen" id="ddmen_sel_'.$gPar->cascMen->id.'_'.$id.'"><option>'.$gPar->cascMen->getItemName($gPar).'</option></select>';
                                $cont['body'] .=  '<input class="inp2" name="change_'.$pName.'_'.$l.'_'.$actKey->subArgs[$i].'" type="hidden" size="8" maxlength="200" value="'.$gPar->val.'">';
                            } else
                            {
                                $argMen[$i] = new $actKey->subArgsKeyDef[$i]($actKey, $cont, $gPar, $actKey->subArgsVals[$i]);
                            }
                        
                            if ($actKey->subArgs[$i] == "type" && $gPar->val != "")
                            {
                                // create hidden edit win
                                $cont['body'] .= "<div id='openModal".$id."' class='editWin'>";
                                $cont['body'] .= "<div>";
                                $cont['body'] .= "<input type=\"button\" class='close' value=\"X\" onclick=\"closeEditWin('".$id."');\" />";
                                $cont['body'] .= "<h2>Edit Window</h2>";
                                
                                // create key
                                $gPar->post_name = $pName.'_'.$l;
                                $this->instDrawKey($actKey, $cont, $gPar, $l, $i);
                                
                                $cont['body'] .= "</div>";
                                $cont['body'] .= "</div>";
                                $cont['body'] .= "<input type=\"button\" onclick=\"openEditWin('".$id."');\" value=\"Edit\">";
                            }

                            $cont['body'] .=  "</div>";
                        }
                        $cont['body'] .= "</div>";
                    }
                    $cont['body'] .= "</div>";
                }
                $cont['body'] .= "</div>";
            }            
            $cont['body'] .= "</div>";
            
            
            // ------- draw arguments ----------------------------------------------------------------------
            
            for ($k=0;$k<(sizeof($actKey->args) / 3);$k++)
            {
                for ($l=0;$l<min(sizeof($actKey->args) - $k*3, 3);$l++)
                {
                    $ind = $k *3 + $l;

                    $cont['body'] .= "<div style='width:60px;display:inline-block;'>".$actKey->args[$ind].":</div>";

                    if ( sizeof($actKey->argsKeyDef) != 0 && $actKey->argsKeyDef[$ind] != "" )
                    {
                        // schmutzig, name wird als referenz übergeben und muss später wieder korrigiert werden...
                        $gPar->post_name = $pName."_@".$actKey->args[$ind];
                    	$gPar->drawTake = false;
                        $gPar->val = $val[ $actKey->args[$ind] ];
                        $aKey = new $actKey->argsKeyDef[$ind]($actKey, $cont, $gPar, $actKey->argsVals[$ind]);
                        
                    } else
                    {
                        $cont['body'] .=  "<input name='change_".$pName."_@".$actKey->args[$ind]."' type='text' ";
                    	$cont['body'] .=  "size='8' maxlength='200' value='".$val[ $actKey->args[$ind] ]."'>";
                    }
                    
                    if ($ind != sizeof($actKey->args)-1) $cont['body'] .=  "<br>"; // platzhalter";
                }
            }
            
            $gPar->val = $val;
            
            if ( sizeof($actKey->args) > 0 ) $cont['body'] .=  "<br>"; // platzhalter";
            
            if ( $gPar->canRemove )
                $cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$pName."\" value='entfernen'>";
            
            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->post_name = $pName;
        }
        
        
        function instDrawKey(&$actKey, &$cont, &$gPar, $ind, $subInd)
        {
            // get subnodes
            $q = $gPar->xt->xml->xpath($gPar->xmlStr."/".$actKey->type."[".($ind+1)."]");            
            if (sizeof($q) > 0) $q = $q[0];
            
            if (sizeof($q->children()) > 0 )
            {
                $typeName = $q['type']."";
                                
                // search object in subArgVals
                foreach($actKey->element as $elemObj)
                    if (get_class($elemObj) == $typeName)
                        $obj = $elemObj;
                
                // here the values have to be read
                $gPar->val = $q->children()[0];
                
                // check for attributes
                if ( sizeof($q->children()[0]->attributes()) > 0 )
                    foreach($q->children()[0]->attributes() as $k => $v)
                        $gPar->val[$k] = $v;
                
                if (isset($obj))
                {
                    // check if lang, if yes, read lang specific
                    if ($obj->isLang)
                    {
                        $lang = $gPar->lang;
                        $gPar->val[0] = $gPar->val->$lang;
                    }
                    
                    $gPar->post_name .= "__".$obj->postId."_".$subInd;
                    $obj->addHeadEditor($gPar, $gPar->stylesPath, $cont);
                    $obj->addToEditor($gPar, $cont);
                }
            }
        }
    }
?>