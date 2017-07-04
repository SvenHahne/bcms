<?php
    
    #############################################################################
    # adapted from Page Password Protect 2.13 (http://www.zubrag.com/scripts/)  #
    #############################################################################
    
    class BLogin
    {
        protected $allAccessClients = array();
        protected $clients;
        protected $head;
        protected $error_msg = "";
        protected $incorrectPass = false;
        protected $login_info = array();
        protected $mysqlH;
        protected $timeout_minutes = 15;
        protected $timeout_check_activity = true;
        protected $timeout;
        protected $isPublic = true;
        
        public $actUser;
        public $requestLogin = false;
        
        
        public function __construct($_actUser, &$_mysqlH, $_reqLogin = false)
        {
            $this->actUser = $_actUser;
            $this->mysqlH = $_mysqlH;
            $this->requestLogin = $_reqLogin;
            
            // timeout in seconds
            $this->timeout = ($this->timeout_minutes == 0 ? 0 : time() + $this->timeout_minutes * 60);
            
            // get list of all access clients
            $this->clients = $this->mysqlH->getClients();
            foreach($this->clients as $client)
                if ($client["rights"] == "all")
                    array_push($this->allAccessClients, $client);
        }
        
        
        function draw(&$body, &$dPar, $protect = true)
        {
            $body .= '
                <script type="text/javascript">
                    function checkRedir()
                    {
                        var users = [';
                        
                        // clients to java
                        for($i=0;$i<sizeof($this->clients);$i++)
                        {
                            $body .= '"'.$this->clients[$i]['name'].'"';
                            if($i!=sizeof($this->clients)-1) $body .= ', ';
                        }
                        
            $body .= '];
                        var redirName = document.getElementById("logName").value;
                        if (users.indexOf(redirName) < 0) {
                            redirName = "login?unknown";
                        }
                        document.getElementById("logform").action += redirName;
                    }
                </script>
            
                <div class="login_main">
                    <h2 class="login">Login</h2><br>
                    <h3 class="login">Please enter password to access your personal area</h3>
                ';
            
            if ($this->incorrectPass)
                $body .= '
                    <br>
                    <div class="wrongPass">incorrect password!!!</div>
                ';
            
            if (isset($_GET['unknown']))
                $body .= '
                    <br>
                    <div class="wrongPass">Unknown user!!!</div>
                    ';
            
            $body .= '
                    <br>
                    <font color="red">'.$this->error_msg.'</font>
                    <form action="'.$GLOBALS['root'].'" onsubmit="checkRedir()" class="login" id="logform" method="post">
                        <div class="login_row">
                            <input onclick="this.value=\'\'" class="login" type="input" name="access_login" id="logName" value="Login" />
                        </div>
                        <div class="login_row">
                            <input onclick="this.value=\'\'" class="login" type="password" name="access_password" value="Password" />
                        </div>
                        <div class="login_submit">
                            <input class="subm" type="submit" name="Submit" value="Submit" />
                        </div>
                    </form>
                </div>
            ';
        }
        
        
        function checkGets()
        {
            // for restriced areas check logout
            if (isset($_GET['logout']))
            {
                setcookie("verify_".$this->actUser, '', $this->timeout, '/'); // clear password;
                header('Location: ' . $GLOBALS['root']."index.php");
            }
            
            // add the all access users to the login_info if needed
            foreach($this->allAccessClients as $usrName)
                if (array_search($usrName['passw'], $this->login_info) === false)
                    $this->login_info[$usrName['name']] = $usrName['passw'];
            
            // user provided password
            if (!$this->isPublic)
            {
                if (isset($_POST['access_password']))
                {
                    $login = isset($_POST['access_login']) ? $_POST['access_login'] : '';
                    $pass = $_POST['access_password'];
                    
                    if ( !array_key_exists($login, $this->login_info) || $this->login_info[$login] != $pass)
                    {
                        $this->incorrectPass = true;
                    } else
                    {
                        // set cookie if password was validated
                        setcookie("verify_".$this->actUser, md5($login.'%'.$pass), $this->timeout, '/');
                        
                        // Some programs (like Form1 Bilder) check $_POST array to see if parameters passed
                        // So need to clear password protector variables
                        unset($_POST['access_login']);
                        unset($_POST['access_password']);
                        unset($_POST['Submit']);
                        
                        // disable login window
                        $this->requestLogin = false;
                    }
                } else
                {
                    // check if password cookie is set
                    if (!isset($_COOKIE['verify_'.$this->actUser]))
                        $this->requestLogin = true;
                    
                    // or check if the cookie of an all access usr is set
                    foreach($this->allAccessClients as $usrName)
                        if (!isset($_COOKIE['verify_'.$usrName['name']]))
                            $this->requestLogin = true;
                    
                    // check if cookie is good
                    $found = false;
                    
                    foreach($this->login_info as $key=>$val)
                    {
                        $lp = $key.'%'.$val;
                        
                        $cookieSet = isset($_COOKIE['verify_'.$this->actUser]) && $_COOKIE['verify_'.$this->actUser] == md5($lp);
                        
                        // check for all access users
                        foreach($this->allAccessClients as $usrName)
                            if (isset($_COOKIE['verify_'.$usrName['name']]) && $_COOKIE['verify_'.$usrName['name']] == md5($lp))
                                $cookieSet = true;
                        
                        if ($cookieSet)
                        {
                            $found = true;
                            
                            // prolong timeout
                            if ($this->timeout_check_activity)
                                setcookie("verify_".$this->actUser, md5($lp), $this->timeout, '/');
                            
                            $this->requestLogin = false;
                        }
                    }
                    
                    if (!$found)
                        $this->requestLogin = true;
                }
            }
        }
        
        
        function setActUser($name)
        {
            $this->actUser = $name;
            if ($name != $GLOBALS["pubClientName"])
            {
                $this->login_info = array();
                $this->login_info[ $name ] = $this->mysqlH->getClientPass( $name );
                $this->requestLogin = true;
                $this->isPublic = false;
            }
        }
        
        
        function setActUserToPublic()
        {
            $this->actUser = $GLOBALS["pubClientName"];
            $this->requestLogin = false;
            $this->isPublic = true;
        }
    }
?>