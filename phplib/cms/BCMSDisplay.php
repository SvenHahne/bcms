<?php
    
    include_once ( './config_globals.php' );
    include_once ( 'BCMS.php' );
    include_once ( 'BUtilityFunctions.php' );
	include_once ( 'BDispContentDraw.php' );
	include_once ( 'BDispPar.php' );
    include_once ( 'BDispSearch.php' );
    include_once ( 'BLogin.php' );

	class BCMSDisplay extends BCMS
	{
        protected $getLevel = array();
        protected $mysqlH;
        protected $nrLevels;
        protected $calledFromStart = false;
        protected $changedToNewUrl = false;
        protected $changedToContent = false;
        protected $doResizeImgs = true;
        protected $setResizeImgFlag = true;

        public $actL0 = "";
        public $bd;
        public $body = array();
        public $browsWinSize = array();
		public $lang = "";
		public $keys;
        public $curUrl;
        public $fromUrl = "";
		public $titel;
		public $linkStr;
        public $linkAttr = array();
        public $loginHandler;
        public $imgPrfx;
		public $head = array();
        public $menAnim;
		public $nav = array();
		public $footer = array();
		public $langPreset;
		public $preSel = "";
        public $shiftDown = true;
        public $dataPath;
        public $searchTxt = array();
        public $subNavList = array();
        public $xmlStr;
        public $xmlt;

		public function __construct($_dataPath, $_configPath, $_titel, $_langPreset, $_mysqlH)
		{
            $this->mysqlH = $_mysqlH;

            // load superclass
            include_once( './phplib/cms/EditorModules/BEditorModule.php' );
            include_once( './phplib/cms/EditorModules/BEditorRss.php' );
            include_once( 'BLoadKeyDefs.php' );
			include_once( $_configPath );
			include_once( 'stylemapping.php' );

			$this->nrLevels = $keys['nrLevels'];
			$this->actLvl = 0;
			$this->keys = &$keys;
			$this->titel = $_titel;
			$this->langPreset = $_langPreset;
			$this->head['base'] = "";
            $this->head['jqGlobVar'] = "";
			$this->head['jqDocReady'] = "";
			$this->head['jqWinLoad'] = "";
			$this->head['jqWinResize'] = "";
			$this->head['jqWinScroll'] = "";
			$this->head['js'] = "";
			$this->body['sec1'] = "";
			$this->body['sec2'] = "";

            $this->searchTxt['de'] = "Suche";
            $this->searchTxt['en'] = "Search";
            $this->searchTxt['es'] = "Búsqueda";

            $this->dataPath = $_dataPath;
            $this->loginHandler = new BLogin($GLOBALS["pubClientName"], $_mysqlH);

            //---------------------------------------------------------------------------------------------------------

            // get script root, for making all paths absolute
            $GLOBALS['root'] = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], "index"));

			//---------------------------------------------------------------------------------------------------------

            // call img resize Function
            if (isset($_POST['doResizeImgs'])) $this->resizeImgs();
		}
		
		//--------------------------------------------------------------------------------
        
		public function init()
		{
            // open database, access not via url but via file
            $this->xmlt = new XMLTools($this->dataPath);
            $this->procGets();
            
            // if nothing selected -> public entry
            if ($this->getLevel[0] == "")
            {
                $this->loginHandler->requestLogin = false;
                
                // get pub short
                $pubShortQ = $this->xmlt->xml->xpath("//l".$GLOBALS["xmlClientLvl"]."[@client='".$GLOBALS["pubClientName"]."']/@short");
                if (sizeof($pubShortQ)>0)
                    $this->getLevel[0] = (string)$pubShortQ[0];
            }
 
            // get direct or rewritten and converted url
            $this->curUrl = $this->getUrl($this->lang);
            // get current Xpath string and link
            $res = $this->getCurXmlStr($this->keys, $this->getLevel, $this->lang);

			// if we have a special SubMenu that uses javascrpit funcionality add init it here
            //$this->initSubMenu( $res[0] );
            
            // make the header
			$this->makeBaseHead();

            // check if the actual node has no children other than levels
            $hasContent = false;
            $level = $this->actLvl;

            if ( ($level == 1 && $GLOBALS["xmlPubClientShort"]) || ($level == 0 && !$GLOBALS["xmlPubClientShort"]) )
            	$this->calledFromStart = true;            
            
            while(!$hasContent && $level < $this->keys['nrLevels'])
            {
                $q = $this->xmlt->xpath->query($res[0]);
                
                if ($q->length > 0)
                {                    
                    $q2 = $q->item(0);
                    $hasContent = false;
                    
                    // look for a level entry
                    for($i=0;$i<$q2->childNodes->length;$i++)
                        if ( !(strlen($q2->childNodes->item($i)->tagName) == 2 && $q2->childNodes->item($i)->tagName[0] == "l")
                            && $q2->childNodes->item($i)->tagName != "name")
                            $hasContent = true;

                    // if there is no content in the actual level, step down until there is and get the first level entry
                    if (!$hasContent)
                    {                        
                        // check if there is an isstart argument on that level
                        $hasIsStart = false;
                        
                        $newQ = $this->xmlt->xpath->query($res[0]."/l".$level."/@isstart");

                        if ($newQ->length > 0) $hasIsStart = true;
                        
                        if ($hasIsStart)
                        {
                            // if level has the isStart Argument show, where it is set
                            $newQ = $this->xmlt->xpath->query($res[0]."/l".$level."[@isstart='1']");
                            
                            if ($newQ->length > 0)
                            {
                                $getPath = getXmlPathFromDomElem($this->xmlt, $newQ->item(0), $this->keys['nrLevels']);
                                $getPath = $getPath[1];
                                if ( $getPath[ strlen($getPath) -1] ==  "/" )
                                	$getPath = substr( $getPath, 0, strlen($getPath) -1);
                                
                                $res[0] = $getPath;
                                
                                //$res[0] .= "/l".$level."[@short='".$newQ->item(0)->getAttribute('short')."']";
                                //$res[1] .= "&l".$level."=".$newQ->item(0)->getAttribute('short');
                                
                                $this->actLvl++;
                                $this->curUrl = $res[1]."&lang=".$this->lang;
                                $this->changedToNewUrl = true;
                            } else {
                            	$res[0] .= "/l".$level;
                                $res[1] .= "&l".$level;
                            }                     

                        } else
                        {
                            // look for the next valid content
                            $newQ = $this->xmlt->xpath->query($res[0]."/l".$level);
                        	
                            if($newQ->length > 0)
                            {
                                for($i=0;$i<$newQ->length;$i++)
                                {
                                    if ( !(strlen($newQ->item($i)->tagName) == 2 && $newQ->item($i)->tagName[0] == "l")
                                        && $newQ->childNodes->item($i)->tagName != "name")
                                    {                                    	
                                        $hasContent = true;
                                        $res[0] .= "/l".$level."[@short='".$newQ->item($i)->getAttribute('short')."']";
                                        $res[1] .= "&l".$level."=".$newQ->item($i)->getAttribute('short');
                                        $this->actLvl++;
                                    }
                                }
                            }
                            
                            // if there is no content and there are sub levels, step one level down                            
                            if ( !$hasContent && $newQ->length > 0 ) 
                            	$res[0] .= "/l".$level;
                        }
                    }
                }
                $level++;
            }
                        
			$this->xmlStr = $res[0];
			$this->linkStr = $res[1];
        	
            // check if image resizing is needed
            $q = $this->xmlt->xml->xpath($this->xmlStr."/@optimized");
            if(sizeof($q)>0) $q = (int)$q[0];
            $this->doResizeImgs = false;	// hier noch problem, nicht allgemein!!! wenn nicht mit optimierten images, problem
            
			$this->makeNav($res[0], $this->nav, 0, 0);
			$this->makeNav($res[0], $this->footer, 1, 0);
			$this->makeBody();
		}
		
		//--------------------------------------------------------------------------------
        
		private function procGets()
		{
            // REWRITE MODULE NEEDED!!!
            // if url comes from mod_rewrite or the rewriteLink funtion
            if ( isset($_GET['goto']) )
            {            	
                $tmpPre = $this->preSel;
                $this->preSel = "";
                
                $rwsExpl = explode("/", $_GET['goto']);
                
                // check the contents of the goto argument
                // wether it´s a user or a subfolder
                $clients = $this->mysqlH->getClients();
                $foundInd = -1;
                foreach($clients as $key => $val)
                {
                    foreach($rwsExpl as $ind => $rwArg)
                    {
                        if ($val['name'] == $rwArg)
                        {
                            $this->loginHandler->setActUser($rwArg);
                            $this->linkAttr['usr'] = $rwArg;
                            $foundInd = $ind;
                        }
                    }
                }
                
                
                // if no user found and set to public user
                if ($foundInd == -1)
                {
                    $this->loginHandler->setActUserToPublic();
                    array_unshift($rwsExpl, $GLOBALS["pubClientName"]);
                }
                
                
                // check if it leads directly to an entry
                $xStr = "";
                $match = array_fill(0, sizeof($rwsExpl), 0);
                //$matchLang = array_fill(0, sizeof($rwsExpl), 0);
                $gotLang = false;
                
                for ($i=0;$i<sizeof($rwsExpl);$i++)
                {
                    if ($i>0) $xStr .= "/";
                    $xStr .= "l".$i;
                    
                    // search for each language, if the entry exists
                    foreach( $this->keys['langs'] as $lang )
                    {
                        if (!$gotLang || ($gotLang && $this->lang == $lang))
                        {
                            $q = $this->xmlt->xpath->query($xStr."/name/".$lang);
                            $ind = 0;
                            $foundVal = false;
                            
                            while (!$foundVal && $ind < $q->length)
                            {
                                $name = $q->item($ind)->nodeValue;
                                
                                // check if the nodeValue is explicit
                                $q2 = $this->xmlt->xpath->query($xStr."/name/".$lang."[text()='".$name."']");
                                
                                // if not explicit add the rewriteLinksExplicit Attrib
                                if ( $q2->length > 1  )
                                {
                                    $q3 = $this->xmlt->xpath->query($xStr."[".($ind+1)."]/@".$GLOBALS["rewriteLinksExplAttr"]);
                                    if ($q3->length > 0)
                                    {
                                        $cleanStr = str_replace(array(" ", ".", ",", ")", "(", "-", "/"), "", $q3->item(0)->nodeValue);
                                        $cleanStr = remove_accents($cleanStr);
                                        $cleanStr = strtolower($cleanStr);
                                        $name .= "_".$cleanStr;
                                    }
                                }
                                
                                $cleanStr = str_replace(array(" ", ".", ",", ")", "(", "-", "/"), "", $name);
                                $cleanStr = remove_accents($cleanStr);
                                $cleanStr = strtolower($cleanStr);
                                

                                if ($cleanStr == $rwsExpl[$i])
                                {
                                    $foundVal = true;
                                    $xStr .= "[".($ind+1)."]";
                                    $match[$i] = 1;
                                    if ( !$gotLang && $i != 0 ) {
                                        $this->lang = $lang;
                                        $gotLang = true;
                                    }
                                    $this->getLevel[$i] = $q->item($ind)->parentNode->parentNode->getAttribute('short');
                                }
                                
                                $ind++;
                            }
                        }
                        
                        // if item found, stop searching
                        if ($foundVal) break;
                    }
                }
                
                // if entry found, check if there´s content,
                // if not get first entry of one level lower
                if (array_sum($match) == sizeof($rwsExpl))
                {
                    $hasContent = false;
                    $q = $this->xmlt->xpath->query($xStr);
                    if ($q->length > 0)
                    {
                        // loop through childNodes
                        for ($i=0;$i<$q->item(0)->childNodes->length;$i++)
                        {
                            $tagName = $q->item(0)->childNodes->item($i)->tagName;
                            if (strlen($tagName) != 2 && $tagName != "name")
                                $hasContent = true;
                        }
                    }
                    
                    // no content get one level lower and
                    // if there are children (there is always a "name" child, so length has to be >1
                    if ( $GLOBALS["rewriteLinksStepToContent"] && !$hasContent && $q->item(0)->childNodes->length > 1)
                    {
                        $nextLower = $this->xmlt->xpath->query($xStr."/l".sizeof($rwsExpl)."[1]");
                        if ($nextLower->length > 0)
                            $this->getLevel[sizeof($rwsExpl)] = $nextLower->item(0)->getAttribute('short');
                        $this->changedToContent = true;
                    }
                    
                } else
                {
                    // if not for each part of the link a match was found
                    // get all name entries as tree and search for it
                    $found = array();

                    foreach( $this->keys['langs'] as $lang )
                    {
                        $q = $this->xmlt->xpath->query("//name/".$lang);
                        $found[$lang] = array();
                        
                        for ($i=0;$i<$q->length;$i++)
                        {
                            $node = $q->item($i);
                            $cleanStr = str_replace(array(" ", ".", ")", "(", "-"), "", $node->nodeValue);
                            $cleanStr = remove_accents($cleanStr);
                            $cleanStr = strtolower($cleanStr);
                            
                            if ($cleanStr != "")
                                foreach($rwsExpl as $ind => $rwArg)
                                if ($rwArg == $cleanStr)
                                    array_push($found[$lang], $node->getNodePath());
                        }
                    }
                    
                    // select the language with more results
                    usort($found, function($a, $b) {
                          return sizeof($a) - sizeof($b);
                          });
                    
                    if (sizeof($found) > 1)
                    {
                        $found = $found[1];
                    } else if(sizeof($found) > 0)
                    {
                        $found = $found[0];
                    }

                    if (sizeof($found) > 0)
                    {
                        // get the lang
                        $this->lang = explode("/", $found[0]);
                        $this->lang = $this->lang[ sizeof($this->lang) -1];
                        $this->linkAttr['lang'] = $this->lang;

                        // strip name/$lang
                        for($i=0;$i<sizeof($found);$i++)
                            $found[$i] = str_replace("/name/".$this->lang, "", $found[$i]);
                        
                        // kill all strings that are part of other strings
                        $kill = array();
                        for($i=0;$i<sizeof($found);$i++)
                            for($j=0;$j<sizeof($found);$j++)
                                if ($i!=$j)
                                    if (strpos($found[$i], $found[$j]) !== false)
                                        array_push($kill, $j);

                        foreach($kill as $val)
                            unset( $found[$val] );

                        // reorder
                        $found = array_values($found);

                        // take the first result and convert it, set $this->getLevel
                        if (sizeof($found) > 0)
                        {
                        	$lvls = explode("/", $found[0]);
                        
                        	// set xStr to beginning
                        	$xStr = "/".$lvls[1];
                        
                        	for ($i=0;$i<sizeof($lvls)-2;$i++)
                        	{
                        		$xStr .= "/".$lvls[$i+2];
                        		$q = $this->xmlt->xml->xpath($xStr);
                        		$this->getLevel[$i] = (string) $q[0]['short'];
                        	}
                        }
                        
                    } else {
                        $this->preSel = $tmpPre;
                    }
                }
            }
            
			// check if any levels were selected
			for ($i=0;$i<$this->nrLevels;$i++) 
			{
				if ( isset($_GET[ 'l'.$i ]) )
				{ 
					$this->getLevel[$i] = $_GET[ 'l'.$i ];
                    if ($i==0) $this->actL0 = $_GET[ 'l'.$i ];
				} else
                {
					if ( !isset($this->getLevel[$i]) )
                        $this->getLevel[$i] = false;
				}
			}
            
			if ( isset($_GET['lang']) ) {
                
                $this->lang = $_GET['lang'];
                $this->linkAttr['lang'] = $this->lang;
            } else {
                if ($this->lang == "")
                    $this->lang = $this->langPreset;
            }
            
            // geht das hier???
            if ( isset($_GET['path']) ) {
                $GLOBALS['root'] = $_GET['path']."/";
                $GLOBALS['backupUrl'] = $_GET['path']."/";
                $this->linkAttr['rootP'] = $GLOBALS['root'];
            }
            
            if ( isset($_GET['sel']) )
                $this->linkAttr['sel'] = $_GET['sel'];
            
            if ( isset($_GET['dstyle']) )
                $this->linkAttr['dstyle'] = $_GET['dstyle'];
            
            $this->loginHandler->checkGets();
        }
		
		//--------------------------------------------------------------------------------
        
		private function makeBaseHead()
		{
			$this->head['base'] .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta http-equiv="content-language" content="'.$this->langPreset.'">
            <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
            <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
			<meta name="robots" content="INDEX">
			<meta name="description" content="'.$this->titel.' - ">
			<meta name="abstract" content="InStore Diseño">
			<meta name="keywords" content="InStore Diseño">
			<meta name="author" content="'.$this->titel.'">
			<meta name="publisher" content="'.$this->titel.'">
			<meta name="copyright" content="'.$this->titel.'">
			<meta name="audience" content="Alle">
			<meta name="page-topic" content="">
			<meta name="revisit-after" content="3 days">
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1" />
			
			<title>'.$this->titel.'</title>
			';
		}
		
        //--------------------------------------------------------------------------------
        
		private function makeBody()
		{
			$this->bd = new BDispContentDraw($this->xmlt, $this->lang, $this->keys, $this->xmlStr,
									   $this->actLvl, $this->head, $this->body, $this->shiftDown,
                                       $this->linkAttr, $this->mysqlH, $this->curUrl, $this->subNavList,
                                       $this->loginHandler, $this->imgPrfx, $this->doResizeImgs, $this->changedToContent,
									   $this->calledFromStart);
		}
		
		//--------------------------------------------------------------------------------
        
		private function makeNav($xStr, &$ar, $isFoot, $isHide)
		{
            // always highest level
            $xmlStr = $xStr;
            $subLvlStr = substr($xStr, 0, strripos($xStr, "["));
            
            $found = -1;
            preg_match("/[0-9]{4}/i", $subLvlStr, $found);
            if (sizeof($found) > 0) $found = $found[0];
            
            // extract the highest level, with or without user level
            if (isset($GLOBALS["xmlClientLvl"])
                //&& ( $found != -1 && $found == $GLOBALS["xmlPubClientShort"] )
                )
            {
                $xmlStr = substr($xStr,
                                 strpos($xStr, "l".$GLOBALS["xmlClientLvl"]),
                                 strpos($xStr, "]") - strpos($xStr, "l".$GLOBALS["xmlClientLvl"]) +1 );
                $subLvlStr = substr($xStr, 0, strpos($xStr, "l".($GLOBALS["xmlClientLvl"]+2)) -1 );
            } else
            {
                $subLvlStr = substr($xStr, 0, strpos($xStr, "l1") -1 );
            }
            $subLvlStr = substr($subLvlStr, 0, strripos($subLvlStr, "["));

            
            $is_footer = (array) $this->xmlt->xml->xpath( $subLvlStr."/@footer" );
			$is_hide = (array) $this->xmlt->xml->xpath( $subLvlStr."/@hide" );
			
			$ar['names'] = array();
			$ar['short_names'] = array();
			$ar['link'] = array();

			for ($i=0;$i<sizeof( $this->xmlt->xml->xpath($subLvlStr) );$i++)
			{
				if ( !( sizeof($is_footer) > 0 && !intval( $is_footer[$i] ) == $isFoot )
					&& !( sizeof($is_hide) > 0 && !intval( $is_hide[$i] ) == $isHide )
					)
				{
					$name = $this->xmlt->xml->xpath( $subLvlStr."[".($i+1)."]/name/".$this->lang );
					$short = $this->xmlt->xml->xpath( $subLvlStr."[".($i+1)."]/@short" );                    
                    $client = $this->xmlt->xml->xpath( $xmlStr."/@client" );
                    if (sizeof($client) > 0) $client = (string)$client[0];
                    
                    if ( $short[0] == $this->actL0 )
                        $this->actL0 = ucfirst($name[0]);
                    
                    if ( $client == $GLOBALS["pubClientName"] || $client == $this->loginHandler->actUser )
                    {
                    	if ( sizeof( $name ) > 0 )
                    	{
                        	array_push( $ar['names'], ucfirst($name[0]) );
                        	array_push( $ar['short_names'], $short[0] );
                     
                        	$link = preg_replace("/\[@short|'|\]/i", "", $subLvlStr);
                        	$link = str_replace("/", "&", $link);
                        
                        	array_push( $ar['link'], $GLOBALS['root']."index.php?".$link."=".$short[0] );
                    	}
                    }
				}
			}
		}
		
        //--------------------------------------------------------------------------------
        
        public function initSubMenu(&$curXmlPath)
        {
        	$this->menAnim = new MenuAnimation();        	
        	$this->menAnim->addHead($this->head, $curXmlPath);
        }

    	//--------------------------------------------------------------------------------

        public function printSubMenu()
        {        	
        	// get submenu style
        	$q = $this->xmlt->xml->xpath($this->xmlStr."/@submenustyle");
        	if (sizeof($q) > 0) $q = "".$q[0];

        	switch($q) 
        	{
           	   case "std" : 
           		   print '<div class="klmListWrapper"> <div class="klmListHead"> <div class="lvlDropDownBig">';

                   for($i=0;$i<sizeof($this->subNavList)-1;$i++)
                   {
                       print '<div class="subNav"><a ';
                       if ($this->subNavList[$i]['short'] == $this->subNavList['selLvlShort'])
                       {
                           print 'class="blue subNavSwSmall" ';
                       } else {
                           print 'class="subNavSwBig" ';
                       }
                       print 'href="'.rewriteLink($this->subNavList[$i]['link'].'&sel='.$i.'&lang='.$this->lang, $this->xmlt, $this->keys).'" target="_self">'.$this->subNavList[$i]['name'];
                       print '</a></div>';
                       if ($i != sizeof($this->subNavList)-2)
                           print '<div class="subNavSep">|</div>';
                   }
                   
           		   print '</div></div></div>';

                   break;
           	   case "anim" :
           		   $this->menAnim->draw($this->body['sec1'], $this->subNavList, $this->bd->dPar);
           		   break;
           	   default : break;
        	}
        }

        //--------------------------------------------------------------------------------
        
        // function for recursive call to build the navigation
        public function mobileMenIt(&$itLvl, &$itStr, $lang, $headName)
        {
            // get subentries of level
            $q = $this->xmlt->xml->xpath($itStr."/l".($itLvl+1)."/name/".$lang);
            
            for ($i=0;$i<sizeof($q);$i++)
            {
                $isHidden = false;
                $hidden = $this->xmlt->xml->xpath($itStr."/l".($itLvl+1)."[".($i+1)."]/@hide");

                if (sizeof($hidden) > 0)
                    $isHidden = (boolean)(int)$hidden[0];

                if (!$isHidden)
                {
                    if ($itLvl == $GLOBALS["xmlClientLvl"])
                    {
                        print '<li class="paddMenNav">';
                        print '<a class="';
                        if (in_array($q[$i], $headName)) print 'inWhite';
                        print ' padMenNav"';
                    } else {
                        print '<li class="padSubMen" style="margin-left:'.(($itLvl+1)*10 + 5).'px;">';
                        print '<a class="black whiteHover"';
                    }
                    
                    print ' href="'.rewriteLink(getHrefFromXmlStr($this->xmlt, $itStr."/l".($itLvl+1)."[".($i+1)."]", $lang),
                                                $this->xmlt,
                                                $this->keys).'">';
                    print mb_strtoupper($q[$i]);
                    print '</a>';
                    print '</li>';

                    $enterIt = $itLvl+0;
                    $enterItStr = $itStr;
                    
                    if (in_array($q[$i], $headName))
                    {
                        $itLvl++;
                        $itStr = substr($this->xmlStr, 0, strpos($this->xmlStr, "l".$itLvl)+17 );
                        $this->mobileMenIt($itLvl, $itStr, $lang, $headName);
                    }
                    
                    $itLvl = $enterIt;
                    $itStr = $enterItStr;
                    
                    if ($enterIt == $GLOBALS["xmlClientLvl"])
                    {
                        if ($i != sizeof($q) -1)
                            print '<hr class="padMen">';
                        else
                            print '<hr class="padMen trans">';
                    }
                }
            }
        }

        //--------------------------------------------------------------------------------
        
        public function printMobileMen()
        {
            $lang = $this->lang;
            $headName = array();

            // get actual level pointer and corresponing names
            $q = $this->xmlt->xml->xpath($this->xmlStr);
            while (sizeof($q) > 0)
            {
                array_push($headName, (string)$q[0]->name->$lang);
                $q = $q[0]->xpath("parent::*");
            }
            
            // get first non-client level;
            $itLvl = $GLOBALS["xmlClientLvl"];
            $itStr = substr($this->xmlStr, 0, strpos($this->xmlStr, "l".$itLvl)+17 );
            $this->mobileMenIt($itLvl, $itStr, $lang, $headName);
        }

        //--------------------------------------------------------------------------------

        public function printFooter()
        {
        	// add script for automatic generation of resolution specific resized images
        	if ($this->doResizeImgs)
        	{
        		print '<script type="text/javascript">optiImg([';
        
        		for($i=0;$i<sizeof($this->keys['resolutions']);$i++)
        		{
        			print $this->keys['resolutions'][$i];
        			if ($i!=sizeof($this->keys['resolutions'])-1) print ',';
        		}
        
        		print '], "';
        		print 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        		print ', '.$GLOBALS["resizeImgFact"].'");</script>';
        
        		// set optimized flag in database
        		if($this->setResizeImgFlag)
        		{
        			$q = $this->xmlt->xpath->query($this->xmlStr."/@optimized");
        			if ($q->length > 0) {
        				$q->item(0)->nodeValue = 1;
        				$this->xmlt->saveDOM();
        			}
        		}
        	}
        }

        
        //--------------------------------------------------------------------------------
        
        public function resizeImgs()
        {        	
        	foreach($_POST as $k => $v)
        	{
        		if ($k != "doResizeImgs" && $k!= "null")
        		{
        			$sizes = explode("_", $v);
        			$absPath = urlToServerAbsPath($k);
        			$cleanStr = str_replace(array("_gif", "_jpg", "_png"), array(".gif", ".jpg", ".png"), $absPath);
        
        			list($width, $height, $type, $attr) = getimagesize( $cleanStr );
        
        			for ($i=0;$i<sizeof($this->keys['resolutions']);$i++)
        			{
        				$prfx = implode ('', array_fill(0, $i+1, 'k'));
        				$newName = dirname($cleanStr)."/".$prfx."_".basename($cleanStr);
        
        				// das sollte im hintergrund geschehen
        				if (!file_exists($newName) || $width != $sizes[$i])
        				{
        					// run smart_resize_image as php script in background
        					$execStr = 'php -r "include_once(\'./phplib/cms/smart_resize_image.function.php\');smart_resize_image(\'';
        					$execStr .= $cleanStr.'\','.$sizes[$i].','.($height * ($sizes[$i] / $width)).',true, \''.$newName.'\', false);" > /dev/null &';
        					exec($execStr);
        					//                          smart_resize_image($cleanStr, $sizes[$i], $height * ($sizes[$i] / $width), true, $newName, false);
        				}
        			}
        		}
        	}
        
        	header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        	//            header('Location: '.$_SERVER['REDIRECT_SCRIPT_URI']);
        }
        
        //--------------------------------------------------------------------------------
        
		public function getCurXmlStr(&$keys, &$sel, $lang)
		{
			$xmlStr = "";
			$linkStr = $GLOBALS['root']."index.php?";
			
			for ($i=0;$i<$keys['nrLevels'];$i++)
			{
				if ( $sel[$i] != "" )
				{
                    $this->actLvl++;
                    
                    if ( $i>0 ) {
                        $xmlStr .= "/";
                        $linkStr .= "&";
                    }
                    $xmlStr .= "l".$i."[@short='".$sel[$i]."']";
                    $linkStr .= "l".$i."=".$sel[$i];
				}
			}
			
			return array($xmlStr, $linkStr);
		}

		//--------------------------------------------------------------------------------		

		public function getXmlOneLevelUp($xStr)
		{
			$newXmlStr = "";
			$splitStr = explode("/", $xStr);
			for ($i=0;$i<sizeof($splitStr)-1;$i++)
			{
				$newXmlStr .= $splitStr[$i];
				if ( $i < sizeof($splitStr)-2 ) $newXmlStr .= "/";
			}
			
			if ( substr($newXmlStr, -1) == "/" )
				$newXmlStr = substr( $newXmlStr, 0, strlen($newXmlStr)-1);
			
			
			return $newXmlStr;
		}
		
		//--------------------------------------------------------------------------------
		
		public function getLevelImage()
		{
			$xStr = $this->xmlStr;
			$lvl = $this->actLvl;
			$q = $this->xmlt->xml->xpath( $xStr."/@image" );
			
			while ( ( sizeof($q) == 0 || $q[0] == "" )&& ($lvl-1) >= 0 )
			{
				$xStr = $this->getXmlOneLevelUp($xStr);
				$lvl--;
				$q = $this->xmlt->xml->xpath( $xStr."/@image" );
			}
			$path = "";
			
			// check if any levels were selected
			for ($i=0;$i<$lvl;$i++) 
			{
				if ( $this->getLevel[$i] != "" )
					$path .= $this->getLevel[$i]."/";
			}
			
			$path .= $q[0];
			return $path;
		}

		//--------------------------------------------------------------------------------
		
		public function select($_str)
		{
			$this->preSel = $_str;
			$this->shiftDown = true;
		}

		//--------------------------------------------------------------------------------
		
        public function getUrl($_dLang)
        {
            if ( !$this->changedToNewUrl )
            {
                $curUrl = $this->curPageURL();
            } else {
                $curUrl = $this->curUrl;
            }

            // if mod rewrite was used replace the url here
            if ( isset($_GET['goto']) )
            {
                $curUrl = str_replace($_GET['goto'], "", $curUrl);
                $curUrl .= "index.php?";
                
                for($i=0;$i<sizeof($this->getLevel);$i++)
                {
                    if ($i!=0) $curUrl .= "&";
                    $curUrl .= "l".$i."=".$this->getLevel[$i];
                }
            }

            $ret = $curUrl;
            
            if ( strpos($curUrl, "lang=") === FALSE )
            {
                if (strpos($curUrl, "?") === false)
                {
                    $ret = $ret."?lang=".$_dLang;
                } else {
                    $ret = $ret."&lang=".$_dLang;
                }
            } else {
                $ret = str_replace('lang='.$this->lang, 'lang='.$_dLang, $ret);
            }
            
            return $ret;
        }

        //--------------------------------------------------------------------------------
        
        public function getWindowSize()
        {
        	// get browser width and height via javascript if the actual size differs from the last
        	// one saved
        	session_start();
        
        	if (isset($_SESSION['screen_width']) AND isset($_SESSION['screen_height']))
        	{
        		$ind = 0;
        		while ($_SESSION['screen_width'] < $keys['resolutions'][$ind] && $ind < sizeof($keys['resolutions']) )
        			$ind++;
        			$this->imgPrfx = implode ('', array_fill(0, $ind, 'k'));
        
        			// echo 'User resolution: '.$_SESSION['screen_width'].'  x '.$_SESSION['screen_height'];
        
        			/*
        			 // uncomment this for reseting the width and height each reload
        			 print '<script type="text/javascript">if(window.innerWidth != '.$_SESSION["screen_width"].' || window.innerHeight != '.$_SESSION["screen_height"].') { window.location = "http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?width="+window.innerWidth+"&height="+window.innerHeight; }</script>';
        			  
        			 session_unset();
        			 */
        
        	} else if (isset($_REQUEST['width']) AND isset($_REQUEST['height']))
        	{
        		$_SESSION['screen_width'] = $_REQUEST['width'];
        		$_SESSION['screen_height'] = $_REQUEST['height'];
        		// reset the url, remove the width and height get variables
        		header('Location: '.$_SERVER['SCRIPT_URI']);
        		$this->setResizeImgFlag = false;
        	} else
        	{
        		print '<script type="text/javascript">window.location = "http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?width="+window.innerWidth+"&height="+window.innerHeight;</script>';
        		$this->setResizeImgFlag = false;
        	}
        }
    }
?>