<?php
		
	class KLink extends KeyDef
	{
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
        
		function init()
		{
			$this->xmlName = "link";
			$this->htmlName['de'] = "Link";
			$this->htmlName['es'] = "Link";
			$this->htmlName['en'] = "Link";
			$this->type = "link";
			$this->postId = "link";
			$this->size = 58;
			$this->maxsize = 200;
			$this->uploadPath = "downloads";
			$this->isUplType = TRUE;
		}
		
		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{
            if ( !$GLOBALS["filedrag"] )
            {
                $cont['footer'] .= '<script src="'.$GLOBALS['root'].'js/filedrag.js" type="text/javascript"></script>
                ';
                $GLOBALS["filedrag"] = true;
            }
        }
		
        
		function addToEditor(&$gPar, &$cont) 
		{
			$gPar->hasThumb = true;
			$df = new KESingleUpload($this, $cont, $gPar);
		}
        
		function addHead(&$head, &$dPar)
        {
        	parent::addHead($head, $dPar);

            if(isset($dPar->args['style']) && $dPar->args['style'] == "margLeft6")
            {
                if ( $GLOBALS["imgLiquid"] == false )
                {
                    $head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/imgLiquid.js"></script>
                    ';
                    $GLOBALS["imgLiquid"] = true;
                }
                
                if ( $GLOBALS["image"] == false )
                {
                    $head['jqDocReady'] .= '$(".imgLiquidFill").imgLiquid({fill: true, horizontalAlign: "center", verticalAlign: "40%"});';
                    $GLOBALS["image"] = true;
                }
            }
        }
        
        function draw(&$head, &$body, &$xmlItem, &$dPar)
        {
            // parse link
            $link = $xmlItem."";

            // wenn es sich um keine url, oder ein verweis auf eine php oder html datei handelt
            if (strpos($link, "http") === false && strpos($link, ".php") === false
                && strpos($link, ".html") === false && strpos($link, "search") !== false )
            {
                $link = $GLOBALS['root']."index.php?".$link."&lang=".$dPar->lang;
                $link .= "&isLink&searchStrict&from_url=".$dPar->curUrl;
            }
            
            // div dass über dem div mit dem image liegt.
            // bei hover wird auf das nachfolgenden div element zugegriffen und hier die farbe geändert
            if ($dPar->args['style'] == "gridImg" || $dPar->args['style'] == "gridImgS" || $dPar->args['style'] == "gridImgXS"
                || $dPar->args['style'] == "gridImgOvr" || $dPar->args['style'] == "gridImgB"
                || $dPar->args['style'] == "fullSlim"
                || $dPar->args['style'] == "gridFullVisible"
                )
            {
                // sollte das vielleicht auch ohne rewrite passieren?
                $body .= '<a class="mason_over" href="'.$link.'" target="_'.$xmlItem['target'].'">';
                $body .= mb_strtoupper($xmlItem->attributes()['text']);
                $body .= '<hr class="white">';
                $body .= mb_strtoupper($xmlItem->attributes()['subtext']);
                $body .= '</a>';
            }
            
			$url = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$xmlItem['image'];
            $link = $GLOBALS['root'].$GLOBALS['backupUrl'].$dPar->clientFolder.$this->uploadPath."/".$dPar->linkPath.$dPar->subLinkPath.$link;

            // print image thumb
            if ($dPar->args['style'] == "gridImg" || $dPar->args['style'] == "gridImgXS" || $dPar->args['style'] == "gridImgS" || $dPar->args['style'] == "gridImgB")
            {
                $body .= '<div class="'.$dPar->args['style'].'" style="background-image: url('.$url.');">';
				$body .= '</div>';
                
            } else if($dPar->args['style'] == "fullSlim" || $dPar->args['style'] == "gridImgOvr" )
            {
                $body .= '<div class="'.$dPar->args['style'];
                $body .= '"><span class="img_helper"></span><img class="'.$dPar->args['style'].'" src="'.$url.'" /></div>';

            } else if($dPar->args['style'] == "margLeft6")
            {
            	/*
                $body .= '<a href="'.$link.'" target="'.$xmlItem['target'].'">';
                $body .= '<div class="imgLiquidFill linkContF">';
                    $body .= '<div class="linkContFOTextCont">';
                        $body .= '<div class="linkContFOText">'.$xmlItem['text'].'</div>';
                        $body .= '<span class="img_helper"></span>';
                        $body .= '<img class="" src="'.$url.'" />';
                    $body .= '</div>';
                $body .= '</div>';
                $body .= '</a>';
*/
            } else if($dPar->args['style'] == "gridFullVisible")
            {
                $body .= '<div class="'.$dPar->args['style'].'" style="background-image: url('.$url.');">';
                $body .= '</div>';
                
            } else if($dPar->args['style'] == "gridInfoBelow")
            {
                $body .= '<div class="'.$dPar->args['style'].'">';
                
                $body .= '<a class="'.$dPar->args['style'].'" href="'.$link.'" target="_'.$xmlItem['target'].'">';
   
                $body .= '<div class="'.$dPar->args['style'].'Pic" style="background-image: url('.$url.');"></div>';
                $body .= '<div class="'.$dPar->args['style'].'Text">';
                $body .= mb_strtoupper($xmlItem->attributes()['text']);
                
                if($xmlItem->attributes()['subtext'] != "")
                {
                    $body .= '<hr class="white">';
                    $body .= mb_strtoupper($xmlItem->attributes()['subtext']);
                }
                
                $body .= '</div></a>';
                
                $body .= '</div>';
                
            } else
            {
                $body .= '<a href="'.rewriteLink($link, $dPar->xt, $dPar->keys).'" target="'.$xmlItem['target'].'">';

                $body .= '<div class="'.$dPar->args['style'].'"><span class="img_helper"></span><img class="'.$dPar->args['style'].'" src="'.$url.'" /></div>';
                $body .= '<div class="'.$dPar->args['style'].'Text'.'">'.$xmlItem['text'].'<br>'.$xmlItem['subtext'].'</div>';

                $body .= '</a>';
            }
        }
	}

?>