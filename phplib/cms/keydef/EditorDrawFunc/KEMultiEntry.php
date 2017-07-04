<?php

	class KEMultiEntry
	{
		function __construct(&$actKey, &$cont, &$gPar)
		{
            $pName = $gPar->post_name;
            $val = $gPar->val;
            
            // argumente für den gesamten Eintrag
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
            
            // get the prefix for the corresponding media link
            $urlPrefix = "";
            $pSplit = explode("_", $pName);
            for ($i=0;$i<($gPar->nrLevels);$i++) $urlPrefix .= $pSplit[$i]."/";
            
            // if user levels are used, remove the refering entry
            if (isset($GLOBALS["xmlClientLvl"]) && $GLOBALS["xmlClientLvl"] == 0)
            {
                $urlPrefix = substr($urlPrefix, strpos($urlPrefix, "/")+1, strlen($urlPrefix));
                while (strpos($urlPrefix, "//") !== false)
                    $urlPrefix = str_replace("//", "/", $urlPrefix);
            }
            
            $urlPrefix .= implode('', array_fill(0, sizeof($gPar->keys['resolutions']), 'k'))."_";
            
            // print subnodes
            
            // print add button
            $cont['body'] .= "<div>";
            
            $cont['body'] .= "<div style='margin:10px 0px 5px 0px;'><b>".$actKey->type."s</b>";
            $cont['body'] .= "<input style='margin-left:8px;margin-bottom:0px;' class='but plus shiftUp2' type='submit' name='newMultKeyEntry_$pName' value=''>";
            $cont['body'] .= "</div>";
            
            
            // draw each subentry
            for ($l=0;$l<sizeof($gPar->medialist);$l++)
            {
                $id = "".(floor($l/10)).($l%10);

                // begin subentry / table
                $cont['body'] .= "<div style='display:table;margin-left:0px;margin-bottom:5px;background-color:rgba(0, 0, 0, 0.1);padding: 2px 5px 5px 5px;border-radius:5px;'>";

                // begin table row, previe pic + arguments
                $cont['body'] .= "<div style='display:table-row;'>";
                
                // Thumbnail preview pic
                if ($actKey->usesThumbs)
                {
                    $cont['body'] .= "<div style='display:table-cell;'>";

                    $picUrl = explode(".", $gPar->medialist[$l]);
                    $cont['body'] .= "<img style='max-width:88px;' src='";                    

                    $noImg = false;
                    if(sizeof($picUrl) > 1)
                    {
                        if(array_search($picUrl[1], $GLOBALS["ffmpegDstFormats"]) !== false)
                            $picUrl[1] = "jpg";
                        
                        if(file_exists($gPar->clientFolder.$actKey->uploadPath."/".$urlPrefix.$picUrl[0].".".$picUrl[1]))
                            $cont['body'] .= $gPar->clientFolder.$actKey->uploadPath."/".$urlPrefix.$picUrl[0].".".$picUrl[1];
                        else
                            $noImg = true;
                    } else
                    {
                        $noImg = true;
                    }

                    if($noImg) $cont['body'] .= "pic/placeholder.png";

                    $cont['body'] .= "'>";
                    $cont['body'] .= "</div>";
                }

                // table cell for nested table
                $cont['body'] .= "<div style='display:table-cell;padding-left:5px;vertical-align: top'>";

                // nested table for arguments begin
                $cont['body'] .= "<div style='display:table;'>";

                // main entry
                $cont['body'] .= "<div style='display:table-row;'>";
                $cont['body'] .= "<div style='display:table-cell;width:75px;'> </div>";
                $cont['body'] .= "<input class='inp_cell' name='change_".$pName."_".$l."' type='text' size='".$actKey->size."' value='".$gPar->medialist[$l]."'>";
                $cont['body'] .= "</div>";


                // bau die subargumente
                if (sizeof($actKey->subArgs) != 0)
                {
                    for ($i=0;$i<sizeof($actKey->subArgs);$i++)
                    {
                        $cont['body'] .= "<div style='display:table-row;'>";

                        if ($actKey->subArgsShow[$i])
                        {
                            $cont['body'] .=  "<div style='display:table-cell;'>".$actKey->subArgs[$i].": </div>";
                            
                            $gPar->drawTake = false;
                            $gPar->post_name = $pName.'_'.$l.'_@'.$actKey->subArgs[$i];
                            

                            if ( $actKey->subArgsKeyDef[$i] == "" )
                            {
                                $cont['body'] .=  "<input class='inp_cell' name='change_".$pName."_".$l."_@".$actKey->subArgs[$i]."' type='text' size='".$actKey->size."' maxlength='200' value='".$gPar->medialist[$l][$actKey->subArgs[$i]]."'>";
                            } else if ($actKey->subArgsKeyDef[$i] == "KECascadingMenu" && isset($gPar->cascMen))
                            {
                            	$cont['body'] .= '<select name="change_'.$gPar->post_name.'" class="ddmen2" id="ddmen_sel_'.$gPar->cascMen->id.'_'.$id.'"><option>'.$gPar->cascMen->getItemName($gPar).'</option></select>';
                            	$cont['body'] .=  '<input class="inp2" name="change_'.$pName.'_'.$l.'_@'.$actKey->subArgs[$i].'" type="hidden" size="8" maxlength="200" value="'.$gPar->val.'">';                            		 
                            } else
                            {
                                $argMen[$i] = new $actKey->subArgsKeyDef[$i]($actKey, $cont, $gPar, $actKey->subArgsVals[$i]);
                            }
                            
                        } else
                        {
                            // add as hidden argument
                            $cont['body'] .=  "<input name='change_".$pName."_".$l."_@".$actKey->subArgs[$i]."' type='hidden' value='".$gPar->medialist[$l][$actKey->subArgs[$i]]."'>";
                        }
                        
                        $cont['body'] .=  "</div>";
                    }
                }
                
                // end nested table for arguments
                $cont['body'] .= "</div>";
                
                // end table cell for nested table
                $cont['body'] .= "</div>";

                // end table row for nested table
                $cont['body'] .= "</div>";


                // shift arrows for subentry
                $cont['body'] .= "<div style='display:table-row;'>";
                $cont['body'] .= "<div style='display:table-cell;'>";

                if ($actKey->showShiftArrows)
                {
                    $cont['body'] .= "<input class='but down' type='submit' name=\"shiftDown_".$pName."_".$l."\" value=''>";
                    $cont['body'] .= "<input class='but up' type='submit' name=\"shiftUp_".$pName."_".$l."\" value=''>";
                }
                
                $cont['body'] .= "<input class='but trash' type='submit' name=\"remove_".$pName."_".$l."\" value=''>";
                $cont['body'] .= "</div>";
                
                // upload per subentry

                if ( $actKey->uploadPath != "" )
                {
                    $cont['body'] .= "<div style='display:table-cell;'>";

                    $cont['body'] .=  "<input value='".$val." hochladen' class='fileselect' type='file' name='upload_".$pName."_".$l."' size='20' ";
                    if ( $actKey->uploadType != "" )
                        $cont['body'] .=  "accept='".$actKey->uploadType."'";
                    $cont['body'] .=  " >";
                    $cont['body'] .=  "<input type='submit' name=\"subm\" value='hochladen'><br>";
                    
                    $cont['body'] .= "</div>";
                }
                
                $cont['body'] .= "</div>";


                // end subentry table
                $cont['body'] .= "</div>";
            }
            
            $cont['body'] .= "</div>";
            
            if ( $gPar->canRemove )
                $cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$pName."\" value='entfernen'>";
            
            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->post_name = $pName;
        }
	}
?>