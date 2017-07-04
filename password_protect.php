<?php

		
###############################################################
# BCMS Password manager
###############################################################
# Adapted from Page Password Protect 2.13 http://www.zubrag.com/scripts/ 



// Add login/password pairs below, like described above
// NOTE: all rows except last must have comma "," at the end of line
$LOGIN_INFORMATION = array(
  'bcms' => 'askhdIUH'
);

// request login? true - show login and password boxes, false - password box only
define('USE_USERNAME', true);

// User will be redirected to this page after logout
define('LOGOUT_URL', 'http://127.0.0.1');
	
// time out after NN minutes of inactivity. Set to 0 to not timeout
define('TIMEOUT_MINUTES', 0);

// This parameter is only useful when TIMEOUT_MINUTES is not zero
// true - timeout time from last activity, false - timeout time from login
define('TIMEOUT_CHECK_ACTIVITY', true);


///////////////////////////////////////////////////////
// do not change code below
///////////////////////////////////////////////////////

// show usage example
if(isset($_GET['help'])) {
  die('include_once following code into every page you would like to protect, at the very beginning (first line):<br>&lt;?php include_once("' . str_replace('\\','\\\\',__FILE__) . '"); ?&gt;');
}

// timeout in seconds
$timeout = (TIMEOUT_MINUTES == 0 ? 0 : time() + TIMEOUT_MINUTES * 60);

// logout?
if(isset($_GET['logout'])) {
  setcookie("verify_editor", '', $timeout, '/'); // clear password;
  header('Location: ' . LOGOUT_URL);
  exit();
}

if(!function_exists('showLoginPasswordProtect')) {

// show login form
function showLoginPasswordProtect($error_msg) {
?>
<html>
<head>
  <title>Please enter password to access this page</title>
  <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</head>
<body>
  <style>
    @font-face {
        font-family: 'KelsonSansRegular';
        src: url('style/fonts/kelsonsansregular.eot');
        src: url('style/fonts/kelsonsansregular.eot') format('embedded-opentype'),
        url('style/fonts/kelsonsansregular.woff2') format('woff2'),
        url('style/fonts/kelsonsansregular.woff') format('woff'),
        url('style/fonts/kelsonsansregular.ttf') format('truetype'),
        url('style/fonts/kelsonsansregular.svg#KelsonSansRegular') format('svg');
    }
    input { font-family: "KelsonSansRegular", Georgia, serif; font-size:0.8em; border: 1px solid black; border-radius:8px; width:190px; height:25px; padding:8px 5px 5px 5px; position:absolute; top:0px; right:0px; }
    input.subm { position:relative; top:0px; right:75px; padding:5px 5px 5px 5px; width:300px;
        background: rgb(255,255,255); /* Old browsers */
        background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(232,232,232,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(100%,rgba(232,232,232,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e8e8e8',GradientType=0 ); /* IE6-9 */
    }
    h2 { font-family: "KelsonSansRegular", Georgia, serif; font-size:2em; color: #009ee3; }
    h3 { font-family: "KelsonSansRegular", Georgia, serif; font-size:1em; }
    div.main { width:500px; margin-left:auto; margin-right:auto; margin-top:30px;text-align:center; border:solid 1px black; border-radius:10px; padding:20px;
        background: rgb(255,255,255); /* Old browsers */
        background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(232,232,232,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(100%,rgba(232,232,232,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom,  rgba(255,255,255,1) 0%,rgba(232,232,232,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e8e8e8',GradientType=0 ); /* IE6-9 */
    }
    div.row { width:300px; height:27px; text-align:left; margin:0 auto 0 auto; margin-bottom:5px; position:relative; }
    div.label { font-family: "KelsonSansRegular", Georgia, serif; width:30%; margin-left:0; text-align:left; display:inline-block;position:absolute;top:5px; left:0px; }
    div.input { width:70%; margin-left:0; text-align:left; display:inline-block; height:25px;}
    div.submit { width:150px; margin:0; text-align:left; display:inline-block; height:25px; }
  </style>
    <div class="main">
        <h2>Website Editor</h2><br>
        <form method="post">
            <h3>Please enter password to access this page</h3>
            <font color="red"><?php echo $error_msg; ?></font>
            <br />
            <?php
                if (USE_USERNAME) echo '<div class="row"><div class="label">Login:&nbsp;</div><div class="input"><input type="input" name="access_login" /></div></div><div class="row"><div class="label">Password:</div>';
            ?>
                <div class="input"><input type="password" name="access_password" /></div>
            </div>
            <p></p>
            <div class="submit"><input class="subm" type="submit" name="Submit" value="Submit" /></div>
        </form>
        <br />
        <a style="font-size:9px; color: #B0B0B0; font-family: Verdana, Arial;" href="http://www.zubrag.com/scripts/password-protect.php" title="Download Password Protector">Powered by Password Protect</a>
    </div>
</body>
</html>

<?php
  // stop at this point
  die();
}
}

// user provided password
if (isset($_POST['access_password'])) {

  $login = isset($_POST['access_login']) ? $_POST['access_login'] : '';
  $pass = $_POST['access_password'];
  if (!USE_USERNAME && !in_array($pass, $LOGIN_INFORMATION)
  || (USE_USERNAME && ( !array_key_exists($login, $LOGIN_INFORMATION) || $LOGIN_INFORMATION[$login] != $pass ) ) 
  ) {
    showLoginPasswordProtect("Incorrect password.");
  }
  else {
    // set cookie if password was validated
    setcookie("verify_editor", md5($login.'%'.$pass), $timeout, '/');
    
    // Some programs (like Form1 Bilder) check $_POST array to see if parameters passed
    // So need to clear password protector variables
    unset($_POST['access_login']);
    unset($_POST['access_password']);
    unset($_POST['Submit']);
  }

}

else {

  // check if password cookie is set
  if (!isset($_COOKIE['verify_editor'])) {
    showLoginPasswordProtect("");
  }

  // check if cookie is good
  $found = false;
  foreach($LOGIN_INFORMATION as $key=>$val) {
    $lp = (USE_USERNAME ? $key : '') .'%'.$val;
    if ($_COOKIE['verify_editor'] == md5($lp)) {
      $found = true;
      // prolong timeout
      if (TIMEOUT_CHECK_ACTIVITY) {
        setcookie("verify_editor", md5($lp), $timeout, '/');
      }
      break;
    }
  }
  if (!$found) {
    showLoginPasswordProtect("");
  }

}

?>
