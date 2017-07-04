<?php
	
	class KESingleUpload
	{
		function __construct(&$actKey, &$cont, &$gPar)
		{
            $pName = $gPar->post_name;
            $val = $gPar->val;

			$labelSpace = 60;
            
            $isImg = ($actKey->xmlName == "image") || ($actKey->xmlName == "@image");
			
            if ( !$actKey->uplArgOnly )
            {
                $cont['body'] .=  "<input value='".$actKey->htmlName[$gPar->lang]." hochladen' class='fileselect' type='file' maxlength='40000000' name='upload_".$pName."[]' size='1'  ";

                if ( $actKey->uploadType != "" )
                    $cont['body'] .=  "accept='".$actKey->uploadType."'";
                
                $cont['body'] .=  " multiple=''><br>";
            }

			if ( $gPar->val != "" || $gPar->val['image'] != "" ) $cont['body'] .=  "<div style='width:500px;display:inline-block'>";
			
			// if it´s not an image just display the url
			if ( !$isImg )
			{
				$cont['body'] .=  "<div style='width:".($labelSpace+1)."px;margin-top:5px;display:inline-block'>Url: </div>";
                $cont['body'] .=  "<input name='change_".$pName."' type='text' size='".$actKey->size."' maxlength='".$actKey->maxsize."' value='".$gPar->val."'>";
			} 


			// display the corresponding image, when uploaded
			if ( $gPar->hasThumb || $isImg )
			{
				$urlPrefix = "";
				$pSplit = explode("_", $gPar->post_name);
				for ($i=0;$i<($gPar->nrLevels);$i++)
                    $urlPrefix .= $pSplit[$i]."/";

                // if user levels are used, remove the refering entry
                if (isset($GLOBALS["xmlClientLvl"]) && $GLOBALS["xmlClientLvl"] == 0)
                {
                    $urlPrefix = substr($urlPrefix, strpos($urlPrefix, "/")+1, strlen($urlPrefix));
                    while (strpos($urlPrefix, "//") !== false)
                        $urlPrefix = str_replace("//", "/", $urlPrefix);
                }

                
				$divHeight = sizeof($actKey->args);
				if ( $actKey->xmlName != "image" ) $divHeight += 1.4;
				if ( $actKey->xmlName == "@image" ) $divHeight = 4;

				if ( $gPar->val['image'] != "" )
				{
					$cont['body'] .=  "<div style='border:solid 1px black;margin:2px;margin-right:5px;float:left;overflow:hidden;";
					$cont['body'] .=  "height:".($divHeight * 21)."px;width:120px;'>";
//                    $cont['body'] .=  "<img style='max-width:120px;' src='".$gPar->val['image']."'>";
					$cont['body'] .=  "<img style='max-width:120px;' src='".$gPar->clientFolder.$actKey->uploadPath."/".$urlPrefix."/".$gPar->val['image']."'>";
					$cont['body'] .=  "</div>";
				} else if ( $isImg && $gPar->val != "" && $actKey->xmlName != "@image" )
				{
					$cont['body'] .=  "<div style='border:solid 1px black;margin:2px;margin-right:5px;float:left;overflow:hidden;";
					$cont['body'] .=  "height:".($divHeight * 21)."px;width:120px;'>";
					$cont['body'] .=  "<img style='max-width:120px;' src='".$gPar->clientFolder.$actKey->uploadPath."/".$urlPrefix."/".$gPar->val."'>";
					$cont['body'] .=  "</div>";
				}				
                
                // wenn der link nicht zu einem bild zeigt, hier die option ein voransichtsbild hochzuladen
                // $image sollte erstes argument in $actKey->args sein
				if ( !$isImg )
				{
					// voransichts bild hochladen, erstes argument muss "image" sein!!!
					$cont['body'] .=  "<div style='margin-top:2px;";
                    
					if ( $gPar->val['image'] != "" ) {
						$cont['body'] .=  "display:inline-block;width:333px;";
					} else { 
						$cont['body'] .=  "display:block;width:464px;";
					}
					$cont['body'] .= "height:44px;background:#AAA;border-radius: 5px;border:solid 1px grey'>";

                    
					// image, muss erstes argument sein
					$cont['body'] .= "<div style='width:".$labelSpace."px;float:left;margin-top:5px;'>&nbsp;".$actKey->args[0].":</div>";
                    
					$cont['body'] .= "<div style='height:22px;float:left;'>";
					$cont['body'] .= "<input value='".$actKey->htmlName[$gPar->lang]." hochladen' class='fileselect' type='file' name='upload_".$pName."_@".$actKey->args[0]."[]' size='1' accept='image/*' multiple=''>";
					
					$cont['body'] .= "</div>";
					
					$cont['body'] .= "</div>";
				}				
			}

			// draw the arguments
			if ( $gPar->hasThumb ) $argStart = 1; else $argStart = 0; 
            
            for ($k=0;$k<(sizeof($actKey->args) / 3);$k++)
			{
				for ($l=0;$l<min(sizeof($actKey->args) - $k*3, 3);$l++)
				{
					$ind = $k*3 + $l;

                    // wenn es sich nicht um einen verweis auf ein Bild handelt, blende hier
                    // das "image" als argument aus, dass hierfür ein eigenes Feld gebaut wird
                    if ( $gPar->hasThumb && $actKey->args[$ind] != "image")
                    {
                        $cont['body'] .=  "<div style='display:block;'>";
                        $cont['body'] .=  "<div style='width:60px;display:inline-block;'>".$actKey->args[$ind].":</div>";
                        
                        if ( sizeof($actKey->argsKeyDef) != 0 && $actKey->argsKeyDef[$ind] != "" )
                        {
                            // schmutzig, name wird als referenz übergeben und muss später wieder korrigiert werden...
                            $gPar->post_name = $pName."_@".$actKey->args[$ind];
                        	$gPar->drawTake = false;
                            $gPar->val = $val[ $actKey->args[$ind] ];
                            $aKey = new $actKey->argsKeyDef[$ind]($actKey, $cont, $gPar, $actKey->argsVals[$ind]);

                        } else {
                            $cont['body'] .=  "<input class='inp2' name='change_".$pName."_@".$actKey->args[$ind]."' type='text' ";
                        	$cont['body'] .=  "size='30' maxlength='400' value='".$val[ $actKey->args[$ind] ]."'>";
                        }
                        
                        $cont['body'] .= "</div>";
                    }

                    
//                    if (sizeof($actKey->argsKeyDef) > 0 && $actKey->argsKeyDef[$ind]."" != "KEButton" )
//                        $cont['body'] .=  "<br>"; // platzhalter";
				}
			}
            
            $gPar->val = $val;

            if ( $gPar->val != "" || $gPar->val['image'] != "" ) $cont['body'] .=  "</div>";

			if ($gPar->drawTake)
				$cont['body'] .=  "<input type='submit' name='subm' value='&uuml;bernehmen'>";
			
			if ($gPar->canRemove)
				$cont['body'] .=  "<input class='but trash' style='clear:both;' type='submit' name=\"remove_".$pName."\" value=''>";

            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->post_name = $pName;
		}
	}
?>
