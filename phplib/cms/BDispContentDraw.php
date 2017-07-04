<?php
	
	class BDispContentDraw 
	{
		public $xt;
		public $dPar;
        
		function __construct(&$_xt, $_lang, &$_keys, $_xmlStr, $_actLevel,
                             &$_head, &$_body, $_shiftDown, &$_linkAttr,
                             &$_mysqlH, $curUrl, &$_subNavList, $_loginHandler,
							 $_imgPrfx, $_doResizeImgs, $_changedToContent,
							 $_calledFromStart)
		{				
            $this->xt = $_xt;                        
			$contQ = $this->xt->xml->xpath($_xmlStr);
							
            //check if there was a searching call
            $s = new BDispSearch($this->xt, $_keys, $_lang, 15, "node");
            // hack standard search is in l0[@short="0000"]/l1[@short="0002"]
            $search = $s->proc("", $GLOBALS["searchRestrict"], "l0[@short='0000']/l1[@short='0002']");

            $clientInf = getClientFolder($_xt, $_xmlStr, $_mysqlH);

            $this->dPar = new BDispPar();
            $this->dPar->actLevel = $_actLevel;
            $this->dPar->calledFromStart = $_calledFromStart;
            $this->dPar->changedToContent = $_changedToContent;
            $this->dPar->curUrl = $curUrl;
            $this->dPar->imgPrfx = $_imgPrfx;
            $this->dPar->doResizeImgs = $_doResizeImgs;
            $this->dPar->keys = &$_keys;
            $this->dPar->link = $this->getCurUrl($_xmlStr);
            $this->dPar->linkAttr = $_linkAttr;
            $this->dPar->loginHandler = $_loginHandler;
            $this->dPar->lang = $_lang;
            $this->dPar->nrLevels = $_keys['nrLevels'];
            $this->dPar->mysqlH = &$_mysqlH;
            $this->dPar->subNavList = &$_subNavList;
            $this->dPar->clientFolder = $GLOBALS["clientBaseUrl"].$clientInf[0];
            $this->dPar->clientUsers = $_mysqlH->getUsersOfClient($clientInf[1]);
            $this->dPar->xmlStr = $_xmlStr;
            $this->dPar->xt = &$_xt;
                        
            // draw it!
            if ( !isset($_GET['search']) )
            {                
                //draw login screen;
                $layout = "KLPlain";
                $layout = new $layout();
                
                $ar = $this->xmlStrToPath($layout, $_xmlStr);
                $this->dPar->linkWithUsrPath = $ar[0];
                $this->dPar->linkPath = $ar[1];
                
                if ($_loginHandler->requestLogin)
                {
                    $_loginHandler->draw($_body['sec2'], $this->dPar);
                } else
                {
                    // load scripts and stylesheets if necessary
                    $layout->addHead($_head, $this->dPar);
                    $layout->draw($_head, $_body, $contQ[0], $this->dPar);
                }
            } else
            {
                $layout = "KLSearchResult";
                $layout = new $layout();
                
                $ar = $this->xmlStrToPath($layout, $_xmlStr);
                $this->dPar->linkWithUsrPath = $ar[0];
                $this->dPar->linkPath = $ar[1];
                
                // load scripts and stylesheets if necessary
                $layout->addHead($_head, $this->dPar);
                
                // draw search results
                $layout->draw($_head, $_body, $search, $this->dPar);
            }
		}

        function xmlStrToPath( $class, $xmlStr )
		{
            $path = array("", "");
			$spl = explode( "/", $xmlStr );
            
			for ( $i=0;$i<sizeof($spl);$i++)
			{
				$valArg = explode( "[", $spl[$i] );

                // wenn type
                // remove the user "l0" entry
				if ( sizeof($valArg) > 1 && $valArg[0] != "data")
				{
                    $subPath = explode("'", $valArg[1]);
                    
                    // if is short arg
                    if (sizeof($subPath) > 1 )
                    {
                        if ($valArg[0] != "l0")
                        {
                            $path[1] .= $subPath[1]."/";
                        }
                        $path[0] .= $subPath[1]."/";
                    }
				}
			}

			return $path;
		}
        
        
        public function getCurUrl(&$xmlStr)
		{
			$linkStr = $GLOBALS['root']."index.php?";
			$str = str_replace("[@short='", "=", $xmlStr);
			$str = str_replace("']", "", $str);
            $str = str_replace("/", "&", $str);
			return $linkStr.$str;
		}        
	}
?>