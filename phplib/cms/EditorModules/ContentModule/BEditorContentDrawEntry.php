<?php
	
	class BEditorContentDrawEntry
	{
		public $actLevel;
		public $scroll;
		public $xt;
		public $formName;
		public $gPar;
		public $post_name;
		public $head;
		public $cont;
		public $langPreset;
		public $curUrl;

		private $lang;
		protected $stylesPath;
        protected $mysqlH;
		
		//function BEditorContentDrawEntry(&$_stylesPath, $_lang, &$_keys, &$_cont, &$_mysqlH)
        function __construct(&$_stylesPath, $_lang, &$_keys, &$_cont, &$_mysqlH)
		{
			$this->actLevel = 0;
			$this->scroll = 0;
			$this->lang = $_lang;
			$this->formName = "";
			$this->stylesPath = $_stylesPath;
			$this->cont = &$_cont;
            $this->mysqlH = $_mysqlH;
			$this->gPar = new BEditorDrawPar($_keys);
		}
		
		
		function getActPaths(&$xmlStr, &$keys, $sel, &$actLvl)
		{			
			for ($i=0;$i<$keys['nrLevels'];$i++)
			{
				if ( $sel[$i] != "" )
				{
					$actLvl++;					
					$name = (array) $this->xt->xml->xpath( $xmlStr."/name/".$this->lang );
					$short_name = (array) $this->xt->xml->xpath( $xmlStr."/@short" );					
				} 				
			}
		}

		
        function draw(&$keys, $entrNr, $actKey, $entrMultId, $sel, $xmlStr, $cs)
        {
			if ( !$actKey->hideInEditor )
			{
				// get the positon in the database and generate responding paths
				// for the form names
				$actLvl = 0;
				$this->getActPaths($this->xmlStr, $keys, $sel, $actLvl);
				$sub_post_name = $this->post_name;
				
				$argXStr = "";
//                $xStr = $xmlStr."[".($entrNr+1)."]/".$actKey->xmlName;
                $xStr = $xmlStr."/".$actKey->xmlName;

                
				// get the value of the key
				if ( $entrMultId != -1 )
				{
					if ( $actKey->isLang )
					{
						$argXStr = $xStr."[".($entrMultId+1)."]";
						$xStr .= "[".($entrMultId+1)."]/".$this->lang;
					} else
					{
						$xStr .= "[".($entrMultId+1)."]";
					}
					$sub_post_name .= "_".$actKey->postId."_".$entrMultId;
				} else
                {
					// is base key
					if ( $actKey->isLang )
					{
						$argXStr = $xStr;
						$xStr .= "/".$this->lang;
					} 
					$sub_post_name .= "_".$actKey->postId."_";
				}


                $val = -1;
				$fObj = new BFormObj("change_".$this->post_name, $val, $keys, $this->xt, $this->mysqlH);
                
                $val = $this->xt->xml->xpath( $xStr );

				if ( sizeof($val) == 1 )
				{
					$val = $val[0];
                    
					// check for attributes
					if ( $argXStr != "" )
					{
						$args = $this->xt->xpath->query( $argXStr );
						if ( $args->length == 1 )
						{
							if( $args->item(0)->attributes->length > 0 )
							{
                                for( $i=0;$i<$args->item(0)->attributes->length;$i++)
                                    $val[ $args->item(0)->attributes->item($i)->localName ] = $args->item(0)->attributes->item($i)->nodeValue;
							}
						}
					}					
				}				


				//if ( sizeof( $val ) > 0 ) $val = $val[0]; else $val = "";
				$fObj->val = $val;
                
				// if the keys has subentries, also get them
				if ( $actKey->isMultType )
				{
                    $this->gPar->medialist = $this->xt->xml->xpath( $xmlStr."/".$actKey->xmlName."[".($entrMultId+1)."]/".$actKey->type );
				}
				

				// if normal entry key
				if ( $entrMultId != -1 )
				{
					$this->cont['body'] .= "<div class='keyBlock".$cs."'>";
					$this->gPar->drawTake = true;
				} else 
				{
					// if level descriptive	base key
					$this->cont['body'] .= "<div class='keyBlock".$cs."_b'>";
					$this->gPar->drawTake = false;
				}


				// print the name of the key
				$this->cont['body'] .= "<div class='label'>".$actKey->htmlName[$this->langPreset]."</div>";
				$this->cont['body'] .= "<div class='picAndForm fullrow'>";
				
				$this->gPar->post_name = &$sub_post_name;
				$this->gPar->val = &$val;
				$this->gPar->lang = $this->lang;
				$this->gPar->nrLevels = $keys['nrLevels'];
				$this->gPar->actLvl = $actLvl;
				$this->gPar->xt = &$this->xt;
				$this->gPar->curUrl = $this->curUrl;                            
                $this->gPar->xmlStr = $xmlStr."/".$actKey->xmlName."[".($entrMultId+1)."]";
                $this->gPar->clientFolder = $GLOBALS["clientBaseUrl"].getClientFolder($this->xt, $this->gPar->xmlStr, $this->mysqlH)[0];
                $this->gPar->clientUsers = $this->mysqlH->getUsersOfActClient();
                $this->gPar->formName = $this->formName;

				// add additional javascripts if needed
				$actKey->addHeadEditor($this->gPar, $this->stylesPath, $this->cont);
                
				// add the html code
                $actKey->addToEditor($this->gPar, $this->cont);

				$this->cont['body'] .= "</div>\n";
				
				if ( $entrMultId != -1 )
				{
					$this->cont['body'] .= $this->drawShiftArrows($sub_post_name, true);
					$this->cont['body'] .= "<input class='but trash' type='submit' name=\"remove_".$sub_post_name."\" value=''>";
				}

				$this->cont['body'] .= "</div>\n";
			}
		}

		function drawShiftArrows($post_name, $isBase, $shiftUp = false)
		{
			$str = "";

			if ( !$isBase ) 
				$str .= "<div style='position:relative;left:-40px;width:60px;margin-top:-20px;margin-left:auto'>";
			
			$str .= "<input class='up but";
            if ( $shiftUp ) $str .= "  shiftUp2";
            $str .= "' type='submit' name=\"shiftUp_".$post_name."\" value=''>";
			$str .= "<input class='down but";
            if ( $shiftUp ) $str .= "  shiftUp2";
            $str .= "' type='submit' name=\"shiftDown_".$post_name."\" value=''>";

			if ( !$isBase ) $str .= "</div>";
			
			return $str;
		}
		
		function setXt (&$_xt)
		{
			$this->xt = $_xt;
		}
		
		function setPostName($name)
		{
			$this->post_name = $name;
		}
	}
?>