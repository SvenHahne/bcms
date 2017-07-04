<?php
	include_once ( 'simpleimage.php' );
	include_once ( 'smart_resize_image.function.php' );
    require_once ( './phplib/cms/BSendMail.php' );
    
	class BEditorPostProc
	{
		public $post;
		public $xt;
		public $uplTypes;
		public $lang;
		
		protected $langPreset;
		protected $search;
		protected $replace;
		protected $keys;
		protected $backup;
        protected $didEdit;
        protected $mysqlH;
        protected $maxWidth = 2800;

		function __construct(&$_post, &$keys, &$xt, &$lang,
                             $_langPreset, $_backup, $_mysqlH)
		{
			$this->post = $_post;
			$this->xt = &$xt;
			$this->lang = &$lang;
			$this->keys = &$keys;
			$this->langPreset = $_langPreset;
            $this->backup = $_backup;
            $this->mysqlH = $_mysqlH;
						
			// arrays for correction of german umlauts
			$this->search  = array ('ä',  'ö',  'ü',  'Ä', 'Ö', 'Ü', 'ß',  'ø', 'é', 'Á', ' ', '–',
							  '“', '”', '/', '(', ')', '&', '%', '!', '"', '"', 'ñ', 'Ñ', 'í',
                              'á', 'Á', 'Í', 'Ó', 'ó', 'É', 'é', ':');
			$this->replace = array ('ae', 'oe', 'ue', 'Ae','Oe','Ue','sz', 'o', 'e', 'a', '',  '-',
							  '"', '"', '',  '',  '',  '',  '',  '', '', '', 'n', 'N', 'i',
                              'a', 'A', 'I', 'O', 'o', 'E', 'e', '');
			$this->updtItems = array();
            $this->didEdit = false;
        }
        
        function proc ()
        {
            // do all the changing work
			foreach ( $this->post as $key => $value )
			{
				$fo = new BFormObj($key, $value, $this->keys, $this->xt, $this->mysqlH);

				if ( $fo->ins == "change" )
				{
					$this->change( $fo, $this->post );
                    $this->didEdit = true;

				} else if ( $fo->ins == "shiftUp" )					
				{
					$res = $this->shiftUp( $fo );
                    $this->didEdit = true;
					break;
				} else if ( $fo->ins == "shiftDown" )
				{                    
					$res = $this->shiftDown( $fo );
                    $this->didEdit = true;
					break;
				} else 
				{
					$this->updtItems[$key] = $value;
                    $this->didEdit = true;
				}
			}
            
			// do the "new entry" work
			foreach ( $this->updtItems as $key => $value )
			{
				$fo = new BFormObj($key, $value, $this->keys, $this->xt, $this->mysqlH);
                
				if ( $fo->ins == "newKeyEntry" )
				{
                    $newKey = $fo->getPostVal($this->post, "newKeySelect");
					if ( $newKey != "" && $newKey != $this->keys['addEntryDropDownFirstItem'][$this->lang] )
                        $this->addNewKey( $fo, $this->post );
                    $this->didEdit = true;
                    
				} else if ( $fo->ins == "newMultKeyEntry" )
				{
					$this->addNewMultKey( $fo, $this->post );
                    $this->didEdit = true;
					
				} else if ( $fo->ins == "newEntry" )
				{
					$newKey = $fo->getPostVal($this->post, "newEntry");
					
					// hier vielleicht problem?
					//if ( $newKey != "" ) 
					$this->addNewHighLvl( $fo, $this->post );
                    $this->didEdit = true;
					
				} else if ( $fo->ins == "remove" )
				{
					$this->remove( $fo );
                    $this->didEdit = true;
					
				} else if ( $fo->ins == "upload" )
				{
					$this->upload( $fo, $this->post );
                    $this->didEdit = true;
				}
			}

            if ($this->didEdit) $this->backup->countEditNr();

			$this->xt->saveDOM();
		}

		
		//---- add new low level key ---------------------------

		function addNewKey ( &$fObj, &$_post )
		{
			$node = $this->xt->xpath->query( $fObj->xmlStr );
            
			// check if low level exists
			if ( $node->length == 0 ) 
			{
				// low level doesn´t exist, create it
				$xStr = $fObj->getXmlOneLevelUp();
				$tNode = $this->xt->xpath->query( $xStr );
				$newL = $this->xt->doc->createElement( "l".$fObj->actLvl );
				$tNode->item(0)->appendChild( $newL );

				// reload the node
				$node = $this->xt->xpath->query( $fObj->xmlStr );
			}
            
			$thisNode = $node->item(0);
            $this->correctKeyType($fObj);

            // create a new element
            $newElem = $this->xt->doc->createElement( $fObj->keyType );

			// if the element has different languages create the subsections
			if ( $fObj->actKey->isLang )
			{
				foreach ( $this->keys['langs'] as $key => $value )
				{
					$newSubElem = $this->xt->doc->createElement( $value );
					$cdata = $this->xt->doc->createCDATASection( "" );
					$newSubElem->appendChild( $cdata );
					$newElem->appendChild( $newSubElem );
				}
			}
            
			// create arguments
			for ( $i=0;$i<sizeof( $fObj->actKey->args );$i++ ) 
			{
				$attr = $this->xt->doc->createAttribute( $fObj->actKey->args[$i] );
				$attr->nodeValue = $fObj->actKey->argsStdVal[$i];
				$newElem->appendChild( $attr );
			}

			// add new element to the top
			$insertBeforeThis = $thisNode->firstChild;
            while ( $insertBeforeThis->nodeType != 1  )
                $insertBeforeThis = $insertBeforeThis->nextSibling;

			$thisNode->insertBefore( $newElem, $insertBeforeThis );
		}

		
		//---- add new multiple entry key ---------------------------

		function addNewMultKey ( &$fObj, &$_post )
		{
			$node = $this->xt->xpath->query( $fObj->xmlStr );
			$thisNode = $node->item(0);

			// check if exists
			if ( $node->length > 0 ) 
			{
				$newElem = $this->xt->doc->createElement( $fObj->actKey->type );

				// create arguments
				for ( $i=0;$i<sizeof( $fObj->actKey->subArgs );$i++ ) 
				{
					$attr = $this->xt->doc->createAttribute( $fObj->actKey->subArgs[$i] );
                    
                    if (!is_array($fObj->actKey->subArgsStdVal[$i]))
                    {
                        $attr->nodeValue = $fObj->actKey->subArgsStdVal[$i];
                    } else {
                        $attr->nodeValue = $fObj->actKey->subArgsStdVal[$i][0];
                    }
					$newElem->appendChild( $attr );
				}

				// add new element to the top
				$insertBeforeThis = $thisNode->firstChild;
				$thisNode->insertBefore( $newElem, $insertBeforeThis );
			}
		}
		
		
		//---- add new high level entry ---------------------------

		function addNewHighLvl ( &$fObj, &$_post )
		{
			$this->correctKeyType($fObj);
			
			$fObj->actLvl++;
			            
			// create a new element
			$newElem = $this->xt->doc->createElement( $fObj->keyType );

			// create subkeys
			for ( $i=0;$i<sizeof( $this->keys['base'.$fObj->actLvl] );$i++ ) 
			{
                $tKey = $this->keys['base'.$fObj->actLvl][$i];
				if ( $tKey->isAttr )
				{
					$sName = explode('@', $tKey->postId );
					$sName = $sName[1];
                    
                    $subElem = $this->xt->doc->createAttribute( $sName );

                    // if there´s a init value for this argument, set it
                    if ( isset($tKey->initVal) )
                        $subElem->nodeValue = $tKey->initVal;

					// if the subelem is a shortname, generate a unique string for it
					if ( $this->keys['base'.$fObj->actLvl][$i]->postId == "@short" )
					{
						$shortNr = 0;
						$shortStr = sprintf('%04d', $shortNr);
						if ( $fObj->xmlStr == "" ) {
							$xStr = $fObj->keyType."[@short='".$shortStr."']";
						} else {
							$xStr = $fObj->xmlStr."/".$fObj->keyType."[@short='".$shortStr."']";
						}
						
						while ( $this->xt->xpath->query( $xStr )->length > 0 )
						{
							$shortNr++;
							$shortStr = sprintf('%04d', $shortNr);
							if ( $fObj->xmlStr == "" ) {
								$xStr = $fObj->keyType."[@short='".$shortStr."']";
							} else {
								$xStr = $fObj->xmlStr."/".$fObj->keyType."[@short='".$shortStr."']";
							}
						}
						
						$subElem->nodeValue = $shortStr;
					}
				} else 
				{
                    $subName = $tKey->xmlName;
					$subElem = $this->xt->doc->createElement( $subName );
                    
					// if the element has different languages create the subsections
					if ( $tKey->isLang )
					{
						foreach ( $this->keys['langs'] as $key => $value )
						{
							$newSubElem = $this->xt->doc->createElement( $value );
                            
                            if ( isset($tKey->initVal) && sizeof($tKey->initVal) > 0 )
                            {
                                $cdata = $this->xt->doc->createCDATASection( $tKey->initVal[$value] );
                            } else {
                                $cdata = $this->xt->doc->createCDATASection( "" );
                            }
							
							$newSubElem->appendChild( $cdata );
							$subElem->appendChild( $newSubElem );
						}
					}
					
					// create arguments of the subkeys
					for ( $j=0;$j<sizeof( $tKey->args );$j++ ) 
					{
						$attr = $this->xt->doc->createAttribute( $tKey->args[$i] );
						$attr->nodeValue = $tKey->args->argsStdVal[$j];
						$subElem->appendChild( $attr );
					}
				}
				$newElem->appendChild( $subElem );
			}
			
			if ( $fObj->xmlStr == "" ) $fObj->xmlStr .= "/data";

			$node = $this->xt->xpath->query( $fObj->xmlStr );
			$thisNode = $node->item(0);
			
			// add new element to the top
			$insertBeforeThis = $thisNode->firstChild;			
			$thisNode->insertBefore( $newElem, $insertBeforeThis );
		}
		
		
		//---- change ---------------------------

		function change( &$fObj, &$_post )
		{
			$node = $this->xt->xpath->query( $fObj->xmlStr );

            if ($node->length > 0)
            {
                $node = $node->item(0);

                // cdata can´t be changed the normal way, because of the create a
                // new cdata section and replace it with the old one
                if ( $fObj->isLang && !$fObj->isArg )
                {
                    $oldElem = $this->xt->xpath->query( $fObj->xmlStr."/".$this->lang );
                    $oldElem = $oldElem->item(0);

                    $newElem = $this->xt->doc->createElement( $this->lang );
                    $cdata = $this->xt->doc->createCDATASection( $this->char_rpl($fObj->val) );
                    $newElem->appendChild( $cdata );

                    $node->replaceChild( $newElem, $oldElem );

                } else
                {
                    // if there can be only one element with this attribute set to true
                    // and the value for this attribute is true, this set all others to zero
                    if ( $fObj->actKey->isAttr && $fObj->actKey->singleSel && $fObj->val == 1)
                    {
                        // look for all other entries in this level
                        $all = $this->xt->xpath->query( "l0/@isstart" );
                        
                        // if it´s not the actual entry, set it to 0
                        for ($i=0;$i<$all->length;$i++)
                            if ($all->item($i)->nodeValue == 1)
                                $all->item($i)->nodeValue = 0;
                    }
                    
                    $node->nodeValue = $this->char_rpl_amp($this->char_rpl($fObj->val));
                }
            } else
            {
                // wenn noch kein argument vorhanden, mach eins
                $str = explode("/", $fObj->xmlStr);
                $isArg = $str[sizeof($str)-1];
                if ($isArg[0] == "@")
                {
                    $parentStr = "";
                    for ($i=0;$i<sizeof($str)-1;$i++)
                    {
                        $parentStr .= $str[$i];
                        if ($i < sizeof($str)-2) $parentStr .= "/";
                    }
                    
                    $arg = substr($isArg, (sizeof($isArg) -2) * -1);
                    $oldElem = $this->xt->xpath->query( $parentStr );
                    $oldElem = $oldElem->item(0);
                    
                    $newElem = $this->xt->doc->createAttribute( $arg );
                    if ($oldElem) $oldElem->appendChild( $newElem );
                    
                    $node = $this->xt->xpath->query( $fObj->xmlStr );
                    $node = $node->item(0);
                    if ($node) $node->nodeValue = $this->char_rpl_amp($this->char_rpl($fObj->val));
                }
            }
		}
		
		
		//---- upload ---------------------------
        // use error_log to debug

		function upload( &$fObj, &$_post )
        {
			$files = array();
			$files['name'] = array();
			$files['tmp_name'] = array();

			// get client from the user level
            $usrFold = "";
            if (isset($GLOBALS["xmlClientLvl"]))
            {
                $str = $fObj->xmlStr;
                $str = explode("/", $str);
                if (sizeof($str) > $str[$GLOBALS["xmlClientLvl"]])
                {
                    $str = $str[$GLOBALS["xmlClientLvl"]];
                    $client = $this->xt->xpath->query( $str."/@client" );
                    $client = $client->item(0)->nodeValue;
                    
                    // get user folder
                    $usrFold = $this->mysqlH->getClientArgByName($client, "folder");
                }
            }
            
            $dstRoot = $fObj->getDstRoot($usrFold);
            
			// wenn es nur eine datei ist, mach ein array, damit die handhabung diesselbe ist
			// wie bei mehreren dateien
			foreach ( $_FILES as $key => $value ) 
			{
				if ( !is_array( $value['name'] )  ) 
				{
					if ( $value['name'] != "" ) 
					{
						array_push( $files['name'], $value['name'] );
						array_push( $files['tmp_name'], $value['tmp_name'] );
					}
				} else 
				{
					foreach( $value['name'] as $name ) if ( $name != "" ) array_push( $files['name'], $name );
					foreach( $value['tmp_name'] as $name ) if ( $name != "" ) array_push( $files['tmp_name'], $name );
				}
			}

			for ($j=0;$j<sizeof( $files['name'] );$j++) 
			{
				$res_pfad_str = $this->getUniqueFileName( $files['name'][$j], $dstRoot );
				$ending = $res_pfad_str[2];
				$pfad_str = $res_pfad_str[1];
				$res_pfad_str = $res_pfad_str[0];
				$thumb_str = $pfad_str;
                
				// check if file already exists
				if ( !file_exists( $dstRoot."/".$res_pfad_str ) ) 
				{
					// if file doesn´t exist, check if folders exist, recursively
					$folders = explode( "/", $dstRoot );					
					$checkFold = "";
					
					for ($i=0;$i<sizeof($folders);$i++)
					{
						$checkFold .= $folders[$i];
						if ( !file_exists( $checkFold ) ) mkdir( $checkFold, 0775 );
						$checkFold .= "/";
					}
				}

				// copy file from temp to destination
				move_uploaded_file( $files['tmp_name'][$j], $dstRoot."/".$res_pfad_str );
                chmod( $dstRoot."/".$res_pfad_str, 0775 );
                
                // if the file is a jpg, gif or png, check the size
                // if larger than 2000 x height downscale it.
                if (strtolower($ending) == "gif" || strtolower($ending) == "jpg" || strtolower($ending) == "png" )
                {
                    list($width, $height, $type, $attr) = getimagesize( $dstRoot."/".$res_pfad_str );
                    
                    // generate downscaled images
                    if ($GLOBALS["dynImagePhp"] == true)
                    {
                        $this->saveAllRes($res_pfad_str, $dstRoot."/", $this->keys['resolutions'] );
                    } else
                    {
                        // if larger than 2800 x height downscale it.
                        if ($width > $this->maxWidth)
                            smart_resize_image($dstRoot."/".$res_pfad_str,
                                               $this->maxWidth,
                                               $height * ($this->maxWidth / $width),
                                               true,
                                               $dstRoot."/".$res_pfad_str,
                                               false);
                    }
                }
                


                // if the uploaded file is a video, create a thumbnail and convert
                if ( array_search($ending, $GLOBALS["ffmpegAllowedFormats"]) !== false
                		&& !file_exists( $dstRoot."/".$thumb_str.".jpg" ) )
                {
                	// generate thumbnail - muss synchron sein
                	exec($GLOBALS["ffmpegPath"]." -i ".$dstRoot."/".$res_pfad_str." -an -ss 00:00:10 -an -r 1 -vframes 1 -y ".$dstRoot."/".$thumb_str.".jpg");
                
                	// generate downscaled images
                	if ($GLOBALS["dynImagePhp"] == true)
                	{
                		$this->saveAllRes($thumb_str.".jpg", $dstRoot."/", $this->keys['resolutions'] );
                	} else
                	{
                		// if larger than 2800 x height downscale it.
                		if ($width > $this->maxWidth)
                			smart_resize_image($dstRoot."/".$thumb_str.".jpg",
                					$this->maxWidth,
                					$height * ($this->maxWidth / $width),
                					true,
                					$dstRoot."/".$thumb_str.".jpg",
                					false);
                	}
                
                	// generate formats, compatible with all standard browsers
                	foreach($GLOBALS["ffmpegDstFormats"] as $key => $val)
                	{
                		if ( $ending != $val && !file_exists($dstRoot."/".$thumb_str.".".$val) )
                		{
                			exec($GLOBALS["ffmpegPath"]." -i ".$dstRoot."/".$res_pfad_str." -threads 4 -ac 2 -ab ".$GLOBALS["ffmpegAudioBitRate"]." -b ".$GLOBALS["ffmpegVideoBitRate"]." ".$dstRoot."/".$thumb_str.".".$val." > /dev/null &");
                		}
                	}
                }
                
                // get the actual node
                $pNode = $this->xt->xpath->query( $fObj->xmlStr );
                
                // reset the optimize flag
                // hier noch macke!!!!!
                $parNode = $pNode;
                if (!$GLOBALS["dynImagePhp"])
                {
                	if ($parNode->length > 0)
                	{
                		$parNode = $parNode->item(0);
                		while (strlen($parNode->tagName) != 2 && $parNode->tagName[0] != "l")
                			$parNode = $parNode->parentNode;
                
                			$parNode->setAttribute("optimized", "0");
                	}
                }
                
                // check for a current entry, if there is one, delete the file and the scaled versions
                if ($pNode->length > 0)
                {
                	$fileN = $pNode->item(0)->nodeValue;
                	if ($fileN != $res_pfad_str)
                	{
                		$this->deleteSizedImages($fObj, $fileN);
                		if( file_exists( $dstRoot.$fileN ) && !is_dir( $dstRoot.$fileN ) )
                			unlink($dstRoot.$fileN);
                	}
                
                	// delete thumb
                	if($fObj->actKey->usesThumbs)
                	{
                		$thumb = $this->xt->xpath->query( $fObj->xmlStr."/@image" );
                		if ($thumb->length > 0)
                		{
                			$thumb = $thumb->item(0)->nodeValue;
                			if( file_exists( $dstRoot.$thumb ) && !is_dir( $dstRoot.$thumb ) )
                				unlink($dstRoot.$thumb);
                		}
                	}
                }
                
                
                // change xml element
                if ( $fObj->actKey->type == "link" )
                {
                	// unterscheide ob ein voransichtsblid oder die datei für den link selbst hochgeladen wurde
                	if ($fObj->isArg)
                	{
                		$pNode = $this->xt->xpath->query( $fObj->xmlStr );
                		$pNode->item(0)->nodeValue = $res_pfad_str;
                	}
                
                	//---- bis hierhin cool
                
                	// schau was fuer eine art von datei es war
                	// wenn pdf kopier das pdflogo.jpg und mach einen eintrag
                	$endingMap = array(array ("pdf", "pic/", "PDF_Logo.jpg"),
                			array ("doc", "pic/", "doc_icon.jpg"));
                
                	foreach ( $endingMap as $key => $ar )
                	{
                		if ( $ending == $ar[0] )
                		{
                			copy( $ar[1].$ar[2], $dstRoot.$ar[2] );
                			$node = $this->xt->xpath->query( $fObj->xmlStr."/@image" );
                			$node = $node->item(0);
                			$node->nodeValue = $ar[2];
                
                			// if it´s a doc or pdf, add the internal path
                			//$res_pfad_str = $dstRoot.$res_pfad_str;
                		}
                	}
                
                
                	// wenn ein jpg hochgeladen wurde mach einen thumbnail und trag den als image ein
                	if (strtolower($ending) == "gif" || strtolower($ending) == "jpg" || strtolower($ending) == "png")
                	{
                		list($width, $height, $type, $attr) = getimagesize($dstRoot."/".$res_pfad_str);
                
                		$dWidth = 400;
                		smart_resize_image($dstRoot."/".$res_pfad_str,
                				$dWidth,
                				$height * ($dWidth / $width),
                				true,
                				$dstRoot."/thmb_".$res_pfad_str,
                				false);
                
                		$node = $this->xt->xpath->query( $fObj->xmlStr."/@image" );
                		$node = $node->item(0);
                		$node->nodeValue = "thmb_".$res_pfad_str;
                
                		// if it´s a picutre, add the internal path
                		//  $res_pfad_str = $dstRoot.$res_pfad_str;
                	}
                
                	$pNode = $this->xt->xpath->query( $fObj->xmlStr );
                	$pNode = $pNode->item(0);
                	$pNode->nodeValue = $res_pfad_str;
                
                } else
                {
                	// set the corresponding xml entry
                	$pNode = $this->xt->xpath->query( $fObj->xmlStr );
                	$pNode = $pNode->item(0);
                	$pNode->nodeValue = $res_pfad_str;
                }
			}			
		}
		
		
		//---- shift up / down ---------------------------
		
		function shiftUp( &$fObj )
		{            
			$xpq = $this->xt->xpath->query( $fObj->xmlStrNoName );					
			$thisNode = $xpq->item(0);
			$insertBeforeThis = $thisNode->previousSibling;
			
            if (isset($insertBeforeThis->tagName) && $insertBeforeThis->tagName == "name")
            {
                $insertBeforeThis = $insertBeforeThis->previousSibling;
            }

            if ( $fObj->keyType == "name")
            {
                while ( (isset($insertBeforeThis->tagName) && !preg_match('/l[0-9]/', $insertBeforeThis->tagName)) || ($insertBeforeThis != "" && $insertBeforeThis->nodeType != 1) || ( isset($insertBeforeThis->tagName) && $insertBeforeThis->tagName == "name") )
                    $insertBeforeThis = $insertBeforeThis->previousSibling;
            } else {
                while ( (isset($insertBeforeThis->tagName) && preg_match('/l[0-9]/', $insertBeforeThis->tagName) ) || ($insertBeforeThis != "" && $insertBeforeThis->nodeType != 1) || ( isset($insertBeforeThis->tagName) && $insertBeforeThis->tagName == "name") )
                    $insertBeforeThis = $insertBeforeThis->previousSibling;
            }
            
			$thisNode->parentNode->insertBefore( $thisNode, $insertBeforeThis );
			
			$this->xt->saveDOM();
		}
		
		
		function shiftDown( &$fObj )
		{            
			$xpq = $this->xt->xpath->query( $fObj->xmlStrNoName );
			$thisNode = $xpq->item(0);

            $next = $thisNode->nextSibling;

            if ($next != "")
                while ( $next->nodeType != 1  )
                    $next = $next->nextSibling;
            
            // ignore no level keys, if key is level
            // and level keys if key is not a level
            if ($next != "")
            {
                if ( $fObj->keyType == "name" )
                {
                    while ( isset($next->tagName) && !preg_match('/l[0-9]/', $next->tagName) || $next->nodeType != 1 )
                        $next = $next->nextSibling;
                } else {
                    while ( ($next != "" && $next->nodeType != 1) || ( isset($next->tagName) && preg_match('/l[0-9]/', $next->tagName) ) || ( isset($next->tagName) && $next->tagName == "name") )
                        $next = $next->nextSibling;
                }
            }
            
			if ( $next != "" && $next->isSameNode( $thisNode->parentNode->lastChild ) )
			{
				$thisNode->parentNode->appendChild( $thisNode );
			} else 
			{
				// if the node will be the last
				if ( $next != "" )
				{
                    $insertBeforeThis = $next->nextSibling;
				} else {
					$insertBeforeThis = $thisNode->parentNode->firstChild;
				}
				$thisNode->parentNode->insertBefore( $thisNode, $insertBeforeThis );
			}
			 			
			$this->xt->saveDOM();
		}

		// ------- remove entry --------------------------------
		
		function remove( &$fObj )
		{
            // if there are files corresponding to the entry, remove it
			if ( $fObj->actKey != "" && $fObj->actKey->isUplType )
			{
				$files = $fObj->getFilePaths();

                foreach ( $files as $key => $value )
				{
					if ( file_exists( $value ) )
					{
                        // if there are thumbnails also delete them
                        if ($fObj->actKey->usesThumbs)
                        {
                            $thumbPath = substr( $value, 0, strrpos($value, "/") )."/thmb_". substr( $value, strrpos($value, "/")+1, strlen($value) );
                            
                            if(file_exists( $thumbPath ))
                               unlink( $thumbPath );
                        }

                        if ($fObj->actKey->uploadType == "image/*")
                        {
                            $this->deleteSizedImages($fObj, $value);
                        }
                        
                        if ($fObj->actKey->uploadType == "video/*" || $fObj->actKey->postId == "playlist")
                        {
                            $dir = dirname($value);
                            $filename = basename($value);
                            $filename = explode(".", $filename)[0];
                            foreach ($GLOBALS["ffmpegDstFormats"] as $k => $val)
                                if(file_exists( $dir."/".$filename.".".$val ))
                                    unlink( $dir."/".$filename.".".$val );

                            if ( file_exists( $dir."/".$filename.".jpg" ) )
                                unlink($dir."/".$filename.".jpg");

                            $prfx = "k";
                            for ($i=0;$i<sizeof($this->keys['resolutions']);$i++)
                            {
                                //$prfx = implode ('', array_fill(0, $i, 'k'));
                                if ( file_exists( $dir."/".$prfx."_".$filename.".jpg" ) )
                                    unlink($dir."/".$prfx."_".$filename.".jpg");
                                
                                $prfx .= "k";
                            }
                        }

                        // diese if ist eigentlich unsinn, wird aber beim loeschen von videos gebraucht
                        if (file_exists( $value ))
                            unlink ( $value );
					}
				}
			}
            
            // spezial fall link, wenn link hol den image-path
            if( $fObj->actKey != "" && $fObj->actKey->type == "link")
            {
                $pNode = $this->xt->xpath->query( $fObj->xmlStr."/@image" );
                if ($pNode->length > 0)
                {
                    if(file_exists( $thumbPath ))
                        unlink( $thumbPath );
                    $this->deleteSizedImages($fObj, $pNode->item(0)->nodeValue);
                }
            }
            
            
			// remove the node
			$xpq = $this->xt->xpath->query( $fObj->xmlStr );
            $thisNode = $xpq->item(0);
            
            if (is_a($thisNode, 'DOMAttr'))
            {
                $thisNode->nodeValue = "";
            } else {
                $thisNode->parentNode->removeChild( $thisNode );
            }
		}

		// ------- utils --------------------------------
        
        // removes also combined accents
		function char_rpl($inString) 
		{
            $search  = array ('\"', 'n&#x303;', 'N&#x303;', 'a&#x301;', 'A&#x301;', 'E&#x301;',
                              'e&#x301;', 'i&#x301;', 'I&#x301;', 'o&#x301;', 'O&#x301;',
                              'u&#x301;', 'U&#x301;');
            $replace = array ('"',  'ñ',        'Ñ',        'á',        'Á',        'É',
                              'é',         'í',       'Í',        'ó',        'Ó',
                              'ú',         'Ú');
            $outString = str_replace( $search, $replace, $inString );

            // remove white space at the end
            while (substr($outString, -1) == " ")
                $outString = substr($outString, 0, sizeof($outString)-2);
            
            return $outString;
		}

        
        // removes also combined accents
        function char_rpl_amp($inString)
        {
            $search  = array ('&');
            $replace = array ('&amp;amp;');
            $outString = str_replace( $search, $replace, $inString );
            return $outString;
        }

		
        // diese funktion wird im prinzip nicht mehr benötigt...
		function deleteSizedImages(&$fObj, &$filePath)
		{
            // get client
            $str = $fObj->xmlStr;
            $str = explode("/", $str);
            $str = $str[0];
            $client = $this->xt->xpath->query( $str."/@client" );
            $client = $client->item(0)->nodeValue;
  
            $usrFold = $this->mysqlH->getClientArgByName($client, "folder");
			$baseP = $fObj->getDstRoot($usrFold);
            
			// get file name
			$fName = explode("/", $filePath);
			$fName = $fName[ sizeof($fName) -1 ];

			if ( file_exists( $baseP."/".$fName ) && !is_dir( $baseP."/".$fName ) )
				unlink($baseP."/".$fName);
			
				// for all sizes, look if the file exists, if yes, delete it
				$prfx = "k";
				for ($i=0;$i<sizeof($this->keys['resolutions']);$i++)
				{
					//$prfx = implode ('', array_fill(0, $i, 'k'));
					if ( file_exists( $baseP."/".$prfx."_".$fName ) )
						unlink($baseP."/".$prfx."_".$fName);
			
						$prfx .= "k";
				}
		}
		
		
		function getUniqueFileName ($inName, $folder) 
		{
			$resString = "";
			
			$expl = explode( ".", $inName );
			$ending = $expl[ sizeof($expl)-1 ];
			if ( $ending == "jpeg" || $ending == "JPG" ) $ending = "jpg";
			
			$pfad_str = "";
			for ($k=0;$k<(sizeof($expl)-1);$k++) {
				$pfad_str .= $expl[$k];
			}
			$pfad_str = str_replace($this->search, $this->replace, $pfad_str);
			$pfad_str = substr( $pfad_str, 0, 25 );
			
			$resString = $pfad_str.".".$ending;
			
			if ( file_exists( $folder."/".$resString ) ) 
			{
				$fCnt = 0;			
				while ( file_exists( $folder."/".$pfad_str.$fCnt.".".$ending ) ) {
					$fCnt++;
				}
				$resString = $pfad_str.$fCnt.".".$ending;
			}
			
			return array($resString, $pfad_str, $ending);
		}
		
		
		function saveAllRes($filename, $folder, $sizeMap)
		{
			// get the filesize of the new
			list($width, $height, $type, $attr) = getimagesize( $folder.$filename );
		
			$prefix = "k";
		
			// speichere die verschiedenen groessen
			for ($i=0;$i<sizeof($sizeMap);$i++)
			{
				$newWidth = $width;
				if($newWidth > $sizeMap[$i] * $GLOBALS["resizeImgFact"])
					$newWidth = $sizeMap[$i] * $GLOBALS["resizeImgFact"];
		
					smart_resize_image($folder.$filename,                           // file
							$newWidth,                                   // width
							$newWidth / $width * $height,                // height
							true,                                        // proportional
							$folder.$prefix."_".$filename,    // output
							false);                                      // use_linux_commands
		
							chmod( $folder.$prefix."_".$filename, 0775 );
							$prefix .= "k";
			}
		}
		
	
        function correctKeyType(&$fObj)
		{
			if ( $fObj->actLvl > -1 )
			{
				for ($j=0;$j<sizeof($this->keys[ $fObj->actLvl ]);$j++)
				{
					if ( $this->keys[ $fObj->actLvl ][$j]->htmlName[$this->langPreset] == $fObj->val )
					{
						$fObj->actKey = $this->keys[ $fObj->actLvl ][$j];
						$fObj->keyType = $fObj->actKey->xmlName;
					}
				}
			}
		}
	}
?>