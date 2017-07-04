<?php
	
	class KETextArea
	{
		function __construct(&$actKey, &$cont, &$gPar)
		{
            $pName = $gPar->post_name;
            $val = $gPar->val;
            
			if ( $actKey->postId == "guestbook" )
			{
				for ($l=0;$l<sizeof($actKey->args);$l++)
				{
					$cont['body'] .=  "<div style='width:40px;display:inline-block;'>".$actKey->args[$l].":</div>";
					$cont['body'] .=  "<input name='name_".$gPar->post_name."_@".$actKey->args[$l]."' type='text' size='68' maxlength='40' value='".$actKey->argsStdVal[$l]."'><br>";
				}
			}
	
			$cont['body'] .=  "<textarea name='change_".$pName."' rows='3' cols='100'>".$gPar->val."</textarea>";
			if ( $gPar->drawTake )
				$cont['body'] .=  "<input type='submit' class='hook but' name='subm' value=''>";
            
            
            for ($k=0;$k<(sizeof($actKey->args) / 3);$k++)
			{
				for ($l=0;$l<min(sizeof($actKey->args) - $k*3, 3);$l++)
				{
					$ind = $k*3 + $l;
                    
                    if ( $l == 0 && $k > 0 ) $cont['body'] .=  "<br>"; // platzhalter";
                    $cont['body'] .=  "<div style='width:60px;display:inline-block;'>".$actKey->args[$ind].":</div>";
					
                    if( sizeof($actKey->argsKeyDef) != 0 && $actKey->argsKeyDef[$ind] != "" )
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
            $gPar->val = $val;
            
            // schmutzig, name wird als referenz übergeben und muss hier korrigiert werden...
            $gPar->post_name = $pName;
		}
	}
	
?>