<?php
	
	$keys['nrLevels'] = 0;
	
	foreach ($keys as $key => $value ) 
		if ( is_numeric($key) ) $keys['nrLevels']++;

	// add the level object to all higher levels
	for ($i=0;$i<($keys['nrLevels']-1);$i++)
		array_push( $keys[$i], new KLevel() );
	
	// fundamental keys
	for ($i=0;$i<$keys['nrLevels'];$i++)
	{
		if ( !isset($keys['base'.$i]) ) $keys['base'.$i] = array();
        array_unshift($keys['base'.$i], new KAGenButton("search", array("suche", "búsqueda", "search")));
		array_unshift($keys['base'.$i], new KAShort());
		array_unshift($keys['base'.$i], new KName());
	}
	
	$keys['textKeys'] = array(new KText(), new KHead());

	for ($i=0;$i<sizeof($keys['textKeys']);$i++)
		$keys['textKeys'][$i] = $keys['textKeys'][$i]->xmlName;
	
	// style mapping für die auswahllisten beim editor			
	// auswahlliste "class" für die link pics
	$keys['dropDownMap'] = array();
	$keys['dropDownMap']['imgclass'] = array("picIcon");
	$keys['dropDownMap']['divclass'] = array("links");
	$keys['dropDownMap']['target'] = array("_self", "_new");
	$keys['dropDownMap']['target'] = array("_self", "_new");
	
?>