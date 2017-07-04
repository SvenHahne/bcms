<?php
	
	include("password_protect.php");
	include("smart_resize_image.function.php");
	
	error_reporting(E_ALL); 
	ini_set('display_errors', TRUE); 
	
	date_default_timezone_set('GMT');	
	
	if ( isset($_GET['scroll']) ) { 
		$scroll = $_GET['scroll']; 
	} else {
		$scroll = 0;
	}
		
	$formName = "rssform";
	
	$headKeys = array(
					  array("title",		"text",		80, 200,	"",		array(), ""),
					  array("description",	"textarea", 58, 3,		"",		array(), ""),
					  array("link",			"text",		80, 200,	"",		array(), ""),
					  array("language",		"text",		80, 200,	"de-de",array(), ""),
					  array("copyright",	"text",		80, 200,	"de-de",array(), ""),
					  array("pubDate",		"text",		80, 200,	"",		array(), ""),
					  array("image",		"image",	80, 200,	"",		array("url", "title", "link", "width", "height"), "pic")
					  );
	
	$headKeyNameIndexMap = array();
	for ($i=0;$i<sizeof($headKeys);$i++)
	$headKeyNameIndexMap[ $headKeys[$i][0] ] = $i;
	
	$itemKeys = array(
					  array("title",		"text",		80, 200,	"",		array(), ""),
					  array("description",	"textarea", 58, 3,		"",		array(), ""),
					  array("link",			"text",		80, 200,	"",		array(), ""),
					  array("author",		"text",		80, 200,	"",		array(), ""),
					  array("pubDate",		"text",		80, 200,	"",		array(), ""),
					  array("guid",			"text",		80, 200,	"",		array(), ""),
					  array("image",		"image",	80, 200,	"",		array("url", "type", "fileSize", "medium", "width", "height"), "pic")
					  );
	
	$itemKeyNameIndexMap = array();
	for ($i=0;$i<sizeof($itemKeys);$i++)
	$itemKeyNameIndexMap[ $itemKeys[$i][0] ] = $i;
	
	function curPageURL() {
		$pageURL = 'http';
		//if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		
		return $pageURL;
	}
	
	$pageURL = curPageURL();
	$urlPath = explode( "/", $pageURL );
	$resurl = "";
	
	for ($i=0;$i<sizeof($urlPath)-1;$i++ ) {
		$resurl .= $urlPath[$i]."/";
	}
	$urlPath = $resurl;
	
	$rssurl = "";

	if ( isset( $_POST['url'] ) )
		$rssurl = $_POST['url'];

	if ( isset( $_GET['url'] ) )
		$rssurl = $_GET['url'];
	
	function saveRss( $xml, $path ) {
		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML( $xml->asXML() );
		$dom->save( $path );
	}
	
	function saveDom( $doc, $path ) {
		$doc->save( $path );
	}
	
	if ( $rssurl != "" ) { 
				
		$path = $rssurl;
		
		$xmlFile = file_get_contents( $path );
		$xml = new SimpleXMLElement( $xmlFile );
//		$xml = new SimpleXMLElement( $xmlFile, NULL, FALSE, "http://www.w3.org/2005/Atom" );
		
		$doc = new DomDocument;
		$doc->preserveWhiteSpace = FALSE;
		$doc->load($path);
		$doc->formatOutput = TRUE;
		$xpath = new DOMXPath($doc);
		
		
		if ( !isset( $_GET['changeurl'] ) ) 
		{
			
			if ( isset($_POST['subm_newentry']) ) 
			{
				$items = $xpath->query("//rss/channel[1]/item");
				$parent = $items->item(0);
				$newItem = $doc->createElement("item");
				$newItemNr = $items->length +1;
				
				foreach ( $itemKeys as $key => $val ) 
				{
					if ( $val[0] != "image" ) 
					{
						$newElem = $doc->createElement($val[0]);
						
						if ( $val[0] == "guid" ) {
							$newElem->nodeValue = "article ".floor($newItemNr / 10).($newItemNr % 10)." at www.zeitkunst.eu";
							$newAttrib = $doc->createAttribute("isPermaLink");
							$newAttrib->nodeValue = "false";
							$newElem->appendChild( $newAttrib );
						} elseif ( $val[0] == "pubDate" ) {
							$newElem->nodeValue = date("D, j M Y G:i:s e");
						}
						
						$newItem->appendChild( $newElem );
					} 
				}
				
				$parent->parentNode->insertBefore( $newItem, $parent );
				
				saveDom( $doc, $path, $xml );
				
				$xmlFile = file_get_contents( $path );
				$xml = new SimpleXMLElement( $xmlFile );
			}
			
						
			if ( isset( $_POST['delete'] ) ) 
			{
				foreach ( $_POST as $key => $value ) 
				{					
					$keyar = explode( '_', $key );
					
					if ( $keyar[0] == 'check' ) {
						unset( $xml->channel[0]->item[ intval($keyar[1]) ] );
					}
				}			
				saveRss( $xml, $path );
				
				
				$xmlFile = file_get_contents( $path );
				$xml = new SimpleXMLElement( $xmlFile );
			}
			
			if ( isset($_POST['change']) ) 
			{			
				foreach ( $_POST as $key => $value ) 
				{
					$keyar = explode( '_', $key );

					// wenn head einträge
					if ( $keyar[0] == 'head' ) {
						if ( $keyar[1] == 'link' ) {
							$xml->channel[0]->link[0] = $value;
						} elseif ( sizeof( $keyar ) == 3 ) {
							$xml->channel[0]->$keyar[1]->$keyar[2] = $value;
						} else {
							$xml->channel[0]->$keyar[1] = $value;
						}
					}
					
					if ( $keyar[0] == 'item' ) {
						if ( sizeof( $keyar ) == 4 ) {
							// lade die änderungen in das dom doc
							$doc->loadXML( $xml->asXML() );
							$xpath = new DOMXPath($doc);

							// ändere im dom doc
							$med = $xpath->query("//rss/channel[1]/item[".(intval($keyar[1])+1)."]/media:content/@".$keyar[3] );
							$med->item(0)->nodeValue = $value;
							
							// lade die änderungen in das $xml file
							$xml = simplexml_import_dom( $doc );						

						} else { 
							$xml->channel[0]->item[ intval( $keyar[1] ) ]->$keyar[2] = $value;
						}
					}
				}
				saveRss( $xml, $path );
			}
			
			foreach ( $_POST as $key => $value ) 
			{
				$keyar = explode( '_', $key );
				
				//---- shift up eintrags inhalt ---------------------------
				
				if ( $keyar[0] == "shiftUp" )
				{
					$xpq = $xpath->query( "//rss/channel[1]/item[".($keyar[1]+1)."]" );		
					
					if ( $keyar[1] != 0 ) 
					{
						$xpq->item(0)->parentNode->insertBefore( $xpq->item(0), $xpq->item(0)->previousSibling );
						
						saveDom( $doc, $path, $xml );
						$xmlFile = file_get_contents( $path );
						$xml = new SimpleXMLElement( $xmlFile );
					}
				}
				
				//---- shift down eintrags inhalt ---------------------------
				
				if ( $keyar[0] == "shiftDown" )
				{
					$xpq = $xpath->query( "//rss/channel[1]/item[".($keyar[1]+1)."]" );
					
					if ( $keyar[1] != $xpath->query( "//rss/channel[1]/item")->length - 1 ) 
					{					
						// such das nächste node
						
						// wenn das node ans ende soll
						if ( $keyar[1] == ( $xpath->query( "//rss/channel[1]/item")->length - 1 ) ) {
							
							print_r( $xpq );
							$xpath->query( "//rss/channel[1]")->item(0)->appendChild( $xpq );
							
						} else {
							$insertBeforeThis = $xpath->query( "//rss/channel[1]/item[".($keyar[1]+3)."]" )->item(0);
							$xpq->item(0)->parentNode->insertBefore( $xpq->item(0), $insertBeforeThis );
						}
						
						saveDom( $doc, $path, $xml );
						$xmlFile = file_get_contents( $path );
						$xml = new SimpleXMLElement( $xmlFile );

					}
				}

				
				//---- image remove ---------------------------
				
				if ( $keyar[0] == "imageremove" && sizeof($keyar) > 2 ) {
					$node = $xpath->query("//rss/channel[1]/item[".(intval($keyar[2])+1)."]");
					$med = $xpath->query("//rss/channel[1]/item[".(intval($keyar[2])+1)."]/media:content");
					
					if ( $med->length > 0 ) {
						$url = $xpath->query("//rss/channel[1]/item[".(intval($keyar[2])+1)."]/media:content/@url");
						$url = $url->item(0)->nodeValue;
						$url = explode( $resurl, $url );
						$url = $url[1];
						if ( file_exists($url) ) unlink ( $url );

						$node->item(0)->removeChild( $med->item(0) );
					}
					
					saveDom( $doc, $path, $xml );				
					$xmlFile = file_get_contents( $path );
					$xml = new SimpleXMLElement( $xmlFile );
				}

				
				//---- image upload -----------------------------------
				
				if ( $keyar[0] == "change" && sizeof($keyar) > 1 ) 
				{				
					$rssUrl = $_POST['url'];
					
					if ( $keyar[2] == 'head' ) {
						$urlToSave = $headKeys[ $headKeyNameIndexMap['image'] ][6];
					} else {
						$urlToSave = $itemKeys[ $itemKeyNameIndexMap['image'] ][6];
					}
					
					// wenn verzeichnis nicht vorhanden, erstelle es
					if ( !file_exists( $urlToSave ) )
						if ( opendir( $urlToSave ) == FALSE )
							mkdir( $urlToSave, 0755 );
					
					$fileInd = $keyar[2]."_".$keyar[3]."_".$keyar[1];

					$ending = explode(".", $_FILES[ $fileInd ]['name']);
					$ending = $ending[1];
					
					move_uploaded_file($_FILES[ $fileInd ]['tmp_name'], $urlToSave."/".$_FILES[ $fileInd ]['name'] );
					chmod( $urlToSave."/".$_FILES[ $fileInd ]['name'], 0755 );
					
					list($width, $height, $type, $attr) = getimagesize( $urlToSave."/".$_FILES[ $fileInd ]['name'] );
					
					$fileName = $urlToSave."/".$_FILES[ $fileInd ]['name'];
					
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
						if ( $original_aspect < $thumb_aspect ) {
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
						
						/*
						smart_resize_image( $urlToSave."/".$_FILES[ $fileInd ]['name'], 
										   100, 100 / $width * $height, true,
										   $urlToSave."/".$_FILES[ $fileInd ]['name'],
										   true
										   );
						 */
					}
					
					if ( $value == 'head' ) {
						foreach( $headKeys[ $headKeyNameIndexMap['image'] ][5] as $ind => $arg )
							$xml->channel[0]->image->$arg = "";
						
						$xml->channel[0]->image->url = $urlPath.$urlToSave."/".$_FILES['image']['name'];
						$xml->channel[0]->image->width = $width;
						$xml->channel[0]->image->height = $height;
						// image link must be channel link
						$xml->channel[0]->image->link = $xml->channel[0]->link;
						// image title must be channel title
						$xml->channel[0]->image->link = $xml->channel[0]->title;
					} else {
						$node = $xpath->query("//rss/channel[1]/item[".(intval($keyar[3])+1)."]");
						$med = $xpath->query("//rss/channel[1]/item[".(intval($keyar[3])+1)."]/media:content");

						if ( $med->length > 0 ) $node->item(0)->removeChild( $med->item(0) );
											
						$vals = array();
						$vals['url'] = $urlPath.$urlToSave."/".$_FILES[ $fileInd ]['name'];
						$vals['type'] = "image/".strtolower($ending);
						// clear cache
						clearstatcache();					
						$vals['fileSize'] = filesize( $fileName );
						$vals['medium'] = "image"; 
						$vals['width'] = $new_width; 
						$vals['height'] = $new_height;

						$element = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:content', '');

						foreach( $itemKeys[ $itemKeyNameIndexMap['image'] ][5] as $ind => $arg ) {
							$attr = $doc->createAttribute( $arg );
							$attr->nodeValue = $vals[ $arg ];
							$element->appendChild( $attr );
						}
						
						$node->item(0)->appendChild( $element );
						saveDom( $doc, $path, $xml );
						
						$xmlFile = file_get_contents( $path );
						$xml = new SimpleXMLElement( $xmlFile );
					}
				}
				saveRss( $xml, $path );
			}
		}
	}	
	
	$totWidth = 810;
	$leftCol = 150;
	
?>

<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel='stylesheet' type='text/css' href='style/ed_styles.css'>
<link href="style/ed_styles.css" rel="stylesheet">
</head>

<body style="" onload="document.body.scrollTop = <?php print $scroll; ?>;">
<div class="outerCnt">
<div>
<h1>Rss Editor</h1>

<form name="selUrl" class="main" action="rsseditor.php?changeurl" method="post" enctype="multipart/form-data" accept-charset="utf-8">
	<div class='label'>URL:</div>
	<div class='picAndForm'>
		<select name="url" >
		<?php
			if ( $handle = opendir('.') )
			{
				while ( false !== ( $file = readdir($handle) ) ) 
				{
					$pi = pathinfo( $file );
					if ( isset($pi['extension']) && $pi['extension'] == "rss") {
						print "<option";
						if ( $rssurl != "" && $pi['basename'] == $rssurl )
							print " selected";
						print ">".$pi['basename']."</option>";
					}
				}		
				closedir($handle);
			}
			?>
		</select>
		<input type='submit' name='newurl' value='&uuml;bernehmen'>
	</div>
</form>


<form name="<?php print $formName ?>" class="main" onsubmit="document.forms[1].action += ('&scroll='+document.body.scrollTop);" action="rsseditor.php?url=<?php print $rssurl ?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<div class="rightCol" style="width:<?php print $totWidth-$leftCol; ?>px;left:<?php print $leftCol ?>px;">

<div class="submButs">
<input type="submit" name="change" value="&Auml;nderungen &uuml;bernehmen">
<input type="submit" name="subm_newentry" value="Neuen Eintrag hinzuf&uuml;gen">
<input type="submit" name="delete" value="Ausgew&auml;hlte l&ouml;schen">
</div>

<?php
	
	print "<br>";
	
	function drawEntries ( $xml, $items, $dataKeys, $keyIndexMap, $isItem ) 
	{
		$i = 0;
		
		foreach( $items as $key => $value ) 
		{
			$cs = $i % 2;
			
			$prfx = "head";
			if ( $isItem == true ) 
			{
				$prfx = "item_".$i;
				print "<div class='checkBox'>";
				print "<input type='checkbox' name='check_".$i."' value='0'>";
				print "</div>\n";
				
				print "<div class='input".$cs."'>";
			}
			
			foreach( $dataKeys as $headKeyInd => $itemName ) 
			{
				print "<div class='label'>".$itemName[0].":</div>";
				print "<div class='picAndForm'>";
				
				if ( $itemName[1] == "text" ) { 
					print "<input class='inp' name='".$prfx."_".$itemName[0]."' type='text' size='".$itemName[2]."' maxlength='".$itemName[3]."' value='".$value->$itemName[0]."' >";
				} elseif( $itemName[1] == "textarea" ) {
					print "<textarea class='inp' name='".$prfx."_".$itemName[0]."' cols='".$itemName[2]."' rows='".$itemName[3]."'>".$value->$itemName[0]."</textarea>";
					print "<input type='submit' name='change' value='&uuml;bernehmen'>";
				} elseif( $itemName[1] == "image" ) {
					print "<input value='".$itemName[1]." hochladen' type='file' name='".$prfx."_".$itemName[0]."' size='20' accept='image/*'>";
					print "<input type='submit' name=\"change_".$itemName[1]."_".$prfx."\" value='hochladen'>";
					
					if ( $isItem == true ) 
					{
						$item = $xml->xpath("/rss/channel/item[".($i+1)."]");
						if ( sizeof( $item[0]->children('media', true)->content ) > 0 ) {
							$image = $item[0]->children('media', true)->content->attributes();
						} else { 
							$image = NULL;
						}
					} else {
						$image = $xml->xpath("/rss/channel/image");
					}
										
					if ( gettype( $image ) != NULL && sizeof($image) != 0 ) 
					{
						print "<br>";
						print "<img src='";
						if ( $isItem == true ) { print $image['url']; } else { print $image[0]->url; }
						print "' style='width:110px;float:left;margin-right:8px'>";
						
						print "<div style='width:300px;overflow:hidden;'>";
						foreach ( $dataKeys[ $keyIndexMap['image'] ][5] as $key => $value ) {
							print "<div style='width:50px;display:inline;float:left;'>".$value.": </div>";
							print "<div><input class='inp' name='".$prfx."_image_".$value."' type='text' size='40' maxlength='200' value='";
							if ( $isItem == true ) { print $image[$value]; } else { print $image[0]->$value; }
							print "' ></div>";
						}
						print "<div style='width:50px;display:inline;float:left;'>";
						print "<input style='clear:both;' type='submit' name=\"imageremove_item_".$i."\" value='entfernen'>";
						print "</div>";

						print "</div>";
					}
				}
				
				print "</div>";
			}
			
			if ( $isItem == true ) 	{
				// button zum hoch und runter schieben
				print "<div style='width:60px;margin-top:-20px;margin-left:560px;'>";
				print "<input style='' type='submit' name=\"shiftUp_".$i."\" value='^'>";
				print "<input style='' type='submit' name=\"shiftDown_".$i."\" value='v'>";
				print "</div>";
				
				print "</div>\n";
			}

			$i++;
		}
	}
	
	
	if ( $rssurl != "" ) 
	{ 
		$items = $xml->xpath("/rss/channel");
		drawEntries( $xml, $items, $headKeys, $headKeyNameIndexMap, false );
		
		print "<br>";
		print "<br>";
		
		$items = $xml->xpath("/rss/channel/item");
		drawEntries( $xml, $items, $itemKeys, $itemKeyNameIndexMap, true );
	}
	?>
</div>
</form>
</div>
</div>
</body>

</html>