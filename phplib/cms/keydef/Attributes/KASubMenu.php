<?php
    class KASubMenu  extends KeyDef
    {
        public function __construct()
        {
            $this->xmlName = "@submenu";
            $this->htmlName['de'] = "Sub-Menu";
            $this->htmlName['es'] = "Submenú";
            $this->htmlName['en'] = "Sub-menu";
            $this->type = "submenu";
            $this->postId = "@submenu";
            $this->initAtNewEntry = TRUE;
            $this->isAttr = TRUE;
            $this->showShiftArrows = FALSE;
        }
        
        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {
            $this->cascMen = new KECascadingMenu($this, $cont, $gPar, FALSE);
        }
        
        function addToEditor(&$gPar, &$cont)
        {
            $basePName = $gPar->post_name;
            $val = $gPar->val;
            
            // get name of ref entry
            $entr = "";
            $entrLink = "";
            if ($val['submenu'] != "")
            {
                $entrLink = $val['submenu'];
                $q = $gPar->xt->xpath->query($val['submenu']."/name/".$gPar->lang);
                if ( $q->length > 0 ) $entr = $q->item(0)->nodeValue;
            }
            
            $cont['body'] .=  '<div style="width:470px;height:22px;">';
            $this->cascMen->addToEditor();
            
            $cont['body'] .= '<select class="ddmen2" name="change_'.$basePName.'" id="ddmen_sel_'.$this->cascMen->id.'_00"><option>'.$entr.'</option></select>';
            
            // hidden input field to submit the value
            $cont['body'] .= '<input type="hidden" name="change_'.$basePName.'"';
            $cont['body'] .= 'value="'.$entrLink.'">';
            
            $cont['body'] .=  '</div>';
        }
    }
    ?>