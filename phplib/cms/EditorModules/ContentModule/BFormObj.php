<?php 
	class BFormObj 
	{	
		protected $keys;
		protected $xt;
        protected $mysqlH;
		
		public $keyar;
		public $keyType;
		public $entrNr;
		public $ins;
		public $val;
		public $sel;
		public $subArg;
		public $subEntrNr;
        public $capsNode;
		public $xmlStr;
		public $xmlStrOneUp;
		public $xmlStrNoName;
		public $actLvl;
		public $actKey;
        public $capsKey;
		public $lang;
		public $isArg = false;
		public $isSubArg = false;
        public $isLang = false;
		
		function __construct($key, &$value, &$keys, &$xt, &$mysqlH)
		{
			$this->val = $value;
			$this->keys = &$keys;
			$this->xt = &$xt;
			$this->actLvl = -1;
			$this->entrNr = -1;
			$this->actKey = "";
			$this->capsKey = "";
            $this->keyar = explode( '_', $key );
            $this->mysqlH = $mysqlH;
            
			// get the instruction
			$this->ins = $this->keyar[0];
			
			// get the selected levels
			$this->sel = array();
			for ($i=0;$i<$keys['nrLevels'];$i++)
			{
				if ( sizeof( $this->keyar ) > ($i+1) 
					&& $this->keyar[$i+1] != ""
					&& $this->keyar[$i+1] != "level") 
				{
					$this->sel[$i] = $this->keyar[$i+1];
					$this->actLvl++;
				}
			}
			
			// get the type of the key and the corresponding class
			// actkey must be set
			if ( sizeof( $this->keyar ) > ($keys['nrLevels']+1) )
			{
				$this->keyType = $this->keyar[ $keys['nrLevels'] +1 ];

				if ( sizeof( $this->keyar ) > ($keys['nrLevels'] +2) ) 
					$this->entrNr = intval( $this->keyar[ $keys['nrLevels'] +2 ] );

				// if there´s a valid keyType get the corresponding class
				if ( !preg_match('#[\d]#', $this->keyType) )
				{					
					$keyId = 0;
					if ( $this->actLvl > -1 )
					{
						while ($keyId < sizeof( $keys[$this->actLvl] )
							   && $keys[$this->actLvl][$keyId]->postId != $this->keyType )
							$keyId++;
						
						// if nothing found, search the base arrays
						if ( $keyId == sizeof($keys[$this->actLvl]) )
						{
							$keyId = 0;
							while ($keys['base'.$this->actLvl][$keyId]->postId != $this->keyType 
								   && $keyId < sizeof($keys['base'.$this->actLvl]) -1)
								$keyId++;
							$this->actKey = $keys['base'.$this->actLvl][$keyId];

						} else {
							$this->actKey = $keys[$this->actLvl][$keyId];
						}
					}
				}
			}

			// if key is level, correct the keytype and the actkey
			if ( $this->keyType == "level" )
			{
				$this->keyType = "l";
				$this->actKey = new KLevel();
			}
            
            if ($this->actKey != "" && $this->actKey->isLang)
                $this->isLang = true;

            /*
            // get the argument of the child Node if this is the child Node has Arguments
			$this->subArgs = "";
			if ( sizeof( $this->keyar ) >= $keys['nrLevels'] +3 ) 
			{
				if ( preg_match('#[\d]#', $this->keyar[ $keys['nrLevels'] +2 ]) )
				{
					$this->subEntrNr = intval( $this->keyar[ $keys['nrLevels'] +2 ] );
				} else
				{
					$this->subArgs = $this->keyar[ $keys['nrLevels'] +2 ];
				}
			}
			*/

            // get arguments of the child Nodes
			$this->subArg = "";
			if ( sizeof( $this->keyar ) >= $keys['nrLevels'] +5 )
				$this->subArg = $this->keyar[ $keys['nrLevels'] +4 ];

            
			// build a xml string that points to the corresponding entry in the database
			$this->xmlStr = "";
			for ($i=0;$i<($keys['nrLevels']);$i++) 
				if (sizeof( $this->keyar ) > ($i+1) 
					&& $this->keyar[$i+1] != "" 
					&& $this->keyar[$i+1] != "level" ) 
					$this->xmlStr .= "l".$i."[@short='".$this->sel[$i]."']/";		

			if ( $this->keyType != "l" )
            {
				$this->xmlStr .= $this->keyType;
			} else {
				$this->keyType .= $this->actLvl+1;
			}
			
			if ( substr($this->xmlStr, -1) == "/" )
				$this->xmlStr = substr( $this->xmlStr, 0, strlen($this->xmlStr)-1);			
			
			if ( sizeof( $this->keyar ) >= $keys['nrLevels'] + 4 )
			{
				// if no literal is argument
				if ( !preg_match('#[\d]#', $this->keyar[ $keys['nrLevels'] + 3 ] ) )
				{
					$this->xmlStr .= "[".($this->keyar[ $keys['nrLevels'] + 2 ]+1)."]/".$this->keyar[ $keys['nrLevels'] + 3 ];
				} else {
					// multiples
					$this->xmlStr .= "[".($this->keyar[ $keys['nrLevels'] + 2 ]+1)."]/".$this->actKey->type."[".($this->keyar[ $keys['nrLevels'] + 3 ] +1)."]";
				}
			} else if ( sizeof( $this->keyar ) >= $keys['nrLevels'] + 3 )
			{
				$this->xmlStr .= "[".($this->keyar[ $keys['nrLevels'] + 2 ]+1)."]";
			}
			
			
			if ( sizeof( $this->keyar ) >= $keys['nrLevels'] +5 && $this->keyar[ $keys['nrLevels'] +4 ] != "" )
			{
				$this->xmlStr .= "/".$this->keyar[ $keys['nrLevels'] + 4 ];
				$this->isSubArg = true;
			}
            
            // look for encapsuled elements in childNodes
            if (sizeof($this->keyar) > $keys['nrLevels'] +6)
            {
                $this->capsNode = $this->keyar[$keys['nrLevels'] +5];
                $this->xmlStr .= "/".$this->keyar[$keys['nrLevels'] +5]."[".($this->keyar[$keys['nrLevels'] +6] +1)."]";

                // if there´s a valid keyType get the corresponding class
                if ( !preg_match('#[\d]#', $this->capsNode) && $this->capsNode != "" )
                {
                    foreach($this->actKey->element as $el)
                        if (get_class($el) == "K".ucfirst($this->keyar[$keys['nrLevels'] +5]))
                            $this->capsKey = $el;
                    
                    if ($this->capsKey->isLang)
                        $this->isLang = true;
                }
                
                // look for arguments of the encapsuled elements in childNodes
                if (sizeof($this->keyar) >= $keys['nrLevels'] +8)
                    $this->xmlStr .= "/".$this->keyar[$keys['nrLevels'] +7];
            }
			
            // check if is argument
            foreach($this->keyar as $k => $v)
            	$this->isArg = $this->isArg || (strlen($v) > 0 && (bool)($v[0] == "@"));
            
			$this->xmlStrOneUp = $this->getXmlOneLevelUp();
			$this->xmlStrNoName = $this->getXmlNoName();
		}
		
		
		function getFilePaths()
		{
			$pAr = array();
            
            // get client path, if necessary
            $clientPath = "";
            $clientFolder = getClientFolder($this->xt, $this->xmlStr, $this->mysqlH);
            if ($clientFolder[0] != "")
                $clientPath .= "clients/".$clientFolder[0];
            
			$path = $clientPath.$this->actKey->uploadPath."/";

			for ($i=0;$i<($this->keys['nrLevels']-1);$i++) 
				if ( $this->keyar[$i+1] != ""
                    && (!isset($GLOBALS["xmlPubClientShort"]) ||
                        (isset($GLOBALS["xmlPubClientShort"]) && $GLOBALS["xmlClientLvl"] != $i )))
					$path .= $this->sel[$i]."/";

            
			$valName = $this->xt->xpath->query( $this->xmlStr );
						
			if ( $valName->item(0)->nodeValue != "" )
			{
				// if file is a image get also the sized version			
				if ( $this->actKey->uploadType == "image" )
				{				
					for ($i=0;$i<sizeof( $this->keys['sizeMap']["name"] );$i++)
					{
						array_push($pAr, $path.$this->keys['sizeMap']["name"][$i].$valName->item(0)->nodeValue);
					}
				} else 
				{
					array_push($pAr, $path.$valName->item(0)->nodeValue );
				}
			}

			return $pAr;
		}
		
		
		function getDstRoot($clientFolder)
		{
			$path = $GLOBALS["clientBaseUrl"]."/".$clientFolder."/".$this->actKey->uploadPath."/";

			for ($i=0;$i<$this->keys['nrLevels'];$i++)
			{
				if ( $this->keyar[$i+1] != "" && !(isset($GLOBALS["xmlClientLvl"]) && $i <= $GLOBALS["xmlClientLvl"]) )
				{
					$path .= $this->sel[$i];
					if ( $i<($this->keys['nrLevels']-1) ) $path .= "/";
				}
			}

			return $path;
		}
		
		
		function getPostVal(&$_post, $ind)
		{            
            $res = $ind."_";
			for ($i=1;$i<sizeof($this->keyar);$i++)
			{
				$res .= $this->keyar[$i];
				if ( $i != sizeof($this->keyar)-1 ) $res .= "_";
			}
			$this->val = $_post[$res];
			
			return $this->val;
		}
		
		
		function getXmlOneLevelUp()
		{
			$newXmlStr = "";
			$splitStr = explode("/", $this->xmlStr);
			for ($i=0;$i<sizeof($splitStr)-1;$i++)
			{
				$newXmlStr .= $splitStr[$i];
				if ( $i < sizeof($splitStr)-2 ) $newXmlStr .= "/";
			}

			if ( substr($newXmlStr, -1) == "/" )
				$newXmlStr = substr( $newXmlStr, 0, strlen($newXmlStr)-1);

			
			return $newXmlStr;
		}
        
        
        function getXmlGetLevel()
        {
            $newXmlStr = "";
            $splitStr = explode("/", $this->xmlStr);

            // if it´s an attribute, remove it
            if ( $splitStr[ sizeof($splitStr) -1 ][0] == "@")
            {
                for ($i=0;$i<sizeof($splitStr)-1;$i++)
                {
                    $newXmlStr .= $splitStr[$i];
                    if ( $i < sizeof($splitStr)-2 ) $newXmlStr .= "/";
                }

                if ( substr($newXmlStr, -1) == "/" )
                    $newXmlStr = substr( $newXmlStr, 0, strlen($newXmlStr)-1);
            }
            
            // if there is an entry selected, remove the select brackets
            if ($newXmlStr[ strlen($newXmlStr) -1 ] == "]")
                $newXmlStr = substr( $newXmlStr, 0, strripos($newXmlStr, "["));
            
            return $newXmlStr;
        }
		
		
		function getXmlNoName()
		{
			$newXmlStr = "";
			$splitStr = explode("/", $this->xmlStr);
			for ($i=0;$i<sizeof($splitStr);$i++)
			{
				$tStr = explode("[", $splitStr[$i]);
				
				if ( $i == sizeof($splitStr) -1 )
				{
					if ( $tStr[0] != "name" ) $newXmlStr .= $splitStr[$i];
				} else {
					$newXmlStr .= $splitStr[$i];
					if ( $i < sizeof($splitStr)-1 && $tStr[0] != "name" ) 
						$newXmlStr .= "/";
				}
			}
			
			if ( substr($newXmlStr, -1) == "/" )
				$newXmlStr = substr( $newXmlStr, 0, strlen($newXmlStr)-1);
			
			return $newXmlStr;
		}
		
		
		function dump()
		{	
			print "<br>";
			print "ins: ".$this->ins."<br>";
			print "keyType: ".$this->keyType."<br>";
			print "entrNr: ".$this->entrNr."<br>";
			print "subEntrNr: ".$this->subEntrNr."<br>";
			print "sel: "; print_r($this->sel); print "<br>";
			print "subArg: ".$this->subArg."<br>";
            print "capsNode: ".$this->capsNode."<br>";
			print "xmlStr: ".$this->xmlStr."<br>";
			print "xmlStrOneUp: ".$this->xmlStrOneUp."<br>";
			print "xmlStrNoName: ".$this->xmlStrNoName."<br>";
			print "val: ".$this->val."<br>";
			print "actLvl: ".$this->actLvl."<br>";
			print "lang: ".$this->lang."<br>";
            print "isLang: ".$this->isLang."<br>";
            print "isArg: ".$this->isArg."<br>";
			print "actKey: "; print_r( $this->actKey ); print "<br>";
            print "capsKey: "; print_r( $this->capsKey ); print "<br>";
			print "<br>";
		}
	}
?>