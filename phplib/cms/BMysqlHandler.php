<?php
    
    include_once ("BLoadKeyDefs.php");
    include_once ("./config_globals.php");
    
    class BMysqlHandler
    {
        public $con;
        public $url;
        public $db_name;
        public $usr;
        public $pw;
        public $client_table;
        public $users_table;
        public $db_selected;
        protected $tables = array();
        protected $users;
        protected $actQ;
        public $actClient;
        public $clientKey;
                
        function __construct($url, $dbName, $usr, $pw)
        {            
            // make user Key to get the arguments;
            $this->clientKey = new KClient();

            $this->url = $url;
            $this->db_name = $dbName;
            $this->usr = $usr;
            $this->pw = $pw;
            $this->client_table = "bcms_clients";
            $this->users_table = "bcms_users";
            
            array_push($this->tables, array($this->client_table, $this->clientKey->args, $this->clientKey->argsMysqlDef));
            array_push($this->tables, array($this->users_table, $this->clientKey->subArgs, $this->clientKey->subArgsMysqlDef));
            
            $this->openDb();

            // if not create it
            if (!$this->db_selected)
            {
                print "database doesn´t exist, it will be created now.<br>";
                $sql = "CREATE DATABASE ".$this->db_name;
                if ( mysqli_query($this->con, $sql) )
                {
                    echo "Database my_db created successfully<br>";
                } else {
                    echo "Error creating database: " . mysqli_error($this->con);
                }
                $this->db_selected = mysqli_select_db($this->con, $this->db_name);
            }
            
            // if tables don´t exist, create them
            foreach($this->tables as $table)
            {
                $create_table = "CREATE TABLE IF NOT EXISTS ".$table[0]." ( ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
                for ($i=0;$i<sizeof($table[1]);$i++)
                {
                    $create_table .= $table[1][$i]." ".$table[2][$i];
                    if ($i != sizeof($table[1]) -1) $create_table .= ", ";
                }
                $create_table .= ")";
                $create_tbl = $this->con->query($create_table);
            }

            $this->actQ = $this->getClientByName($GLOBALS["pubClientName"]);

            // if pub user doesn´t exist, add him user
            if ( $this->actQ->num_rows == 0 )
            {
                $arg = array_fill(0, sizeof($this->clientKey->args), "");
                $arg[0] = $GLOBALS["pubClientName"];
                $arg[2] = $GLOBALS["pubClientName"];
                $this->addClient($arg);
            }
            
            // get active users
            $this->reloadClients();
            
            mysqli_close($this->con);
        }
        
        function openDb()
        {
            // open mysql connection
            $this->con = mysqli_connect($this->url, $this->usr, $this->pw);
            if (mysqli_connect_errno()) print_r ('mysql Verbindung fehlgeschlagen: ' . mysqli_connect_error());
            
            // check if mysql database bcms_clients exists
            $this->db_selected = mysqli_select_db($this->con, $this->db_name);
            
            return !mysqli_connect_errno();
        }
        
        function getClients()
        {
            return $this->clients;
        }

        // aufruf nur in klasse
        function getClientByName($name)
        {
            $ret = false;
            $cmd = "SELECT * FROM `".$this->client_table."` WHERE name='".$name."'";
            $this->actQ = $this->con->query($cmd);
            if ( !$this->actQ ) print "error: ".mysqli_error($this->con); else $ret = $this->actQ;
            return $ret;
        }
        
        
        function getClientArg($nr, $arg)
        {
            return $this->clients[$nr][$arg];
        }

        
        function getClientArgs($nr)
        {
            return $this->clients[$nr];
        }
        
        function getClientArgByName($name, $arg)
        {
            $ret = false;
        	if ( $this->openDb() )
        	{
				$cmd = "SELECT * FROM `".$this->client_table."` WHERE name='".$name."'";
				$this->actQ = $this->con->query($cmd);
				
				if ( !$this->actQ )
				{
					print "error: ".mysqli_error($this->con);
				} else
				{
					$actClientFold = array();
					while ($row = $this->actQ->fetch_assoc()) 
					{
						array_push($actClientFold, $row);
					}
					
//					$actClientFold = $this->actQ->fetch_all(MYSQLI_ASSOC);
//					print_r( $actClientFold );
					
					$ret = $actClientFold[0][$arg];
				}
				
				mysqli_close($this->con);
        	}
            return $ret;
        }
        
        // wird gerade nicht benutzt
        function getActClientArg($arg)
        {
            $ret = false;
            $cmd = "SELECT * FROM `".$this->client_table."` WHERE name='".$this->actClient."'";
            $this->actQ = $this->con->query($cmd);
            
            if ( !$this->actQ )
            {
                print "error: ".mysqli_error($this->con);
            } else
            {
                $actClientFold = $this->actQ->fetch_all(MYSQLI_ASSOC);
                $ret = $actClientFold[0][$arg];
            }
            
            return $ret;
        }
        
        
        function getClientPass($name)
        {
            $ret = false;
            $ret = $this->getClientArgByName($name, "passw");
            
            return $ret;
        }
        
        
        function getNrClients()
        {
            return sizeof($this->clients);
        }

        
        function getUsersOfClient($name)
        {
            $ret = false;
        	if ($this->openDb() )
        	{        	
        		$cmd = "SELECT * FROM `".$this->users_table."` WHERE client='".$name."'";
        		$this->actQ = $this->con->query($cmd);
            
        		if ( !$this->actQ )
        		{
        			print "error: ".mysqli_error($this->con);
        		} else
        		{
        			//$ret = $this->actQ->fetch_all(MYSQLI_ASSOC);
        			$ret = array();
    				while ($row = $this->actQ->fetch_assoc()) 
    					array_push($ret, $row);
        		}
            
        		mysqli_close($this->con);
        	}
            return $ret;
        }
        
        
        function getUsersOfActClient()
        {
            $ret = false;
        	if ($this->openDb())
        	{ 
				$cmd = "SELECT * FROM `".$this->users_table."` WHERE client='".$this->actClient."'";
				$this->actQ = $this->con->query($cmd);
				
				if ( !$this->actQ )
				{
					print "error: ".mysqli_error($this->con);
				} else
				{
					$ret = array();
					while ($row = $this->actQ->fetch_assoc()) 
						array_push($rets, $row);
					//$ret = $this->actQ->fetch_all(MYSQLI_ASSOC);
				}
				mysqli_close($this->con);
			}
            return $ret;
        }
        
        
        function addClient($vars)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				$cmd = "INSERT INTO `".$this->client_table."`(";
	
				for ($i=0;$i<sizeof($this->clientKey->args);$i++)
				{
					$cmd .= "`".$this->clientKey->args[$i]."`";
					if ($i != sizeof($this->clientKey->args) -1)
						$cmd .= ", ";
				}
				
				$cmd .= ") VALUES (";
	
				for ($i=0;$i<sizeof($this->clientKey->args);$i++)
				{
					if ($i<sizeof($vars))
					{
						$cmd .= "'".$vars[$i]."'";
					} else {
						$cmd .= "'".$this->clientKey->argsStdVal[$i]."'";
					}
					
					if ($i != sizeof($this->clientKey->args) -1)
						$cmd .= ", ";
				}
	
				$cmd .= ")";
				
				$this->actQ = $this->con->query($cmd);
				if ( !$this->actQ )
				{
					print "error: ".mysqli_error($this->con);
					$ret = false;
				}
				mysqli_close($this->con);
			}
            return $ret;
        }
        
        
        function addNewUserForClient($client)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				$cmd = "INSERT INTO `".$this->users_table."`(";
				
				for ($i=0;$i<sizeof($this->clientKey->subArgs);$i++)
				{
					$cmd .= "`".$this->clientKey->subArgs[$i]."`";
					if ($i != sizeof($this->clientKey->subArgs) -1)
						$cmd .= ", ";
				}
				
				$cmd .= ") VALUES (";
				
				for ($i=0;$i<sizeof($this->clientKey->subArgs);$i++)
				{
					if ( $this->clientKey->subArgs[$i] == "client" )
					{
						$cmd .= "'".$client."'";
					} elseif( $this->clientKey->subArgs[$i] == "userId" )
					{
						$cmd .= "UUID()";
					} else {
						$cmd .= "'".$this->clientKey->subArgsVals[$i]."'";
					}
					
					if ($i != sizeof($this->clientKey->subArgs) -1)
						$cmd .= ", ";
				}
				
				$cmd .= ")";
				
				$this->actQ = $this->con->query($cmd);
				if ( !$this->actQ )
				{
					print "error: ".mysqli_error($this->con);
					$ret = false;
				}
				mysqli_close($this->con);
        	}
            return $ret;
        }
        
        // wir momentan nicht benutzt
        function setActClientArg($arg, $val)
        {
            $ret = false;
            $cmd = "UPDATE `".$this->client_table."` SET ".$arg."='".$val."' WHERE name='".$this->actClient."'";
            $this->actQ = $this->con->query($cmd);
            
            if ( !$this->actQ )
            {
                print "error: ".mysqli_error($this->con);
            } else
            {
                $ret = true;
            }
            
            return $ret;
        }
        
        
        function delClient($usrNr)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				if ( $this->clients[$usrNr]['name'] != $GLOBALS["pubClientName"] )
				{
					$cmd = "DELETE FROM `".$this->client_table."` WHERE ";
	
					for ($i=0;$i<sizeof($this->args);$i++)
					{
						$cmd .= $this->clientKey->args[$i]." = '".$this->clients[$usrNr][ $this->clientKey->args[$i] ]."'";
						if ($i != sizeof($this->args) -1) $cmd .= " AND ";
					}
					
					$this->actQ = $this->con->query($cmd);
					if ( !$this->actQ ) {
						print "error: ".mysqli_error($this->con);
						$ret = false;
					}
				} else {
					print "public clients can´t be deleted";
					$ret = false;
				}
				mysqli_close($this->con);
			}
            return $ret;
        }

        
        function delClientByName($usrName)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				if ( $usrName != $GLOBALS["pubClientName"] )
				{
					$cmd = "DELETE FROM `".$this->client_table."` WHERE name='".$usrName."'";
					$this->actQ = $this->con->query($cmd);
					if ( !$this->actQ ) {
						print "error: ".mysqli_error($this->con);
						$ret = false;
					}
				} else {
					print "public clients can´t be deleted";
					$ret = false;
				}
				mysqli_close($this->con);
			}
            return $ret;
        }
        
        
        function delUserOfClient($client, $userId)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				$cmd = "DELETE FROM `".$this->users_table."` WHERE client='".$client."' AND userId='".$userId."'";
				$this->actQ = $this->con->query($cmd);
				if ( !$this->actQ ) {
					print "error: ".mysqli_error($this->con);
					$ret = false;
				}
				mysqli_close($this->con);
        	}
            return $ret;
        }
        
        
        function clientExists($clientName)
        {
            $ret = false;
            if (array_search( $clientName, array_column($this->clients, 'name') ) !== false)
                $ret = true;
            return $ret;
        }
        
        
        function update($arg, $usrNr, $newUsrName)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				$cmd = "UPDATE ".$this->client_table." SET ".$arg."='".$newUsrName."' WHERE ".$arg."='".$this->clients[$usrNr][$arg]."'";
				$this->actQ = $this->con->query($cmd);
				if ( !$this->actQ ) {
					print "error: ".mysqli_error($this->con);
					$ret = false;
				}
				mysqli_close($this->con);
			}
            return $ret;
        }
        
        
        function updateByName($arg, $name, $newArg)
        {
            $ret = true;
        	if ($this->openDb())
        	{
				$cmd = "UPDATE ".$this->client_table." SET ".$arg."='".$newArg."' WHERE name ='".$name."'";
				$this->actQ = $this->con->query($cmd);
				if ( !$this->actQ ) {
					print "error: ".mysqli_error($this->con);
					$ret = false;
				}
				mysqli_close($this->con);
			}
            return $ret;
        }
        
        
        function reloadClients()
        {
            $cmd = "SELECT * FROM `".$this->client_table."` ORDER BY name ASC";
            $this->actQ = $this->con->query($cmd);
            if ( $this->actQ )
            {
            	$this->clients = array();
				while ($row = $this->actQ->fetch_assoc()) 
					array_push($this->clients, $row);

            //	$this->clients = $this->actQ->fetch_all(MYSQLI_ASSOC);
            } else {
            	$this->clients = array();
            }
        }


        function updateUserArgByClientNameAndUserId($name, $userId, $arg, $value)
        {
            $ret = false;
        	if ($this->openDb())
        	{
        		$cmd = "UPDATE `".$this->users_table."` SET ".$arg."='".$value."' WHERE client='".$name."' AND userId='".$userId."'";
				$this->actQ = $this->con->query($cmd);
				
				if ( !$this->actQ )
				{
					print "error: ".mysqli_error($this->con);
				} else
				{
					$ret = true;
				}
				mysqli_close($this->con);
			}
        	
            return $ret;
        }
        
        
        function query($cmd)
        {
            $this->actQ = $this->con->query($cmd);
            if (!$this->actQ ) print "mysql error: ".mysqli_error($this->con);
            return $this->actQ;
        }
    }
?>