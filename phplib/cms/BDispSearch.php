<?php
    
    class BDispSearch
    {
        protected $xt;
        protected $asc;
        protected $lang;
        protected $searchKeys;
        protected $maxNrResults;
        protected $returnType;
        protected $fromUrl;
        protected $keys;
        
        function __construct(&$_xt, &$_keys, $lang, $maxNrResults, $returnType)
        {
            $this->xt = $_xt;
            $this->keys = $_keys;
            $this->lang = $lang;
            $this->maxNrResults = $maxNrResults;
            $this->returnType = $returnType;
            if(isset($GLOBALS["xmlClientLvl"]))
            {
                $this->searchKeys = array("/name/$lang", "/cont/$lang", "/head/$lang", "/l2/@place",
                                          "/l2/@year", "/l3/@place", "/l3/@year", "/l3/@keywords");
            } else {
                $this->searchKeys = array("/name/$lang", "/cont/$lang", "/head/$lang", "/l1/@place",
                                          "/l1/@year", "/l2/@place", "/l2/@year", "/l2/@keywords");
            }
            
            // open the associations database
            $this->asc = new XMLTools("db/searchConfig.xml");
        }
        
        // $searchRootXp with @short, restrTo [0, 1, 2 ... n] = level
        function proc($explSearch = "", $restrTo = -1, $searchRootXp = "")
        {
            $result = array();
            $searchTerm = "";
            
            // restrict search
            $restr = "";
            
            // get the corresponding index
            if ($searchRootXp != "")
            {
                $xmlSpl = explode("/", $searchRootXp);
                
                if ($restrTo < sizeof($xmlSpl))
                {
                    for ($i=0;$i<max($restrTo+1, 0);$i++)
                    {
                        if ($i>0) $restr .= "/";
                        $restr .= $xmlSpl[$i];
                    }
                }
            } else {
                for ($i=0;$i<max($restrTo+1, 0);$i++)
                {
                    if ($i>0) $restr .= "/";
                    $restr .= "l".$i;
                }
            }
            
            if (isset($_GET['search']))
            {
                $searchTerm = $_GET['search'];
            } else {
                $searchTerm = $explSearch;
            }
            
            if ( $searchTerm != "" )
            {
                // clean the search term, convert special characters
                $sTerm = remove_accents( $searchTerm );
                $sTerm = str_replace('-', ' ', $sTerm);
                $sTerm = preg_replace('/[^\a-zA-Z0-9\ \,]/s', '', $sTerm);
                
                // split at " " and ","
                $sTerms = preg_split('/[\,\ ]/', $sTerm);
                
                // remove empty entries
                if(($key = array_search("", $sTerms)) !== false)
                    unset($sTerms[$key]);
                
                // create an associative array with all names and the corresponding xml paths
                $sar = array();
                $sar['name'] = array();
                $sar['path'] = array();
                
                foreach ($this->searchKeys as $sKey => $sVal)
                {
                    $q = $this->xt->xpath->query("/".$sVal);

                    foreach ($q as $k => $v)
                    {                    	
                    	// get the xml path
                        $xmlPath = getXmlPathFromDomElem($this->xt, $v, $this->keys['nrLevels']);
                        
                        
                        // apply the restriction, filter the search root Element, if set
                        if ($restr == ""
                            || strpos($xmlPath[1], $restr) !== false
                            && ($searchRootXp == "" || $searchRootXp != $xmlPath[1]) )
                        {
                            // convert special characters
                            // array_push( $sar['name'], $this->remove_accents( $v->nodeValue ) );

                            // search also for the name without "." and "-"
                            $opt2Search = str_replace("-", " ", remove_accents( $v->nodeValue ));
                            $opt2Search = str_replace(".", " ", $opt2Search);
                            $opt2Search = str_replace(",", " ", $opt2Search);
                            
                            if ($opt2Search != "")
                            {
                                // if it´s a name look for association and insert them
                                if ($sVal == "/name/".$this->lang)
                                {
                                    $opt2Split = explode(" ", $opt2Search);

                                    foreach($opt2Split as $oVal)
                                    {
                                        // take the name ($opt2Search) and look for associations
                                        $a = $this->asc->xml->xpath($this->lang."/assoc[@name='".$oVal."']");

                                        if (sizeof($a) > 0)
                                        {
                                            foreach($a[0]->key as $nSearch)
                                            {
                                                array_push( $sar['name'], $nSearch );
                                                array_push( $sar['path'], $v );
                                            }
                                        }
                                    }
                                }
                                
                                array_push( $sar['name'], $opt2Search );
                                array_push( $sar['path'], $v );
                                //array_push( $sar['path'], $v->getNodePath() );
                            }
                        }
                    }
                }
                
//                 print "search Keys: ";
//                  print_r($sTerm);
//                  print "<br>";
                
                // apply regex
                $res = array();

                // search name  case insensitive
                $r = preg_grep ('/'.$sTerm.'/i', $sar['name']);
                foreach ($r as $rKey => $rVal) array_push($res, $rKey);
                
                // search for name without spaces, case insensitive
                $r = preg_grep ('/'.str_replace(" ", "", $sTerm).'/i', $sar['name']);
                foreach ($r as $rKey => $rVal) array_push($res, $rKey);
                
                if (!isset($_GET['searchStrict']))
                {
                    // search split term case insensitive
                    foreach ($sTerms as $sVal)
                    {
                        if (strlen($sVal) > 2)
                        {
                            $r = preg_grep ('/'.$sVal.'/i', $sar['name']);
                            foreach ($r as $rKey => $rVal) array_push($res, $rKey);
                        }
                    }
                }
                
                // remove duplicates
                
                // count nr of occurences
                $nrResults = sizeof($res);

                // zähle die anzahl der vorkommnisse pro gefundenem resultat
                $res = array_count_values( $res );
                asort($res);
                $ak = array_keys($res);
                
                for ($i=sizeof($ak)-1; $i>=max(sizeof($ak) - $this->maxNrResults, 0); $i--)
                {
                    $path = $sar['path'][ $ak[$i] ];

                    if ( is_a($path, "DOMAttr") )
                    {
                        $path = $path->parentNode;
                    } else {
                        while ( preg_match('/l[0-9]/', $path->tagName) == 0 )
                            $path = $path->parentNode;
                    }
                    
                    switch ( $this->returnType )
                    {
                        case "node" : array_push($result, $path);
                            break;
                        case "string" : array_push($result, $path->getNodePath());
                            break;
                    }
                }

                if ( $nrResults == 0 )
                {
                    $result = array(0, $_GET['search']);
                } else
                {
                    if (is_object($result[0]) && get_class($result[0]) != "DOMElement")
                        $result = array_unique($result);
                }
                
            } else {
                $result = array(0, "");
            }
                        
            return $result;
        }
    }
?>