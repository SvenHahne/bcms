<?php
    include_once('Isotope.php');	// must be loaded first, since the other inherent from this!!!
    include_once('JPlayer.php');	// must be loaded first, since the other inherent from this!!!
    include_once('Masonry.php');	// must be loaded first, since the other inherent from this!!!
    include_once('Packery.php');	// must be loaded first, since the other inherent from this!!!
	include_once('keydef/KeyDef.php');	// must be loaded first, since the other inherent from this!!!

    $folders = array('keydef',
                     'keydef/Attributes',
                     'keydef/EditorDrawFunc',
                     'keydef/Layouts',
                     'keydef/LevelMenues',
                     'keydef/DrawFunc');
        
    foreach($folders as $fold)
    {
        $h = opendir('./phplib/cms/'.$fold); //Open the current directory
        while (false !== ($entry = readdir($h)))
            if ($entry != '.' && $entry != '..' && $entry != '.DS_Store')
                if (!is_dir('./phplib/cms/'.$fold.'/'.$entry) && $entry != "KeyDef.php")
                    include_once($fold.'/'.$entry);
    }
    
?>