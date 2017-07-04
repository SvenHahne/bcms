<?php
	class KEDropDown
	{
		function __construct(&$actKey, &$cont, &$gPar, $opts)
		{			
			$cont['body'] .=  '<select name="change_'.$gPar->post_name.'" onchange="this.form.action+=(\'&scroll=\'+document.documentElement.scrollTop); submit();" class="no_marg">';
								
			for ($i=0;$i<sizeof($opts);$i++)
			{
				$cont['body'] .=  "<option";
				if ($gPar->val == $opts[$i] ) $cont['body'] .= " selected";
				$cont['body'] .=  ">".$opts[$i]."</option>";
			}
			$cont['body'] .=  "</select>";

			if ( $gPar->drawTake )
				$cont['body'] .=  "<input type='submit' class='hook but' name='subm' value=''>";

			if ( $gPar->canRemove ) 
				$cont['body'] .=  "<input style='clear:both;' type='submit' name=\"remove_".$gPar->post_name."\" value='entfernen'>";
		}
	}
?>