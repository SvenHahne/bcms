<?php
    
    // global variables
    $GLOBALS["backupCount"] = -1;
    $GLOBALS["kLevelStd"] = false;
    $GLOBALS["klm"] = false;
    $GLOBALS["rewriteLinks"] = true;
    $GLOBALS["rewriteLinksExplAttr"] = "short";
    $GLOBALS["rewriteLinksStepToContent"] = true;  // if we use rewriteLinks, define wether the interpretation 
    $GLOBALS["siteName"] = "testsite.cl";
    												// of this link to the data hirarchie should always lead to an entry with content or not
    $GLOBALS["slideShowCntr"] = 0;
    $GLOBALS["tableEditWin"] = false;
    $GLOBALS["pubClientName"] = "public";
    
    // ffmpeg
    $GLOBALS["ffmpegPath"] = "/usr/local/bin/ffmpeg";
    $GLOBALS["ffmpegDstFormats"] = ["m4v", "ogv", "webm"];
    $GLOBALS["ffmpegAllowedFormats"] = ["m4v", "avi", "flv", "mov", "mpg", "mp4", "ogv", "webm", "mp3"];
    $GLOBALS["ffmpegAudioBitRate"] = "128000";
    $GLOBALS["ffmpegVideoBitRate"] = "400k";
    
    // xml
    $GLOBALS["searchRestrict"] = 1; // search only in the same l0 and l1 as the search request
    $GLOBALS["xmlClientLvl"] = 0;
    $GLOBALS["xmlPubClientShort"] = "0000"; // comment out for switching off
    
    // jquery, key specific javascripts should be loaded only once
    $GLOBALS["cleditor"] = false;
    $GLOBALS["clientUpload"] = false;
    $GLOBALS["colText"] = false;
    $GLOBALS["dropDownMen"] = false;
    $GLOBALS["dropDownMenCntr"] = 0;
    $GLOBALS["dynImagePhp"] = true;
    $GLOBALS["filedrag"] = false;
    $GLOBALS["gallery"] = false;
    $GLOBALS["image"] = false;
    $GLOBALS["rssImage"] = false;
    $GLOBALS["imgLiquid"] = false;
    $GLOBALS["isotopeJInitSet"] = false;
    $GLOBALS["jplayerCounter"] = 0;
	$GLOBALS["jKeyframes"] = false;
    $GLOBALS["masonry"] = false;
    $GLOBALS["masonryJInitSet"] = false;
    $GLOBALS["packeryJInitSet"] = false;
    $GLOBALS["packery"] = false;
    $GLOBALS["responsveSlides"] = false;
    $GLOBALS["setLangGet"] = false;
    $GLOBALS["youTubePopUp"] = false;
    $GLOBALS["resizeImgFact"] = 0.7; // in prozent relativ zur maximalen gesamtbreite des gerätes
    
    // file system
    $GLOBALS["backupUrl"] = "";
    $GLOBALS["clientBaseUrl"] = "clients/";
    $GLOBALS["root"] = "";
    
    // email
    $GLOBALS["smtpHost"] = "smtp.".$GLOBALS["siteName"];
    $GLOBALS["smtpPort"] = "25";
    $GLOBALS["smtpUser"] = "info@".$GLOBALS["siteName"];
    $GLOBALS["smtpPass"] = "";
    $GLOBALS["notifyIntrv"] = 1; // in minutes
?>