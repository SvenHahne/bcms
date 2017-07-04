<?php
	class KECascadingMenu
	{
		protected $cont;
		protected $gPar;
		public $id;
        protected $multiEntries = false;
		
		function __construct(&$actKey, &$_cont, &$_gPar, $multiEntries)
		{
			$this->cont = &$_cont;
			$this->gPar = &$_gPar;
			$this->id = "";
			
			$this->addHeadEditor();
		}
		
		function addHeadEditor()
		{
			$GLOBALS["dropDownMenCntr"]++;

            $this->id .= (floor($GLOBALS["dropDownMenCntr"] / 10)).($GLOBALS["dropDownMenCntr"] % 10);
			
            // das drop down menu braucht tricks um seine Daten an das Formular zu übergeben
            // das nachher die Datenbank füttert. Im select tag wird als name der name eines
            // versteckten input feldes übergeben. Wenn das Select tag angeklickt wird, wird
            // per jquery der name ausgelesen und die Variable inputname gespeichert
            // wenn die auswahl getroffen ist, wird im Formular anhand des namens das input feld
            // gesucht und die auswahl als value gespeichert
			if ( !$GLOBALS["colText"] )
			{
				$this->cont['head'] .= '<script src="'.$GLOBALS['root'].'js/dropdown-menu.min.js" type="text/javascript"></script>
				<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/dropdown-menu.css" />
				<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/dropdown-menu-skin.css" />';
                
				$this->cont['jquery'] .= 'var actDdMen=""; var actDdSubMen=""; var inputName="";
                // init the drop down menu
                $("#ddmen").dropdown_menu({ sub_indicators:false, drop_shadows:false, close_delay:300, vertical:true });
                // when the drop down menu is being clicked
                $("[id^=ddmen_sel]").on("focus mousedown", function(e) {
                                        if ($.browser.webkit||$.browser.msie) {
                                            e.preventDefault();
                                        } else {
                                            this.blur(); window.focus();
                                        }
                                        // speicher den namen des select tags, in dem der namen des ziel input
                                        // feldes gespeichert ist
                                        inputName = this.name;
                                        actDdMen = $(this).attr("id");
                                        actDdSubMen = actDdMen.substr(actDdMen.length-2,2);
                                        actDdMen = actDdMen.substr(actDdMen.length-5,2);
                                        var pos = $("#ddmen_sel_"+actDdMen+"_"+actDdSubMen).position();
                                        $("#ddmen_sel_"+actDdMen+"_"+actDdSubMen).hide();
                                        $("#ddmen").css({"top":pos.top,"left":pos.left });
                                        $("#ddmen").show();
                                        });
                $("html").click(function() { $("[id^=ddmen_sel]").show(); $("#ddmen").hide(); });
                // when an entry in the drop down menu is clicked
                $("li.ddentry").click(function(e){
                                      e.stopPropagation();
                                      var nameStr = inputName;';
                                      
                if ($this->multiEntries == TRUE)
                    $this->cont['jquery'] .= 'nameStr += parseInt(actDdMen);';
                                      
                $this->cont['jquery'] .= '
                                      $(\'input[name="\'+nameStr+\'"]\').attr("value", $(this).attr("title") );
                                        $("form").submit();
                                      });';
				
				$GLOBALS["colText"] = true;
			}
		}
		
		function addToEditor() 
		{
			$list = '<ul id="ddmen" class="dropdown-menu dropdown-menu-skin" style="display:none;">';

            // null eintrag
            $list .= '<li class="ddentry" title="">&nbsp;';
            $list .= '<span></span>';
            $list .= '</li>';
            
            $xmlStr = "l0";
			$this->menuLevelCb($list, 0, $xmlStr, $this->gPar);
			$list .= '</ul>';
			
			$this->cont['body'] .= $list;
		}
		
		
		function menuLevelCb(&$list, $lvl, $xmlStr, &$gPar)
		{
			$levelNames = array();
			$levelNames[$lvl] = array();
			$levelNames[$lvl]['long'] = (array) $this->gPar->xt->xml->xpath( $xmlStr."/name/".$this->gPar->lang );
			$levelNames[$lvl]['short'] = (array) $this->gPar->xt->xml->xpath( $xmlStr."/@short" );
                                      
			// print all the lowest levels
			for ($j=0; $j<sizeof($levelNames[$lvl]['long']); $j++) 
			{
                // get short entry
                $xpath = $xmlStr;
				$q = $this->gPar->xt->xpath->query( $xmlStr.'['.($j+1).']' );
                if ($q->length > 0)
                    $xpath .= "[@short='".$q->item(0)->getAttribute('short')."']";

                $list .= '<li class="ddentry" title="'.$xpath.'">';
				
				if ( $levelNames[$lvl]['long'][$j] != "" )
				{
					$list .= ucfirst( $levelNames[$lvl]['long'][$j] );
				} else {
					$list .= "-untitled-";
				}
				
				// if there are sublevels
				$nrSubsLevls = $this->gPar->xt->xpath->query( $xmlStr."[@short='".$levelNames[$lvl]['short'][$j]."']/l".($lvl+1) );
				
				if ( $nrSubsLevls->length > 0 )
				{
					$list .= '<span class="dropdown-menu-sub-indicator"></span>';
					$list .= '<ul>';
					$this->menuLevelCb(
									   $list,
									   $lvl+1, 
									   $xmlStr."[@short='".$levelNames[$lvl]['short'][$j]."']/l".($lvl+1), 
									   $gPar
									   );
					
					$list .= '</ul>';
				}
				$list .= '</li>';
			}
		}
        
        function getItemName(&$gPar)
        {
            $itemName = "";
            $q = $gPar->xt->xpath->query( $gPar->val."/name/".$gPar->lang );
            if ( $q->length > 0 ) $itemName = $q->item(0)->nodeValue;

            return $itemName;
        }
	}	
?>