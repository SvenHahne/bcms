<?php
	
	class KEButton
	{
		function __construct(&$actKey, &$cont, &$gPar)
		{
			$cont['body'] .= "<input type='hidden' value='0' name='change_".$gPar->post_name."'>";

			$cont['body'] .= "<input style='margin: 5px 0 4px 0' name='change_".$gPar->post_name."' type='checkbox' ";

			if ( $gPar->val == 1 ) 
			{	
				$cont['body'] .= "checked onclick='document.forms[1].action+= (\"&scroll=\"+document.body.scrollTop);this.form.submit();'";
			} else {
				$cont['body'] .= "onclick='document.forms[1].action+= (\"&scroll=\"+document.body.scrollTop);this.value=1;this.form.submit();'";
			}
			
			$cont['body'] .= " value='".$gPar->val."'>";
			
			if ( $gPar->drawTake )
				$cont['body'] .=  "<input type='submit' class='hook but' name='subm' value=''>";
						
			if ( $gPar->canRemove ) 
				$cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$gPar->post_name."\" value='entfernen'>";
		}
	}	
?>