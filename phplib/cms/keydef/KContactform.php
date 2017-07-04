<?php
    
    // at the moment only one Contact Form is allowed per page
    require_once ("./phplib/cms/BSendMail.php");
    
    class KContactForm extends KeyDef
    {
        protected $fields;
        protected $secLabl;
        protected $secs;
        protected $butLabl;
        
        public function __construct()
        {
            $this->xmlName = "contactform";
            $this->htmlName['de'] = "Kontakt Formular";
            $this->htmlName['es'] = "Formulario Contacto";
            $this->htmlName['en'] = "Contact form";
            $this->type = "contactform";
            $this->postId = "contactform";
            $this->args = array("gridStyle", "email");
            $this->argsKeyDef = array("KEDropDown", "");
            $this->argsVals = array(array("col5_row2"), "");
            $this->argsStdVal = array("col5_row2", "");
            $this->argsShow = array(true, true);
            $this->init();
        }

        function init()
        {
            $this->fields['de'] = array("name", "email", "betreff");
            $this->fields['es'] = array("nombre", "email", "asunto");
            $this->fields['en'] = array("name", "email", "section");
            
            $this->secLabl['de'] = "Betreff";
            $this->secLabl['es'] = "Asunto";
            $this->secLabl['en'] = "Section";
            
            $this->secs['de'] = array("Bestellungen", "Allgemeine Fragen");
            $this->secs['es'] = array("consultas", "trabaja con nosotros");
            $this->secs['en'] = array("Order", "General questions");
            
            $this->butLabl['de'] = array("SENDEN", "ANHANG");
            $this->butLabl['es'] = array("ENVIAR", "ADJUNTO");
            $this->butLabl['en'] = array("SEND", "ATTACHMENT");
        }

        function addHeadEditor(&$gPar, &$stylesPath, &$cont)
        {}
        
        function addToEditor(&$gPar, &$cont)
        {
            $gPar->showVal = false;
            $gPar->drawTake = false;
            $gPar->inpArgClass = "inp2";
            $df = new KEText($this, $cont, $gPar);
        }
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);

            if ( !isset($GLOBALS["contForm"]) || $GLOBALS["contForm"] == false )
            {
                $head['jqDocReady'] .= '
                $("button.contFormApendix").click(function() { $("input[type=file]").click(); });
                ';
                
                $GLOBALS["contForm"] = true;
            }
            
            if ( isset($_POST['message']) || isset($_POST['subject']) )
            {
                $hasError = FALSE;
                             
                // check empty fields
                for ($i=0;$i<sizeof($this->fields['de']);$i++)
                {
                    if ( $_POST[$this->fields['en'][$i]] == "" )
                    {
                        print "<script type='text/javascript'>";
                        print "alert('Falta ".$this->fields['es'][$i]."');";
                        print "</script>";
                        $hasError = TRUE;
                    }
                }
                
                if ( $_POST['message'] == "" )
                {
                    print "<script type='text/javascript'>";
                    print "alert('Error: Kein Inhalt!');";
                    print "</script>";
                    $hasError = TRUE;
                }
                
                if ( $hasError == FALSE )
                {
                    // get email argument from database
                    $q = $dPar->xt->xml->xpath($dPar->xmlStr."/".$this->xmlName);

                    if ( sizeof($q) > 0 )
                    {
                        $email = (string)$q[0]['email'];                        
                        $sendM = new BSendMail($email,
                                               $_POST[ $this->fields['en'][1] ],
                                               $email,
                                               $_POST[ $this->fields['en'][2] ],
                                               $_POST['message']);
                        $sendM->setSmtpInfo($GLOBALS["smtpHost"], $GLOBALS["smtpPort"], true, $GLOBALS["smtpUser"], $GLOBALS["smtpPass"]);
                        $sendM->getAttachment("upload", "upload");
                        $sendM->send();
                    }
                }
            }
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {

            $body .= "<form class='contForm' id='contForm' name='contacto' action='".$dPar->link."' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
            for ($i=0;$i<sizeof($this->fields['de']);$i++)
            {
                $body .= "<div class='contFormCont'><div class='contFormRow'>";
                $body .= "<div class='contFormLabel'>".mb_strtoupper($this->fields[$dPar->lang][$i])."</div>";
                $body .= "<div class='contFormInput'><input class='contForm' name='".$this->fields['en'][$i]."' type='text' size='0' maxlength='50'></input></div>";
                $body .= "</div></div>";
            }

            // secciones
            /*

            $body .= "<div class='contFormCont'>";
            $body .= "<div class='contFormLabel'>".mb_strtoupper($this->secLabl[$dPar->lang])."</div>";
            $body .= "<div class='contFormInput'><input class='contForm' name='".$this->secLabl['en'][$i]."' type='text' size='0' maxlength='50'></input></div>";

            $body .= "<div class='contFormDropDown'>";
            $body .= "<select class='contForm'  dir='rtl' name='subject' onclick='$(\".contFormOpt\").text(this.options[this.selectedIndex].value);'>";
            
            for ($i=0;$i<sizeof($this->secs['de']);$i++)
                $body .= "<option>".$this->secs[$dPar->lang][$i]."</option>";

            $body .= "</select></div>";
            $body .= "</div>";
            */

           // $body .= "<div class='contFormOpt'>".$this->secs[$dPar->lang][0]."</div>";

            // textarea
            $body .= "<div class='contFormCont'>";
            $body .= "<textarea class='contForm' name='message' cols='50' rows='10'></textarea>";
            $body .= "</div>";
            
            $body .= "<div class='contFormCont' style='border:0'>";
            $body .= "<div class='contFormSubmitCont'>";
            // hidden input field
            $body .= "<input type='file' id='file1' name='upload' style='display:none'></input>";
          //  $body .= "<button class='contFormApendix' type='button'>".$this->butLabl[$dPar->lang][1]."</button>";
            $body .= "<button class='contFormSubmit' type='submit'>".$this->butLabl[$dPar->lang][0]."</button>";
            $body .= "</div>";
            $body .= "</div>";
            
            $body .= "</form>";
        }
    }
    ?>