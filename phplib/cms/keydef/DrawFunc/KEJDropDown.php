<?php
	class KEJDropDown
	{
        public $initNames;
        
		function __construct()
		{
			$this->id = "";
            $this->initNames = array();
            $this->initNames['de'] = "Kategorie";
            $this->initNames['es'] = "Categoria";
            $this->initNames['en'] = "Category";
		}
		
		function addHead(&$head)
		{
			$GLOBALS["dropDownMenCntr"]++;
			$this->id .= (floor($GLOBALS["dropDownMenCntr"] / 10)).($GLOBALS["dropDownMenCntr"] % 10);
			
			if ( !$GLOBALS["colText"] )
			{
				$head['base'] .= '<script src="'.$GLOBALS['root'].'js/dropdown-menu.js" type="text/javascript"></script>
				<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/dropdown-menu.css" />
				<link rel="stylesheet" type="text/css" href="'.$GLOBALS['root'].'style/dropdown-menu-lvlskin.css" />';
                $GLOBALS["colText"] = true;
            }
            
            // #ddmen
            $head['jqDocReady'] .= '$(".dropdown-menu").dropdown_menu({ sub_indicators:true, dropAction: "click", open_delay:0, close_delay:0 });
                ';
		}
		
        function draw(&$body, &$xmlItem, &$dPar, &$list)
		{            
			$body['sec1'] .= '<ul id="ddmen" class="dropdown-menu dropdown-menu-skin">';
            $body['sec1'] .= '<li class="ddentry">';
            
            if ( isset($_GET['sel']) ) {
                $body['sec1'] .= ucfirst( $list[ $_GET['sel'] ]['name'] );
            } else {
                $body['sec1'] .= ucfirst( $list[0]['name'] );
//                $body['sec1'] .=  $this->initNames[$dPar->lang];
            }
            
            $body['sec1'] .= '<ul>';
            
			for ($j=0; $j<sizeof($list); $j++)
            {
                $dPar->linkAttr['sel'] = $j;
                $linkAttr = ""; foreach ($dPar->linkAttr as $k => $v ) $linkAttr .= "&".$k."=".$v;
                $link = $dPar->link.$linkAttr;

				$body['sec1'] .= '<a href="'.rewriteLink($link, $dPar->xt, $dPar->keys).'"><li>';
                $body['sec1'] .= ucfirst( $list[$j]['name'] );
				$body['sec1'] .= '</li></a>';
            }

            $body['sec1'] .= '</ul></li>';
            $body['sec1'] .= '</ul>';
		}
	}
?>