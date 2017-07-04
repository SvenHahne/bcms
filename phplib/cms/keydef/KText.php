<?php
		
	class KText extends KeyDef
	{
        function __construct0() { $this->init(); }
        
        function __construct3($a1,$a2,$a3)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
			$this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
            $this->init();
		}
        
        function __construct4($a1,$a2,$a3,$a4)
		{
			$this->args = $a1;
			$this->argsKeyDef = $a2;
            $this->argsVals = $a3;
			$this->argsStdVal = $a4;
            foreach ($this->argsVals as $val)
                array_push($this->argsShow, true);
			$this->init();
		}
        
        function init()
		{
			$this->xmlName = "cont";
			$this->htmlName['de'] = "Text";
			$this->htmlName['es'] = "Texto";
			$this->htmlName['en'] = "Text";
			$this->type = "textarea";
			$this->postId = "cont";
			$this->size = 400;
			$this->maxsize = 4;
			$this->isLang = TRUE;
		}

            
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
			if ( $GLOBALS["cleditor"] == false )
			{
				$cont['head'] .= '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/jquery.cleditor.css" />
				<script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.cleditor.js"></script>
				<script id="cleditor" type="text/javascript">
					$(document).ready(function() {
					$("textarea").each( function( index ){
						$(this).cleditor({
                            useCSS: false,
							width:	465, // width not including margins, borders or padding
							height:	160, // height not including margins, borders or padding
							controls:     // controls to add to the toolbar
								"bold italic underline style color removeformat | undo redo | " +
								"link unlink | cut copy paste pastetext | source",
                            colors :
                                "FFF 989898 46c3d9 000",
							styles:       // styles in the style popup
								[["normal", "<p>"]],
							docCSSFile:	"'.$GLOBALS['root'].'style/'.$stylesPath.'" // use CSS to style HTML when possible (not supported in ie)
						});
					});
				});
				</script>';
				
				$GLOBALS["cleditor"] = true;
			}
		}
		
		function addToEditor(&$gPar, &$cont)
		{
			$df = new KETextArea($this, $cont, $gPar);
		}
		
        function addHead(&$head, &$dPar)
		{
        	parent::addHead($head, $dPar);
		}

        function draw(&$head, &$body, &$xmlItem, &$dPar)
		{
            foreach ($xmlItem->children() as $sub)
                if ( $sub->getName() == $dPar->lang )
                {
                    switch($dPar->args['style'])
                    {
                    case "nText_vCent" :
                        $body .= '<div style="height:100%;display:table;">';
                        $body .= '<div class="'.$dPar->args['style'].'">'.$sub.'</div>';
                        $body .= '</div>';
                        break;
                    default:
                        $body .= '<div class="'.$dPar->args['style'].'">'.$sub.'</div>';
                        break;
                    }
                }
            $body .= "<br>";
		}
	}
?>