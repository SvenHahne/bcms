<?php    
    $usrs = array();
    foreach ($this->mysqlH->getClients() as $val) array_push($usrs, $val['name']);
    
	$keys = array();
	$keys['langs'] = array("es");
    $keys['resolutions'] = array(2000, 1500, 1000, 768, 400);
    $keys['addEntryDropDownFirstItem']['de'] = "---Auswahl----";
    $keys['addEntryDropDownFirstItem']['en'] = "----Select----";
    $keys['addEntryDropDownFirstItem']['es'] = "--Selecciona--";
    
    $keys['gridStyles'] = array("default", "grid_3_3_3_2_1_h0");

    
	// enter the Keys for each level
	// each level can contain entries with whatever keys and has a basic keys
	// which are used for menues, etc. these are stored in the $keys['base'.$n] array
	// for every $i level

    // top level, user entries only
    $keys[0] = array();
    $keys['base0'] = array(new KAClient("client", array("Klient", "Cliente", "cClient"), $usrs, $GLOBALS["pubClientName"]),
                           new KAGenSelect("submenu", array("Sub-Menu", "Submenú", "Sub-menu"), array("none", "sub"), "none")
                           );
    
    $keys[1] = array(new KContactForm(),
                     new KImage(
                                array("useGrid", "style", "gridStyle", "fill", "text", "subtext", "link"),
                                array("KEButton", "KEDropDown", "KEDropDown", "KEButton", "", "", ""),
                                array("1", 
                                	  array("fullWidth", "fixedHeight", "fullHeight"), 
                                	  array("col1", "col1_row1", "col1_row2", "col2", "col3_row033"), 
									  0, 
									  "", 
									  "",
									  "" )
                                ),
                     new KSepLine(array("style"),
                                  array("KEDropDown"),
                                  array( array("norm") )
                                  ),
                     new KLink(array("image", "text", "subtext", "target", "style", "gridStyle"),
                               array("", "", "", "KEDropDown", "KEDropDown", "KEDropDown"),
                               array("", "", "", array("new", "self"), array("gridImg"), array("col4_row1", "col1_row1"))
                               ),
                     new KReflist(array("head-es", "head-en", "style", "max", "gridStyle"),
                                  array("", "", "KEDropDown", "KEDropDown", "KEDropDown"),
                                  array("", "", array("block", "list", "circle"), array("3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "20", "25", "30", "40", "50"),
                                        array("col1_row033", "col1_row050", "col1_row1", "col2_row1", "col2_row2", "col1_row2")),
                                  array("type", "refmode"),
                                  array("KEDropDown", "KEDropDown"),
                                  array(array("col1_row033", "col1_row050", "col1", "col1_050", "col1_row1", "col2_row1", "col2_row2", "col1_row2"),
                                        array("image", "same", "sameOne", "sub", "subOne", "subRef", "subRefOne", "levelRef", "levelRefOne"))),
                     new KProjectInfo(),
                     new KRssfeed(array("style", "gridStyle"),
                                  array("KEDropDown", "KEDropDown"),
                                  array( array("norm_10col"), array("col10_row1") )),
                     new KSlideshow(array("style", "start", "gridStyle"),
                                    array("KEDropDown", "KEDropDown", "KEDropDown"),
                                    array(array("overl", "full_row", "arrows_outside", "flotante"), array("auto", "manual"), array("col1")),
                                    array("ref", "refmode", "refkey","name", "place"),
                                    array("KECascadingMenu", "KEDropDown", "KEDropDown", "", ""),
                                    array("", array("sub", "subRef", "levelRef"), array("KSlideshow"), "", "")
                                    ),
                     new KText(array("useGrid", "style", "gridStyle"),
                               array("KEButton", "KEDropDown", "KEDropDown"),
                               array("1",
                            		 array("nText"),
                                     array("col1", "col1_row2", "col1_row1", "col2", "col2_row1", "col3", "col3_row1", "col4_row1", "col5"))),
                     new KHeadFreePos(array("style"), array("KEDropDown"), array(array("navi")) )
                     );
	$keys['base1'] = array(
                           new KAHide(),
                           new KAStart(),
                           new KAImage(),
                           new KAGenSelect("layout", array("Layout", "Layout", "Layout"), $keys['gridStyles'], $keys['gridStyles'][0]),
                           new KAGenText("paddingH", array("PaddingH", "PaddingH", "PaddingH"), "2"),
                           new KAGenText("paddingV", array("PaddingV", "PaddingV", "PaddingV"), "0"),
                           new KASubMenu(),
                           new KAGenSelect("submenustyle", array("Submenu-Style", "Estilo de submenú", "Submenu-Style"), array("std", "anim"), "std"),
                           new KARefEntry()
                           );

	$keys[2] = array(
                     new KImage(array("style", "gridStyle", "fill", "text", "subtext", "link"),
                                array("KEDropDown", "KEDropDown", "KEButton", "", "", ""),
                                array(array("rotate", "fullWidth", "fixedHeight"),
                                      array("col1", "col1_row1", "col1_row150", "col1_row2", "col1_row3", "col2_row1"),
                                      0, "", "", "" )),
                     new KText(array("style", "gridStyle"),
                               array("KEDropDown", "KEDropDown"),
                               array(array("nText"),
                                     array("col1", "col1_row2", "col2_row1", "col1_row1", "col1_row050", "col1_row2", "col2", "col2_row1", "col2_row050", "col3", "col3_row1", "col3_row2", "col4_row1", "col4_row2", "col10_row1"))),
                     new KGallery(array("style"), array("KEDropDown"), array(array("full_row", "half_row"))),
                     new KLink(array("image", "text", "subtext", "target", "style", "gridStyle"),
                               array("", "", "", "KEDropDown", "KEDropDown", "KEDropDown"),
                               array("", "", "", array("new", "self"), array("linkThumbStd", "full_width_left", "gridImg"), array("col4_row1", "col1_row1"))),
                     new KContactForm(),
                     new KHead(array("style", "gridStyle"), array("KEDropDown", "KEDropDown"), array(array("fromAttr"), array("col1_1_2_2_2", "col1", "col1_row1", "col2", "col2_row1"))),
                    );
	$keys['base2'] = array(new KAStart(),
						   new KAImage(),
                           new KAGenSelect("layout", array("Layout", "Layout", "Layout"), $keys['gridStyles'], $keys['gridStyles'][0]),
                           new KAGenText("paddingH", array("PaddingH", "PaddingH", "PaddingH"), "2"),
                           new KAGenText("paddingV", array("PaddingV", "PaddingV", "PaddingV"), "0"),
                           new KASubMenu(),
                           new KAGenSelect("submenustyle", array("Submenu-Style", "Estilo de submenú", "Submenu-Style"), array("std", "anim"), "std"),
                           new KARefEntry()
                           );
	
    $keys[3] = array(
                     new KImage(array("style", "text"), array("KEDropDown", ""), array(array("halfPic"), "")),
					 new KLink(),
                     new KHead(array("style"), array("KEDropDown"), array(array("fromAttr"))),
                     new KText(array("style"), array("KEDropDown"), array(array("nText"))),
                     new KSlideshow(array("style", "start"),
                                    array("KEDropDown", "KEDropDown"),
                                    array(array("overl", "full_row", "arrows_outside", "flotante"), array("auto", "manual")),
                                    array("name", "place"),
                                    array("", ""),
                                    array("", "")
                                    ),
                     new KReflist(array("head-es", "head-en", "style", "max"),
                                  array("", "", "KEDropDown", "KEDropDown"),
                                  array("", "", array("block", "list"), array("3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "20", "25", "30", "40", "50") ),
                                  array("type", "refmode"),
                                  array("KEDropDown", "KEDropDown"),
                                  array(array("col1_row033", "col1_row050", "col1", "col1_row1", "col2_row1", "col2_row2", "col1_row2"),
                                        array("keywords", "same", "sub", "levelRef"))),
                     new KSepLine(array("style"),
                                  array("KEDropDown"),
                                  array( array("norm") )),
					 new KYoutube(),
                     new KProjectInfo(),
                    new KClientUpload()
                     );
	$keys['base3'] = array(
                           new KAGenText("keywords", array("keywords", "keywords", "keywords")),
                           new KAGenSelect("layout", array("Layout", "Layout", "Layout"), array("info"), "info"),
                           new KAGenText("paddingH", array("PaddingH", "PaddingH", "PaddingH"), "2"),
                           new KAGenText("paddingV", array("PaddingV", "PaddingV", "PaddingV"), "0"),
                           new KASubMenu()
                           );
?>