<?php	
	ini_set('display_errors', TRUE);
	error_reporting(E_ALL);
	//error_reporting(E_ERROR | E_PARSE);

    ini_set("log_errors", 1);
    ini_set("error_log", "/tmp/php-error.log");

    date_default_timezone_set('Europe/Berlin');
    mb_internal_encoding("UTF-8");
    header('Content-Type: text/html; charset=utf-8' );
    
    $phplib_path = "phplib/";

    include_once("password_protect.php");
    include_once($phplib_path."cms/BMysqlHandler.php");
    include_once($phplib_path."cms/BCMSEditor.php");

    $mysqlH = new BMysqlHandler('localhost', 'test_bcms', 'test_bcms', 'aGjhgvckHJJK');
    $bEditor = new BCMSEditor("db/data.xml", "config.php", "es", "style/ed_styles.css", $mysqlH);
?>
