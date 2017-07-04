<?php

	class KEMultiPic
	{
		function __construct(&$actKey, &$cont, &$gPar)
		{
            $pName = $gPar->post_name;
            $val = $gPar->val;
            
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
			
            // layout parameters
            $picDivHeight = 79;
            $space = 5;
            $argSingleHeight = 18;
            $argHeight = 0;
            
            if (sizeof($actKey->subArgs) != 0)
                $argHeight = sizeof($actKey->subArgs) * ($argSingleHeight + $space);

            $topOffs = min(($picDivHeight + $space*2) - $argHeight, 0);

            $argHeight = sizeof($gPar->medialist) * $argHeight;
            
            $heightPicDiv = sizeof($gPar->medialist) * $picDivHeight + (sizeof($gPar->medialist)+1) * $space;
            $height = max($argHeight, $heightPicDiv) * min(sizeof($gPar->medialist), 1);

            if (sizeof($actKey->subArgs) == 0)
                $height = ceil ( sizeof ( $gPar->medialist ) / 5 ) * 85;

            $cont['body'] .=  "<div style='width:470px;height:".$height."px;'>";
            
            // draw arguments
            for ($k=0;$k<(sizeof($actKey->args) / 3);$k++)
            {
                for ($l=0;$l<min(sizeof($actKey->args) - $k*3, 3);$l++)
                {
                    $ind = $k * 3 + $l;
                    
                    if ($actKey->args[$ind] != "image")
                    {
                        if ( $l == 0 && $k > 0 ) $cont['body'] .=  "<br>"; // platzhalter";
                        $cont['body'] .=  "<div style='width:60px;display:inline-block;'>".$actKey->args[$ind].":</div>";
                        
                        if ( sizeof($actKey->argsKeyDef) != 0 && $actKey->argsKeyDef[$ind] != ""  )
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
                    }
                }
            }
            $cont['body'] .= "<br><br>";
            
            $cont['body'] .= "<input class='but plus shiftUp2' type='submit' name='newMultKeyEntry_$pName' value=''>";
            
            if ( $actKey->uploadPath != "" )
            {
                $cont['body'] .=  "<input value='".$actKey->htmlName[$gPar->lang]." hochladen' class='fileselect' type='file' name='upload_".$pName."[]' size='20' ";
                if ( $actKey->uploadType != "" )
                    $cont['body'] .=  "accept='".$actKey->uploadType."'";
                $cont['body'] .=  " multiple=''>";
            }
            
            $cont['body'] .= "<br><br>";
            
			for ($l=0;$l<sizeof ($gPar->medialist);$l++)
			{
                $id = "".(floor($l/10)).($l%10);

				$cont['body'] .=  "<div style='display:inline-block;border:solid 1px black;width:88px;";
                $cont['body'] .=  "margin-right:4px;margin-bottom:4px;height:".$picDivHeight."px;position:relative;top:".$topOffs."px;'>";
                
                if (isset($gPar->medialist[$l]['image']))
                {
                    // if there is a separate image argument, take that
                    $cont['body'] .=  "<img style='max-width:88px;' src='".$gPar->clientFolder.$actKey->uploadPath."/".$urlPrefix."/".$gPar->medialist[$l]['image']."'>";

                } else if( $gPar->medialist[$l] != "" )
                {
                    $cont['body'] .=  "<img style='max-width:88px;' src='".$gPar->clientFolder.$actKey->uploadPath."/".$urlPrefix."/".$gPar->medialist[$l]."'>";
                } else {
                    $cont['body'] .=  "<img style='max-width:88px;' src='pic/placeholder.png'>";
                }
				
				// versteckete input feld zum übertragen des bild pfades						
				$cont['body'] .=  "<input type='hidden' ";
				$cont['body'] .=  "name='name_".$pName."_".$l."'";
				$cont['body'] .=  "type='text' value='filename_".$gPar->medialist[$l]."'>";
				
                $cont['body'] .=  "<input class='but down'  type='submit' name=\"shiftDown_".$pName."_".$l."\" value=''>";
                $cont['body'] .=  "<input class='but up'  type='submit' name=\"shiftUp_".$pName."_".$l."\" value=''>";
				$cont['body'] .=  "<input class='but trash'  type='submit' name=\"remove_".$pName."_".$l."\" value=''>";
				$cont['body'] .=  "</div>";

                // bau die subargumente
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
                        
                        $cont['body'] .=  "</div>";
                    }
                    $cont['body'] .= "</div>";
                }
			}
            
			$cont['body'] .=  "</div>";

            $gPar->isSubArg = false;
            
            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->val = $val;
            $gPar->post_name = $pName;
		}
	}
?>