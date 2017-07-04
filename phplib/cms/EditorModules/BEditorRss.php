<?php

    class BEditorRss extends BEditorModule
    {
        protected $headKeys;
        protected $itemKeys;
        protected $headKeyNameIndexMap;
        protected $itemKeyNameIndexMap;
        protected $rssurl;
        protected $xt = null;
        protected $siteName;
        protected $root;
        protected $picPath = "rss-feeds/pic";
        protected $rssDir = "rss-feeds";
        
		function __construct(&$xt, $lang, $mysqlH)
        {
	        $this->siteName = $GLOBALS["siteName"];
	        $this->root = "http://www.".$GLOBALS["siteName"];
            $this->moduleName = "rss";
            $this->formName = "rssform";
            $this->lang = $lang[0];
            $this->langPreset = $lang[1];
            $this->xt = $xt;
            $this->mysqlH = $mysqlH;

           
            $this->headKeys = array(
                              array("title",		"text",		80, 200,	"",		array(), true),
                              array("description",	"textarea", 58, 3,		"",		array(), true),
                              array("link",			"text",		80, 200,	$this->root,  array(), true),
                              array("language",		"text",		80, 200,	"es-es",array(), true),
                              array("copyright",	"text",		80, 200,	$GLOBALS["siteName"], array(), true),
                              array("pubDate",		"text",		80, 200,	date('D, d M Y H:i:s O'),array(), false),
                              array("image",		"image",	80, 200,	"",		array("url", "title", "link", "width", "height"), array(true, true, true, false, false))
                              );
            
            $this->headKeyNameIndexMap = array();
            for ($i=0;$i<sizeof($this->headKeys);$i++)
                $this->headKeyNameIndexMap[ $this->headKeys[$i][0] ] = $i;
            
            $this->itemKeys = array(
                              array("title",		"text",		80, 200,	"",		array(), true),
                              array("description",	"textarea", 58, 3,		"",		array(), true),
                              array("link",			"text",		80, 200,	$this->root,  array(), true),
                              array("author",		"text",		80, 200,	"news@".$GLOBALS["siteName"], array(), false),
                              array("pubDate",		"text",		80, 200,	date('D, d M Y H:i:s O'),array(), true),
                              array("guid",			"text",		80, 200,	"",		array(), false),
                              array("image",		"image",	80, 200,	"",		array("url", "type", "fileSize", "medium", "width", "height"), array(true, false, false, false, true, true))
                              );
            
            $this->itemKeyNameIndexMap = array();
            for ($i=0;$i<sizeof($this->itemKeys);$i++)
                $this->itemKeyNameIndexMap[ $this->itemKeys[$i][0] ] = $i;
            
            if ( isset($_POST['url']) ) $this->rssurl = $_POST['url'];
            if ( isset($_GET['url']) ) $this->rssurl = $_GET['url'];
            
            // when no file specified, try to get the first file from the rss directory
            if ( $this->rssurl == "" )
            {
                $h = opendir('./'.$this->rssDir); //Open the current directory
            	while (false !== ($entry = readdir($h))) {
                    if($entry != '.' && $entry != '..' && !is_dir($entry)) {
                        $this->rssurl = $entry;
                        break;
                    }
                }
            }
            
            $this->init();
        }
  
        function init()
        {
            if ( $this->rssurl != "" )
                $this->xt = new XMLTools($this->rssDir."/".$this->rssurl);
        }

        
		function addHeadEditor(&$cont)
		{
			if ( !$GLOBALS["filedrag"] )
			{
				$cont['footer'] .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>
				';
				$GLOBALS["filedrag"] = true;
			}
		}
		
        
		function printForm(&$cont, &$keys, &$sel, $scroll)
        {
            $link = 'editor.php?mod=rss&lang='.$this->lang.'&changeurl';
            
            // select formular
            $cont['body'] .= '<div class="rightCol">
            <form name="selUrl" class="main" action="'.$link.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">
            ';
            
            $cont['body'] .= '<h2>Rss Editor</h2>';
                        
            $cont['body'] .= '<div class="highL highLvl0">';
            
            $cont['body'] .= '<input type="submit" name="newurl " value="" style="position:absolute;visibility:hidden;">';

            // show available streams
            $cont['body'] .= '<div class="label">Rss-Streams:</div>';
            $cont['body'] .= '<div class="picAndForm fullrow"><select name="url" onchange="this.form.submit()">';
			if ( $handle = opendir('./rss-feeds') )
			{
				while ( false !== ( $file = readdir($handle) ) )
				{
					$pi = pathinfo( $file );
					if ( isset($pi['extension']) && $pi['extension'] == "rss") {
                        $cont['body'] .=  "<option";
						if ( $this->rssurl != "" && $pi['basename'] == $this->rssurl )
							 $cont['body'] .=  " selected";
                        $cont['body'] .=  ">".$pi['basename']."</option>";
					}
				}
				closedir($handle);
			}
            $cont['body'] .= '</select>&nbsp;<input type="submit" name="delstr" value="delete stream"></div>';
            
            // new stream
            $cont['body'] .= '<div class="label">Add new stream:</div>';
            $cont['body'] .= '<div class="picAndForm fullrow"><input type="text" name="newstrname" value=""><input type="submit" class="hook but shiftUp2" name="subm" value="">&nbsp;&nbsp;(ending ".rss" is added automtically)</div>';

            $cont['body'] .= '</div>';
            $cont['body'] .= '</form>';
            
            
            // content form
            $cont['body'] .= '<form name="'.$this->formName.'" class="main" onsubmit="document.forms[1].action+=(\'&scroll=\'+document.body.scrollTop);" action="editor.php?mod=rss&lang='.$this->lang.'&url='.$this->rssurl.'" method="post" enctype="multipart/form-data" accept-charset="utf-8">';

            $cont['body'] .= '<input type="submit" name="change" value="" style="position:absolute;visibility:hidden;">';

            if ($this->xt != NUll)
            {
                $items = $this->xt->xml->xpath("/rss/channel");
                $this->drawEntries( $cont['body'], $items, $this->headKeys, $this->headKeyNameIndexMap, false );
            }
            
            $cont['body'] .=  '<input type="submit" name="subm_newentry" value="" class="but plus">';

            if ($this->xt != NUll)
            {
                $items = $this->xt->xml->xpath("/rss/channel/item");
                $this->drawEntries( $cont['body'], $items, $this->itemKeys, $this->itemKeyNameIndexMap, true );
            }
            
            $cont['body'] .= '</form></div>';
        }
        
        
        function drawEntries ( &$body, &$items, &$dataKeys, &$keyIndexMap, $isItem )
        {
            $i = 0;
            
            foreach( $items as $key => $value )
            {
                $cs = $i % 2;
                
                $prfx = "head";
                if ( $isItem == true )
                {
                    $prfx = "item_".$i;
                    $body .= "<div class='highL highLvl".$cs."'>";
                } else {
                    $body .= "<div class='highL highLvl2'>";
                }
                
                foreach( $dataKeys as $headKeyInd => $itemName )
                {
                    if ($itemName[6])
                    {
                        $body .= "<div class='label'>".$itemName[0].":</div>";
                        $body .= "<div class='picAndForm fullrow'>";
			
                        if ( $itemName[1] == "text" )
                        {
		   	    			$sel = $itemName[0];
                            $body .= "<input class='inp' name='".$prfx."_".$itemName[0]."' type='text' size='".$itemName[2]."' maxlength='".$itemName[3]."' value='".$value->$sel."' >";
                            
                        } elseif( $itemName[1] == "textarea" )
                        {
			    			$sel = $itemName[0];
                            $body .= "<textarea class='inp' name='".$prfx."_".$itemName[0]."' cols='".$itemName[2]."' rows='".$itemName[3]."'>".$value->$sel."</textarea>";
                            $body .= "<input type='submit' class='hook but shiftUp2' name='change' value=''>";
                            
                        } elseif( $itemName[1] == "image" )
                        {
                            $body .=  "<input value='".$itemName[1]." hochladen' class='fileselect' type='file' maxlength='40000000' name='".$prfx."_upl".$itemName[0]."' size='1' accept='image/*' multiple=''>";
                            
                            $body .= "<input class='trash but shiftUp2' type='submit' name=\"imageremove_item_".$i."\" value=''>";

                            if ( $isItem == true )
                            {
                                $item = $this->xt->xml->xpath("channel/item[".($i+1)."]");
                                if ( sizeof( $item[0]->children('media', true)->content ) > 0 )
                                {
                                    $image = $item[0]->children('media', true)->content->attributes();
                                } else {
                                    $image = NULL;
                                }
                            } else {
                                $image = $this->xt->xml->xpath("channel/image");
                            }
                            
                            if ( gettype( $image ) != NULL && sizeof($image) != 0 )
                            {
                                $body .= "<br>";
                                $body .= "<div class='dtable'>";
                                $body .= "<div class='dtdcenter'>";

                                $body .= "<img src='";
                                if ( $isItem == true ) { $img = $image['url']; } else { $img = $image[0]->url; }
                                $body .= $img."' class='prev' ";
								if ($img == "") $body .= "style='height:65px;'";
								$body .= ">";
                                
                                $body .= "</div>";

                                $body .= "<div class='dtdtop'>";
                                
                                $body .= "<div class='dtable'>";
                                $cn = 0;
                                foreach ( $dataKeys[ $keyIndexMap['image'] ][5] as $key => $value )
                                {
                                    if ($dataKeys[ $keyIndexMap['image'] ][6][$cn])
                                    {
                                        $body .= "<div class='dtr'><div class='dtd'>".$value.": </div>";
                                        $body .= "<div class='dtdcenter'><input class='inp_arg' name='".$prfx."_image_".$value."' type='text' size='40' maxlength='200' value='";
                                        if ( $isItem == true ) { $body .= $image[$value]; } else { $body .= $image[0]->$value; }
                                        $body .= "' ></div></div>";
                                    }
                                    $cn++;
                                }
                                $body .= "</div>";

                                $body .= "</div>";
                                $body .= "</div>";
                            }
                        }
                        
                        $body .= "</div>";
                    }
                }
                
                if ( $isItem == true )
                {
                    // button zum hoch und runter schieben
                    $body .= "<input class='up but' type='submit' name=\"shiftUp_".$i."\" value=''>";
                    $body .= "<input class='down but' type='submit' name=\"shiftDown_".$i."\" value=''>";
                    $body .= "<input class='trash but' type='submit' name='delete' value='".$i."'>";

                    $body .= "</div>\n";
                }
                
                $i++;
            }
        }
        
        
        function getItems()
        {
            $retItems = array();
            
            if ($this->xt != NULL)
            {
                $items = $this->xt->xml->xpath("/rss/channel/item");
            
                $i = 0;
                
                foreach( $items as $key => $value )
                {
                    array_push($retItems, array());
                    
                    foreach( $this->itemKeys as $headKeyInd => $itemName )
                    {
                        if ( $itemName[0] != "image" )
                        {
			    			$sel = $itemName[0];
                            $retItems[$i][$itemName[0]] = $value->$sel;

                        } else
                        {
                            $retItems[$i][$itemName[0]] = array();

                            $item = $this->xt->xml->xpath("channel/item[".($i+1)."]");
                            if ( sizeof( $item[0]->children('media', true)->content ) > 0 )
                            {
                                $image = $item[0]->children('media', true)->content->attributes();
                            } else {
                                $image = NULL;
                            }

                            foreach( $this->itemKeys[6][5] as $argKey => $arg )
                                $retItems[$i][$itemName[0]][$arg] = $image[$arg];
                        }
                    }
                    $i++;
                }
            }
            
            return $retItems;
        }
        
        
        function postProc(&$post, &$keys, $lang, &$backup)
        {
            if ( isset($post['newstrname']) && $post['newstrname'] != "" )
            {
                // write empty file
				$this->rssurl = $post['newstrname'].".rss"; 
                $file = "rss-feeds/".$post['newstrname'].".rss";

                $fh = fopen($file, 'w') or die("can't open file");
                fwrite($fh, "");
                fclose($fh);
                chmod($file, 0775);
                
                $xt = new XMLTools($file);
                $this->createRssHead($xt, $post['newstrname']);

                // reload the file
                $this->xt = new XMLTools($file);
            }
			
			if ( isset($post['delstr']) )
            {
				// delete the stream
				unlink("rss-feeds/".$post['url']);
				
				// get the next stream
				$h = opendir('./rss-feeds'); //Open the current directory
				while (false !== ($entry = readdir($h))) {
					if($entry != '.' && $entry != '..' && $entry != '.DS_Store') {
						$this->rssurl = $entry;
						break;
					}
				}
				
				// reload the file
                $this->xt = new XMLTools("rss-feeds/".$this->rssurl);
            }
            
            if ( !isset( $_GET['changeurl'] ) )
            {
                if ( isset($_POST['subm_newentry']) )
                {
                    $items = $this->xt->xpath->query("channel/item");                    
                    $parent = $items->item(0);
                    $newItem = $this->xt->doc->createElement("item");
                    $newItemNr = $items->length +1;
                    
                    for( $i=0;$i<sizeof($this->itemKeys);$i++)
                    {
                        if ( $this->itemKeys[$i][0] != "image" )
                        {
                            $newElem = $this->xt->doc->createElement($this->itemKeys[$i][0], $this->itemKeys[$i][4]);
                            
                            if ( $this->itemKeys[$i][0] == "guid" )
                            {
                                $newElem->nodeValue = "article ".floor($newItemNr / 10).($newItemNr % 10)." at ".$this->headKeys[2][4];
                                $newAttrib = $this->xt->doc->createAttribute("isPermaLink");
                                $newAttrib->nodeValue = "false";
                                $newElem->appendChild( $newAttrib );
                            }
                            
                            $newItem->appendChild( $newElem );
                        }
                    }

                    if ($parent)
                    {
                        $parent->parentNode->insertBefore( $newItem, $parent );
                    } else {
                        $parent = $this->xt->xpath->query("channel");
                        if ($parent->length > 0)
                        {
                            $parent = $parent->item(0);
                            // if there is no entry don´t insert before but append
                            $parent->appendChild( $newItem );
                        }
                    }
                }

				
                if ( isset($_POST['delete']) )
                {
                    $thisNode = $this->xt->xpath->query("channel/item[".(intval($_POST['delete']) +1)."]");
                    $thisNode = $thisNode->item(0);
                    $thisNode->parentNode->removeChild( $thisNode );
                }
                
				
                if ( isset($_POST['change']) )
                {
                    foreach ( $_POST as $key => $value )
                    {
                        $keyar = explode( '_', $key );
                        
                        // wenn head einträge
                        if ( $keyar[0] == 'head' )
                        {
                            if ( $keyar[1] == 'link' )
                            {
                                $q = $this->xt->xpath->query("channel/link[1]");
                                $q->item(0)->nodeValue = $value;
                            } else if ( sizeof( $keyar ) == 3 )
                            {
                                $q = $this->xt->xpath->query("channel/".$keyar[1]."/".$keyar[2]);
                                $q->item(0)->nodeValue = $value;
                            } else
                            {
                                $q = $this->xt->xpath->query("channel/".$keyar[1]);
                                $q->item(0)->nodeValue = $value;
                            }
                        } else if ( $keyar[0] == 'item' )
                        {
                            if ( sizeof( $keyar ) == 4 )
                            {
                                // ändere im dom doc
                                $med = $this->xt->xpath->query("channel/item[".(intval($keyar[1])+1)."]/media:content/@".$keyar[3] );
                                $med->item(0)->nodeValue = $value;
                                
                            } else {
                                $q = $this->xt->xpath->query("channel/item[".(intval($keyar[1]) +1)."]/".$keyar[2]);

                                if ($keyar[2] == "link")
                                {
                                    $q->item(0)->nodeValue = str_replace( "&", "&amp;", $value );
                                } else {
                                    $q->item(0)->nodeValue = $value;
                                }
                            }
                        }
                    }
                }
                

                foreach ( $_POST as $key => $value )
                {
                    $keyar = explode( '_', $key );
                    
                    //---- shift up eintrags inhalt ---------------------------
                    
                    if ( $keyar[0] == "shiftUp" )
                    {
                        $xpq = $this->xt->xpath->query( "channel/item[".($keyar[1]+1)."]" );
                        
                        if ( $keyar[1] != 0 )
                            $xpq->item(0)->parentNode->insertBefore( $xpq->item(0), $xpq->item(0)->previousSibling );

						$this->reNumber($this->xt);
                    }
                    
                    //---- shift down eintrags inhalt ---------------------------
                    
                    if ( $keyar[0] == "shiftDown" )
                    {
                        $xpq = $this->xt->xpath->query( "channel/item[".($keyar[1]+1)."]" );
                        
                        if ( $keyar[1] != $this->xt->xpath->query( "channel/item")->length - 1 )
                        {
                            // wenn das node ans ende soll
                            if ( $keyar[1] == ( $this->xt->xpath->query( "channel/item")->length - 1 ) )
                            {
                                $this->xt->xpath->query( "channel")->item(0)->appendChild( $xpq );
                                
                            } else {
                                $insertBeforeThis = $this->xt->xpath->query( "channel[1]/item[".($keyar[1]+3)."]" )->item(0);
                                $xpq->item(0)->parentNode->insertBefore( $xpq->item(0), $insertBeforeThis );
                            }
                        }
						
						$this->reNumber($this->xt);
                    }

                    
                    //---- image remove ---------------------------
                    
                    if ( $keyar[0] == "imageremove" && sizeof($keyar) > 2 )
                    {
                        $node = $this->xt->xpath->query("channel/item[".(intval($keyar[2])+1)."]");
                        $med = $this->xt->xpath->query("channel/item[".(intval($keyar[2])+1)."]/media:content");
                        
                        if ( $med->length > 0 )
                        {
                            $url = $this->xt->xpath->query("channel/item[".(intval($keyar[2])+1)."]/media:content/@url");
                            $url = $url->item(0)->nodeValue;
							if ( strpos($url, "http") !== FALSE)
								$url = preg_replace('/[a-z][\w-]+:\/{1,3}www\d{0,3}[.][a-zA-Z0-9]+.[a-z]{3,4}\//', '', $url);
                            if ( file_exists($url) ) unlink ( $url );
                            
                            $node->item(0)->removeChild( $med->item(0) );
                        }                        
                    }
                    
                    
                    //---- image upload -----------------------------------
    
                    if ( (sizeof($keyar) > 1 && $keyar[1] == "uplimage") || (sizeof($keyar) > 2 && $keyar[2] == "uplimage") )
                    {                        
                        //$urlToSave = $root."/".$this->picPath;
                        $urlToSave = $this->picPath;

                        // wenn verzeichnis nicht vorhanden, erstelle es
                        if ( !file_exists( $urlToSave ) )
                            if ( opendir( $urlToSave ) == FALSE )
                                mkdir( $urlToSave, 0774 );
                                                
                        $ending = explode(".", $_FILES[ $key ]['name']);
                        $ending = $ending[1];
                        
                        move_uploaded_file($_FILES[$key]['tmp_name'], $urlToSave."/".$_FILES[$key]['name'] );
                        chmod( $urlToSave."/".$_FILES[$key]['name'], 0774 );
                        
                        list($width, $height, $type, $attr) = getimagesize( $urlToSave."/".$_FILES[$key]['name'] );
                        
                        $fileName = $urlToSave."/".$_FILES[$key]['name'];
                        
                        $thumb_width = 200;
                        $thumb_height = 135;
                        $new_width = $width;
                        $new_height = $height;
                        $yDiff = 0;

                        if ( $width > 200 || $height > 135 )
                        {
                            if ( $ending == "JPG" || $ending == "jpg" || $ending == "jpeg" ) {
                                $image = imagecreatefromjpeg( $fileName );
                            } elseif ( $ending == "gif" ) {
                                $image = imagecreatefromgif( $fileName );
                            } elseif ( $ending == "png" ) {
                                $image = imagecreatefrompng( $fileName );
                            }
                            
                            $original_aspect = $width / $height;
                            $thumb_aspect = $thumb_width / $thumb_height;
                            
                            // zieh das bild auf die standard breite
                            $new_width = $thumb_width;
                            $new_height = floor( $height * ($new_width / $width) );
                            
                            // wenn das bild höher als der thumbnail aspect ist
                            // beschneide das bild
                            if ( $original_aspect < $thumb_aspect )
                            {
                                $yDiff = $new_height - $thumb_height;
                                $new_height = $thumb_height;
                            }
                            
                            $thumb = imagecreatetruecolor( $thumb_width, $new_height );
                            
                            // Resize and crop
                            imagecopyresampled($thumb,
                                               $image,
                                               0, // Center the image horizontally
                                               $yDiff * -0.5, // Center the image vertically
                                               0, 0,
                                               $thumb_width, 
                                               floor( $height * ($new_width / $width) ),
                                               $width, $height);
                            
                            if ( $ending == "jpg" || $ending == "jpeg" || $ending == "JPG") {
                                imagejpeg($thumb, $fileName, 80);
                            } elseif ( $ending == "gif" ) {
                                imagegif($thumb, $fileName, 80);
                            } elseif ( $ending == "png" ) {
                                imagepng($thumb, $fileName, 80);
                            }
                        }
                        
                        
                        if ( $keyar[0] == 'head' )
                        {
                            foreach( $this->headKeys[ $this->headKeyNameIndexMap['image'] ][5] as $ind => $arg )
                            {
                                $q = $this->xt->xpath->query("channel/image/@".$arg);
                                $q->item(0)->nodeValue = "";
                            }

                            $q = $this->xt->xpath->query("channel/image/url");
                            $q->item(0)->nodeValue = $fileName;
                            
                            $q = $this->xt->xpath->query("channel/image/width");
                            $q->item(0)->nodeValue = $width;
                            
                            $q = $this->xt->xpath->query("channel/image/height");
                            $q->item(0)->nodeValue = $height;
                            
                            $q = $this->xt->xpath->query("channel/image/link");
                            $li = $this->xt->xpath->query("channel/link");
                            // image link must be channel link
                            $q->item(0)->nodeValue = $li->item(0)->nodeValue;
                            
                            // image title must be channel title
                            $q = $this->xt->xpath->query("channel/image/title");
                            $li = $this->xt->xpath->query("channel/title");
                            $q->item(0)->nodeValue = $li->item(0)->nodeValue;

                        } else
                        {
                            $node = $this->xt->xpath->query("channel/item[".(intval($keyar[1])+1)."]");
                            $med = $this->xt->xpath->query("channel/item[".(intval($keyar[1])+1)."]/media:content");
                            
                            if ( $med->length > 0 ) $node->item(0)->removeChild( $med->item(0) );
                            
                            $vals = array();
                            $vals['url'] = $fileName;
                            $vals['type'] = "image/".strtolower($ending);
                            // clear cache
                            clearstatcache();					
                            $vals['fileSize'] = filesize( $fileName );
                            $vals['medium'] = "image"; 
                            $vals['width'] = $new_width; 
                            $vals['height'] = $new_height;
                            
                            $element = $this->xt->doc->createElementNS('http://search.yahoo.com/mrss/', 'media:content', '');
                            
                            foreach( $this->itemKeys[ $this->itemKeyNameIndexMap['image'] ][5] as $ind => $arg )
                            {
                                $attr = $this->xt->doc->createAttribute( $arg );
                                $attr->nodeValue = $vals[ $arg ];
                                $element->appendChild( $attr );
                            }
                            
                            $node->item(0)->appendChild( $element );
                        }
                    }
                }
                
                $this->xt->saveDOM();

                // reload
                $this->xt = new XMLTools("rss-feeds/".$this->rssurl);
            }
        }
        
		
        function reNumber(&$xt)
		{
			$q = $this->xt->xpath->query( "channel/item" );
			for($i=0;$i<$q->length;$i++)
			{
				$guid = $q->item($i)->getElementsByTagName("guid");
				$guid->item(0)->nodeValue = "article ".($q->length -$i -1)." at ".$this->siteName;
			}
		}
		
		
        function createRssHead(&$xt, $fileName)
        {
            $rss = $xt->doc->createElement("rss");
            
            $attr = $xt->doc->createAttribute("xmlns:atom");
            $attr->value = "http://www.w3.org/2005/Atom";
            $rss->appendChild($attr);
            
            $attr = $xt->doc->createAttribute("xmlns:media");
            $attr->value = "http://search.yahoo.com/mrss/";
            $rss->appendChild($attr);

            $attr = $xt->doc->createAttribute("version");
            $attr->value = "2.0";
            $rss->appendChild($attr);
                        
            $chan = $xt->doc->createElement("channel");

            // atom link
            $aLink = $xt->doc->createElement("atom:link");
            
            $attr = $xt->doc->createAttribute("href");
            $attr->value = "http://www.".$GLOBALS["siteName"]."/rss-feeds/".$fileName;
            $aLink->appendChild($attr);

            $attr = $xt->doc->createAttribute("rel");
            $attr->value = "self";
            $aLink->appendChild($attr);

            $attr = $xt->doc->createAttribute("type");
            $attr->value = "application/rss+xml";
            $aLink->appendChild($attr);
            
            $chan->appendChild($aLink);
            
            // head
            for($i=0;$i<sizeof($this->headKeys);$i++)
            {
                if ( $this->headKeys[$i][0] == "image" )
                {
                    $el = $xt->doc->createElement($this->headKeys[$i][0]);
                    $subEl = $xt->doc->createElement("url");
                    $el->appendChild($subEl);
                    $subEl = $xt->doc->createElement("title");
                    $el->appendChild($subEl);
                    $subEl = $xt->doc->createElement("link", $this->headKeys[2][4]);
                    $el->appendChild($subEl);
                    
                } else
                {
                    $el = $xt->doc->createElement($this->headKeys[$i][0], $this->headKeys[$i][4]);
                }
                
                $chan->appendChild($el);
            }
            
            $rss->appendChild( $chan );
            $xt->doc->appendChild( $rss );
            $xt->saveDom();
        }
        
        
        function setRssUrl($url)
        {
            $this->rssurl = $url;
            $this->init();
        }
    }
?>
