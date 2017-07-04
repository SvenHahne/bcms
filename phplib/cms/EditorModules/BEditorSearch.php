<?php
    
    class BEditorSearch extends BEditorModule
    {
        protected $headKeys;
        protected $itemKeys;
        protected $headKeyNameIndexMap;
        protected $itemKeyNameIndexMap;
        protected $searchConfigDb;
        protected $xt = null;
        
        function __construct(&$xt, $lang, $mysqlH)
        {
            $this->moduleName = "search";
            $this->formName = "searchconfigform";
            $this->searchConfigDb = "searchConfig.xml";
            $this->lang = $lang[0];
            $this->langPreset = $lang[1];
            $this->xt = $xt;
            $this->mysqlH = $mysqlH;
            
            $this->search  = array ('ä',  'ö',  'ü',  'Ä', 'Ö', 'Ü', 'ß',  'ø', 'é', 'Á', ' ', '–',
                                    '“', '”', '/', '(', ')', '&', '%', '!', '"', '"', 'ñ', 'Ñ', 'í',
                                    'á', 'Á', 'Í', 'Ó', 'ó', 'É', 'é', '~');
            $this->replace = array ('ae', 'oe', 'ue', 'Ae','Oe','Ue','sz', 'o', 'e', 'a', '',  '-',
                                    '"', '"', '',  '',  '',  '',  '',  '', '', '', 'n', 'N', 'i',
                                    'a', 'A', 'I', 'O', 'o', 'E', 'e', '');
            
            $this->labels = array();
            $this->labels['addAsso'] = array();
            $this->labels['addAsso']['de'] = "hinzufuegen:"; $this->labels['addAsso']['en'] = "Add association:"; $this->labels['addAsso']['es'] = "Añadir asociación:";

            $this->labels['name'] = array();
            $this->labels['name']['de'] = "schlüssel:"; $this->labels['name']['en'] = "key:"; $this->labels['name']['es'] = "clave:";

            $this->labels['keys'] = array();
            $this->labels['keys']['de'] = "Assoziationen:"; $this->labels['keys']['en'] = "association:"; $this->labels['keys']['es'] = "asociación:";

            $this->init();
        }
        
        
        function init()
        {
            $this->xt = new XMLTools("db/".$this->searchConfigDb);
        }
        
        
        function addHeadEditor(&$cont)
        {}
        
        
        function printForm(&$cont, &$keys, &$sel, $scroll)
        {
            $link = 'editor.php?mod='.$this->moduleName.'&lang='.$this->lang;
            
            // select formular
            $cont['body'] .= '
                <div class="rightCol">
                <form name="'.$this->formName.'" id="'.$this->formName.'" class="main" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">
            ';

            $cont['body'] .= '<h2>Search Engine Manager</h2>';
            
            $this->printLang($cont['body'], $keys, $this->lang, -32);

            $cont['body'] .= '<div class="highL highLvl0">';

            // invisible submit button for submit by return
            $cont['body'] .= '<input type="submit" name="newAsso" value="" style="position:absolute;visibility:hidden;" />';
            
            // new association
            $cont['body'] .= '<div class="label">'.$this->labels['addAsso'][$this->lang].'</div>';
            $cont['body'] .= '<div class="picAndForm fullrow" style="height:32px;">';
            $cont['body'] .= '<input type="text" name="newassoname" value=""><input type="submit" class="hook but shiftUp2" name="subm" value="" />';
            $cont['body'] .= '</div>';
            
            $cont['body'] .= '</div>';

            // print the entries
            $q = $this->xt->xpath->query($this->lang."/assoc");

            for($i=0;$i<$q->length;$i++)
            {
                $cont['body'] .= '<div class="highL highLvl'.(($i+1) % 2).'">';

                $cont['body'] .= '<div class="label">'.$this->labels['name'][$this->lang].'</div>';
                $cont['body'] .= '<div class="picAndForm fullrow">';
                $cont['body'] .= '<input type="text" name="change_assoc_name_'.$i.'" value="'.$q->item($i)->getAttribute('name').'" />';
                $cont['body'] .= '<input type="submit" class="trash but shiftUp2" name="assocremove_'.$i.'" />';
               // $cont['body'] .= '<input type="submit" class="hook but shiftUp2" name="subm" value="" />';
                $cont['body'] .= '</div>';
                
                // print the associations
                $akeys = $q->item($i)->childNodes;
                $aStr = "";
                
                for($j=0;$j<$akeys->length;$j++)
                {
                    $aStr .= $akeys->item($j)->nodeValue;
                    if($j < $akeys->length -1) $aStr .= ", ";
                }

                $cont['body'] .= '<div class="label">'.$this->labels['keys'][$this->lang].'</div>';
                $cont['body'] .= '<div class="picAndForm fullrow">';
                $cont['body'] .= '<input class="inp" type="text" name="change_assoc_keys_'.$i.'" value="'.$aStr.'" />';
                $cont['body'] .= '</div>';
                
                $cont['body'] .= '</div>';
            }
            
            $cont['body'] .= '<input type="submit" name="change" value="" style="position:absolute;visibility:hidden;" />';
            
            $cont['body'] .= '</form></div>';
        }
        
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {
            $postKeys = array_keys($_POST);

            // ------ add new association --------------------------------------------------
            
            if ( isset($post['newassoname']) && $post['newassoname'] != "")
            {
                $q = $this->xt->xpath->query($lang);
                $node = $this->xt->doc->createElement("assoc");
                
                $name = $this->xt->doc->createAttribute("name");
                $name->value = str_replace($this->search, $this->replace, $post['newassoname']);
                    
                $node->appendChild($name);
                $q->item(0)->appendChild($node);
            }

            // ------ change association name --------------------------------------------------

            $matches = array_filter($postKeys, function ($haystack) {
                                    return( strpos($haystack, "change_assoc_name") !== false );
                                    });

            foreach($matches as $key => $val)
            {
                $nr = explode("_", $val);
                $node = $this->xt->xpath->query($lang."/assoc[".($nr[3]+1)."]/@name");
                $node->item(0)->nodeValue = $_POST[$val];
            }
            
            
            // ------ change association keys --------------------------------------------------
            
            $matches = array_filter($postKeys, function ($haystack) {
                                    return( strpos($haystack, "change_assoc_keys") !== false );
                                    });

            foreach($matches as $key => $val)
            {
                $nr = explode("_", $val);
                
                $parent = $this->xt->xpath->query($lang."/assoc[".($nr[3]+1)."]");
                $parent = $parent->item(0);
                $q = $this->xt->xpath->query($lang."/assoc[".($nr[3]+1)."]/key");

                // remove all children
                for($i=0;$i<$q->length;$i++)
                    $parent->removeChild( $q->item($i) );


                // add all separated keys as nodes
                // clean the search term, convert special characters
                $sTerm = remove_accents( $_POST[$val] );
                $sTerm = str_replace('-', ' ', $sTerm);
                $sTerm = preg_replace('/[^\a-zA-Z0-9\ \,]/s', '', $sTerm);
                
                // split at " " and ","
                $sTerms = preg_split('/[\,\ ]/', $sTerm);

                // remove empty entries and arrays
                $sTerms = array_filter($sTerms);
                
                foreach($sTerms as $term)
                {
                    $node = $this->xt->doc->createElement("key");
                    $node->nodeValue = str_replace($this->search, $this->replace, $term);
                    $parent->appendChild($node);
                }
            }
            
            
            // ------ remove association --------------------------------------------------
            
            $matches = array_filter($postKeys, function ($haystack) {
                                    return( strpos($haystack, "assocremove") !== false );
                                    });
            
            if (sizeof($matches) > 0)
            {
                foreach($matches as $key => $val)
                {
                    $nr = explode("_", $val);
                    $q = $this->xt->xpath->query($lang."/assoc[".($nr[1]+1)."]");
                    if ($q->length > 0)
                        $q->item(0)->parentNode->removeChild($q->item(0));
                }
            }
            
            $this->xt->saveDOM();
        }
        
        function printLang(&$body, &$keys, $lang, $vOffs)
        {
            for ($i=0;$i<sizeof($keys['langs']);$i++)
            {
                $body .= '<div style="display:inline;margin-top:'.$vOffs.'px;">
                <input class="countrLogo" name="changeLang" value="'.$keys['langs'][$i].'" onclick="var path=document.getElementById(\''.$this->formName.'\').action.replace(/lang=[a-z]{2}/, \'\');document.getElementById(\''.$this->formName.'\').action=path;document.getElementById(\''.$this->formName.'\').action+=(\'lang='.$keys['langs'][$i].'&oldlang='.$lang.'\');document.getElementById(\''.$this->formName.'\').submit();" type="image" src="pic/'.$keys['langs'][$i].'.jpg" alt="Absenden" style="';
                if ($lang == $keys['langs'][$i]) $body .= 'border:solid 1px black;';
                $body .= 'height:14px;margin-right:2px;margin-top:5px;">
                ';
            }
        }
    }
?>