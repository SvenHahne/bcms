<?php
    
	class KRssfeed extends KeyDef
	{
        protected $rssEdit;
        
        function __construct0() { $this->init(); }
        
        function __construct3($a1,$a2,$a3)
        {
            $this->args = $a1;
            $this->argsKeyDef = $a2;
            $this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
            $this->init();
        }
        
        function __construct4($a1,$a2,$a3,$a4)
        {
            $this->args = $a1;
            $this->argsKeyDef = $a2;
            $this->argsVals = $a3;
            $this->argsStdVal = $a4;
            foreach ($this->argsVals as $val)
                array_push($this->argsShow, true);
            $this->init();
        }
        
		public function init()
		{
			$this->xmlName = "rssfeed";
			$this->htmlName['de'] = "Rss-Feed";
			$this->htmlName['es'] = "Rss-Feed";
			$this->htmlName['en'] = "Rss-Feed";
			$this->type = "text";
			$this->postId = "rssfeed";
            $this->isMultMasonItems = TRUE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
		function addToEditor(&$gPar, &$cont) 
		{
            $rssStreams = array();
            
            // get all internal rss feeds
            if ($handle = opendir('./rss-feeds'))
            {
                while ( false !== ( $file = readdir($handle) ) )
                {
                    $pi = pathinfo( $file );
                    if (isset($pi['extension']) && $pi['extension'] == "rss")
                        array_push($rssStreams, $pi['basename']);
                }
                closedir($handle);
            }
            
            
            // draw the DropDown Menu with all internal rss feeds
            $cont['body'] .= "<div style='width:60px;display:inline-block;'>Stream:</div>";
            
            $gPar->drawTake = false;
            $dMen = new KEDropDown($this, $cont, $gPar, $rssStreams);
            
            $cont['body'] .= "<br>";
            
            $pName = $gPar->post_name;
            $val = $gPar->val;
            
            // draw the arguments
            for ($k=0;$k<(sizeof($this->args) / 3);$k++)
            {
                for ($l=0;$l<min(sizeof($this->args) - $k*3, 3);$l++)
                {
                    $ind = $k*3 + $l;
                    
                    // wenn es sich nicht um einen verweis auf ein Bild handelt, blende hier
                    // das "image" als argument aus, dass hierfür ein eigenes Feld gebaut wird
                    $cont['body'] .=  "<div style='width:60px;display:inline-block;'>".$this->args[$ind].":</div>";
                        
                    if ( sizeof($this->argsKeyDef) != 0 && $this->argsKeyDef[$ind] != "" )
                    {
                        // schmutzig, name wird als referenz übergeben und muss später wieder korrigiert werden...
                        $gPar->post_name = $pName."_@".$this->args[$ind];
                        $gPar->drawTake = false;
                        $gPar->val = $val[ $this->args[$ind] ];
                        $aKey = new $this->argsKeyDef[$ind]($this, $cont, $gPar, $this->argsVals[$ind]);
                        
                    } else {
                        $cont['body'] .=  "<input class='inp2' name='change_".$pName."_@".$this->args[$ind]."' type='text' ";
                        $cont['body'] .=  "size='30' maxlength='200' value='".$val[ $this->args[$ind] ]."'>";
                    }
                    
                    if (sizeof($this->argsKeyDef) > 0 && $this->argsKeyDef[$ind]."" != "KEButton" )
                        $cont['body'] .=  "<br>"; // platzhalter";
                }
            }
            
            $gPar->post_name = $pName;
            $gPar->val = $val;
        }
        
        function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            // load the rss file and init the editor class
            $this->rssEdit = new BEditorRss($rss, $dPar->lang, null);
            $readMoreText = array();
            $readMoreText['de'] = "Lesen Sie mehr.";
            $readMoreText['es'] = "Leer más.";
            $readMoreText['en'] = "Read More.";
            
            // open the rss stream
            if ( $xmlItem[0] != "" )
                $this->rssEdit->setRssUrl($xmlItem[0]);

            $list = $this->rssEdit->getItems();
            
           // $body .= '<div id="mIt_0" class="masonry item col10_row1">';


//            if ($xmlItem['style'] == "norm_10col")
//            {
                for ($i=0;$i<sizeof($list);$i++)
                {
                    // print date and title
                    $body .= "<div class='masonry item rss_col_date'>"; // 2

                    $oDate = strtotime($list[$i]['pubDate']);
                    $newDate = date("d/m/Y", $oDate);
                    
                    $body .= "<div class='rssDate'>".$newDate."</div>";
                    $body .= "<h3 class='rssNorm10'>".mb_strtoupper($list[$i]['title'])."</h3>";
                    $body .= "</div>";
                    

                    // print image
                    $body .= "<div class='masonry item rss_col_img'>"; // 2
                    $body .= "<div class='rssImgCont'>";
                    $body .= "<img class='rssImg' src='".$list[$i]['image']['url']."'>";                    
                    $body .= "</div>";
                    $body .= "</div>";
                    
                    // print text
                    $body .= "<div class='masonry item rss_col_desc'>"; // 6
                    $body .= "<div class='rssFliesText'>".$list[$i]['description'];
                    $body .= "<br><a href='".rewriteLink($list[$i]['link'], $dPar->xt, $dPar->keys)."' target='_new' class='blue'>".$readMoreText[$dPar->lang]."</a></div>";
                    $body .= "</div>";

                    // separation line
                    if ($i <= sizeof($list) -2) 
                    	$body .= "<div class='masonry item col10_row033'><div style='display:table;width:100%;height:100%;'><div style='display:table-cell;width:100%;height:100%;vertical-align:middle;'><hr class='rssFeed'></div></div></div>";
                }
            //}

            //$body .= '</div>';
        }
	}

?>