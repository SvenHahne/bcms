<?php

	class XMLTools {
		
		public $path;
		public $doc;
		public $xpath;
		public $xmlFile;
		public $xml;
		public $xpRootPath;
		
		function __construct( $_path )
		{
			$this->path = $_path;
			$this->doc = new DomDocument;
			$this->doc->preserveWhiteSpace = FALSE;
			$this->doc->load($this->path);
			$this->doc->formatOutput = TRUE;
			$this->xpath = new DOMXPath($this->doc);
			
			$this->xmlFile = file_get_contents($this->path);
			$this->xml = simplexml_load_string($this->xmlFile, 'SimpleXMLElement', LIBXML_NOCDATA);

			$this->xpRootPath = "";
			for ($i=0;$i<(strlen($this->path)-4);$i++) 
			{
				$this->xpRootPath = $this->xpRootPath.$this->path[$i];
			}
		}
		
		
	//-------- add a key to a specific level or overwrite it -----------------------------------------------
		
		function addKeyEntry($xp, $keyName, $keyValue, $argNameAr, $argStdValueAr, $lvlPos, $id) 
		{
			$xpStr = "";

			for ($i=0;$i<sizeof($lvlPos);$i++)
			{
				if ( $lvlPos[$i] != "" ) 
				{
					$xpStr .= "l".$i;
					if ( is_string($lvlPos[$i]) ) 
					{
						$xpStr .= "[@short='".$lvlPos[$i]."']/";
					} else {
						$xpStr .= "[".$lvlPos[$i]."]/";
					}
				}
			}
			
			$xpStr .= $keyName;
			
			$tNode = $this->xml->xpath( $xpStr );
			
			$makeLangNodes = false;
			
			if ( in_array( $keyName, $textKeys ) ) $makeLangNodes = true;
			
			// array_shift killt sein argument, deshalb zweifacher aufruf
			$tNodeCont = array_shift( $this->xml->xpath( $xpStr ) );
			
			$tNode = $xp->xpath->query( $xpStr );
			$parent = $tNode->item(0);	
			$this->domAddEntry( $xp->doc, $parent, $keyName, $argNameAr, $argStdValueAr, FALSE, $keyValue );
			$this->saveDOM();
		}
		
		
//------------ setters ------------------------------------------------
		
		function createSubNode ( $parent_node, $new_node ) 
		{
			$node = $this->xml->$parent_node->addChild($new_node);
			$node->addChild('cont', '');
		}
		
		
		function domAddEntry( $doc, $rootNode, $name, $attrAr, $attrValAr, $createCDATA, $cont )
		{
			$newEntr = $doc->createElement($name);
			$rootNode->appendChild( $newEntr );	
			
			if ( sizeof( $attrAr ) > 0 )
			{
				for ( $i=0;$i<sizeof( $attrAr );$i++ )
				{
					$idArg = $doc->createAttribute( $attrAr[$i] );
					$idArg->nodeValue = $attrValAr[$i];
					$newEntr->appendChild( $idArg );
				}
			}

			if ( $createCDATA == TRUE )
			{
				$cdata = $doc->createCDATASection($cont);
				$newEntr->appendChild( $cdata );
			} else {
				$data = $doc->createTextNode($cont);
				$newEntr->appendChild( $data );
			}

			return $newEntr;
		}
        
        
        function createBaseEntries($rootName)
        {
            $el = $this->doc->createElement($rootName);
            $this->doc->appendChild($el);
        }
        

	//------------ save functions ------------------------------------------------

		function save ()
		{
			$dom = new DOMDocument('1.0');
			$dom->preserveWhiteSpace = false;
			//$dom->loadXML( $this->xml->asXML() );
            $dom->formatOutput = TRUE;
            
            $dom = dom_import_simplexml($this->xml)->ownerDocument;

			$dom->saveXML();
			$dom->save( $this->path );
            
			//$this->xml->asXML( $this->path );
		}
		
		
		function saveDOM ()
		{
            $this->doc->formatOutput = true;
			$this->doc->save($this->path);
		}
		
        
        function reload()
        {
            $this->doc = new DOMDocument('1.0');
			$this->doc->load($this->path);
            $this->doc->preserveWhiteSpace = false;
			$this->doc->formatOutput = true;
			$this->xpath = new DOMXPath($this->doc);
            
			$this->xmlFile = file_get_contents($this->path);
			$this->xml = simplexml_load_string( $this->xmlFile, 'SimpleXMLElement' );
        }
	}	
?>