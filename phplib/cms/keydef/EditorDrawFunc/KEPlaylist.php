<?php

	class KEPlaylist
	{
		function __construct(&$actKey, &$cont, &$gPar)
		{
			if ( $actKey->uploadPath != "" ) 
			{
				$cont['body'] .=  "<input value='".$actKey->htmlName." hochladen' class='fileselect' type='file' name='name_".$gPar->post_name."[]' size='20' ";
				if ( $actKey->uploadType != "" ) 
					$cont['body'] .=  "accept='".$actKey->uploadType."'";
				$cont['body'] .=  " multiple=''>";
				$cont['body'] .=  "<input type='submit' name=\"subm[]\" value='hochladen' multiple=''>";
			} 
			
			for ($l=0;$l<sizeof($actKey->args);$l++)
			{
				if ( $l == 0 ) $cont['body'] .=  "<br>"; // platzhalter";
				$cont['body'] .=  $actKey->args[$l].":";
				$cont['body'] .=  "<input name='name_".$gPar->post_name."_".$actKey->args[$l]."' type='text' size='8' maxlength='40' value='".$gPar->val[ $actKey->args[$l] ]."'>";
			}
			$cont['body'] .=  "<br><br>";
			
			$urlPrefix = "";
			$pSplit = explode("_", $gPar->post_name);
			for ($i=0;$i<($gPar->nrLevels-1);$i++) $urlPrefix .= $pSplit[$i]."/";
			
			$argHeight = 24;
			$medialistHeight = ( sizeof( $actKey->subArgs ) +2 ) * $argHeight;
			
			$cont['body'] .=  "<div style='width:470px;height:".( sizeof ( $gPar->medialist ) * $medialistHeight )."px;'>";
			
			for ($l=0;$l<sizeof( $gPar->medialist );$l++) 
			{					
				$cont['body'] .=  "<div style='height:".($medialistHeight+2)."px;width:468px;'>";
				
				// url eingabe
				$cont['body'] .=  "<div style='float:left;width:460px;'>";
				$cont['body'] .=  "<div style='float:left;width:40px;'>url:</div>";
				$cont['body'] .=  "<input name='name_".$gPar->post_name."_".$l."'";
				$cont['body'] .=  "type='text' size='70' value='".$gPar->medialist[$l]."'>";
				$cont['body'] .=  "</div>";
				
				for ($m=0;$m<sizeof( $actKey->subArgs );$m++) 
				{
					$cont['body'] .=  "<div style='float:left;width:460px;height:20px;'>";
					
					$cont['body'] .=  "<div style='float:left;width:40px;'>".$actKey->subArgs[$m].":</div>";
					
					$cont['body'] .=  "<div style=''>";
					$cont['body'] .=  "<input name='name_".$gPar->post_name."_".$l."' type='text' ";
					$cont['body'] .=  "size='70' maxlength='60' value='".$gPar->medialist[$l][ $actKey->subArgs[$m] ]."'>";					
					$cont['body'] .=  "</div>";
					
					$cont['body'] .=  "</div>";
				}
				
				$cont['body'] .=  "<div style='float:left;'>";
				$cont['body'] .=  "<input type='submit' name=\"remove_".$gPar->post_name."_".$l."\" value='entfernen'><br>";
				$cont['body'] .=  "</div>";
				
				$cont['body'] .=  "</div>";
			}
			
			$cont['body'] .=  "</div>";	
			$cont['body'] .=  "<input style='display:inline-block;' type='submit' name=\"addsub_".$gPar->post_name."\" value='manuell anfügen'>";
			$cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$gPar->post_name."\" value='".$actKey->postId." entfernen'>";
		}		
	}

?>