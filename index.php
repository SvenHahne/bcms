<?php
	error_reporting(E_ALL & ~E_STRICT); 
	ini_set('display_errors', TRUE);
//    ini_set("log_errors", 1);
//    ini_set("error_log", "/tmp/php-error.log");
	date_default_timezone_set('Chile/Continental');
    mb_internal_encoding("UTF-8");
    header('Content-Type: text/html; charset=utf-8' );
    header('Access-Control-Allow-Origin: *');
    
	$rootPath = "";
	$phplib_path = "phplib/";
    
    // css preprocessing
    require_once $phplib_path."crush/CssCrush.php";
    include_once($phplib_path."cms/BMysqlHandler.php");
	include_once($phplib_path."cms/BCMSDisplay.php");
    
    $mysqlH = new BMysqlHandler('localhost', 'test_bcms', 'test_bcms', 'aGjhgvckHJJK');
    $bDisplay = new BCMSDisplay("db/data.xml", "config.php", "BCMS", "es", $mysqlH);    
    $bDisplay->init();
?>


<!DOCTYPE html>
<html lang="<?php print $bDisplay->lang ?>">
<head>
<!--  
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="pragma" content="no-cache" />
-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!-- mobile viewport optimisation -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <base href="<?php if($_SERVER['HTTP_HOST'] == 'localhost') { print 'http://localhost/bcms/'; } else { print $GLOBALS['root']; }?>">

	<script type="text/javascript" src="<?php print $GLOBALS['root']?>js/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="<?php print $GLOBALS['root']?>js/optiImg.js"></script>

    <link href="<?php print $GLOBALS['root']?>style/fonts.css" rel="stylesheet" type="text/css">
    <?php
        print $bDisplay->head['base'];
        csscrush_set('options', array('plugins' => array('loop', 'ie-inline-block', 'ie-opacity')));
        print csscrush_inline('style/crush_style.css', array('formatter' => 'single-line'));
    ?>
    <script src="<?php print $GLOBALS['root']?>js/utils.js" type="text/javascript"></script>
	<script type="text/javascript">
        <?php print $bDisplay->head['jqGlobVar'] ?>
        $(document).ready(function(){ <?php print $bDisplay->head['jqDocReady'] ?> });
        $(window).load(function(){ <?php print $bDisplay->head['jqWinLoad'] ?> });
        $(window).resize(function(){ <?php print $bDisplay->head['jqWinResize'] ?> });
        $(window).scroll(function(){ <?php print $bDisplay->head['jqWinScroll'] ?> });
    </script>
</head>
<body ontouchstart="">
	<div class="mainTable">
		<div class="mainCenter">
			<div
				class="headerWhiteBackFull<?php if(sizeof($bDisplay->subNavList)==2) print ' headerSmallHl';?>">
				<div class="header">
					<div class="headTable">
						<div class="headLogo headCell">
							<a href="<?php print rewriteLink($GLOBALS['root'].'index.php?lang='.$bDisplay->lang, $bDisplay->xmlt, $bDisplay->keys);?>" target="_self">
  								<img class="headLogo" src="pic/logo.png" data-src="pic/logo.png">
  							</a>
						</div> 						
						<div class="headCell">
  							<div class="headNav">
								<ul class="nav">
								<?php
								if (!$bDisplay->loginHandler->requestLogin) 
								{
									for($i=0; $i < sizeof ( $bDisplay->nav ['names'] ); $i++) {
									
										print '<li class="nav">';
										print '<a class="nav paddL';
										
										//if ($i != sizeof ( $bDisplay->nav ['names'] ) - 1) print ' paddR';
										if ($bDisplay->actL0 == $bDisplay->nav ['names'] [$i]) print ' inBlue';
											
										// subtract the &sel from the link
										$link = explode ( "&sel=", $bDisplay->nav ['link'] [$i] );
										print '" href="' . rewriteLink ( $link [0] . '&lang=' . $bDisplay->lang, $bDisplay->xmlt, $bDisplay->keys ) . '">';
										print $bDisplay->nav['names'][$i];
										print '</a>';
										
										print '</li>';
									}
								}
								?>
								</ul>
								<?php $bDisplay->printSubMenu(); ?>
							</div>
						</div>
								<!--
						<div class="headLang headCell">
							<div class="changeLang">		
		                    <?php	
			                    // language fields                       		
		                    	print '<button onclick="location.href=\''.$bDisplay->getUrl('de').'\'" class="optBut';
			                    if ($bDisplay->lang == 'es') print ' blueBack hoverBlack'; else print ' grayBack hoverBlack';
			                    print '">es</button>
			                    
			           			 <button onclick="location.href=\''.$bDisplay->getUrl('en').'\'" class="optBut';
			                    if ($bDisplay->lang == 'en') print ' blueBack hoverBlack'; else print ' grayBack hoverBlue';
			                    print '">en</button>
			           			<button onclick="openPadMen()" class="optBut blueBack mobMen">&nbsp;</button>';
		                    ?>
		                    </div>
                        </div>
			           			-->
					</div>
				</div>
			</div>
			  <div class="headSpacer<?php if(sizeof($bDisplay->subNavList)==2) print ' headerSmallHl';?>" title=""></div> 
            <?php print $bDisplay->body['sec1']; ?>
            <?php  print $bDisplay->body['sec2']; ?>
            <br>
		</div>
            <!--
		<div class="mainRight">
			<div class="padMenu" id="padMenu">
            <?php  $bDisplay->printMobileMen() ?>
            <button class="pMenRet" onclick="closePadMen()"></button>
			</div>
		</div>
            -->
		<div class="padMenCloseDiv" onclick="closePadMen()"></div>
	</div>
	<br>
	<br>
	<footer class="mainFooter">
		<div class="footerBlack">
			<div class="footerSizer">
				<div class="footImgCnt">© <?php if ($bDisplay->lang == 'es') { print "Todos los derechos reservados"; } else { print "All Rights Reserved"; }  print $GLOBALS["siteName"] ?><img class="ccFooter" src="<?php print $GLOBALS['root']?>pic/by-nc-nd.png" data-src="<?php print $GLOBALS['root']?>pic/by-nc-nd.png" />
				</div>
			</div>
		</div>
	</footer>
</body>
</html>
