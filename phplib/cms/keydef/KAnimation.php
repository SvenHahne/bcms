<?php
        
	class KAnimation extends KeyDef
	{
        public $slideshowNr = 0;

        //-----------------------------------------------------------------

        function __construct0() { $this->init(); }

        //-----------------------------------------------------------------

        function __construct3($a1,$a2,$a3)
        {
            $this->subArgs = $a1;
            $this->subArgsKeyDef = $a2;
            $this->subArgsStdVal = $a3;
            $this->init();
        }

        //-----------------------------------------------------------------

        function __construct6($a1,$a2,$a3,$a4,$a5,$a6)
        {
            $this->args = $a1;
            $this->argsKeyDef = $a2;
            $this->argsVals = $a3;
            foreach ($this->argsVals as $val)
            {
                array_push($this->argsShow, true);
                if ( is_array($val) ) array_push($this->argsStdVal, $val[0]); else  array_push($this->argsStdVal, $val);
            }
            
            $this->subArgs = $a4;
            $this->subArgsKeyDef = $a5;
            $this->subArgsVals = $a6;
            foreach ($this->subArgsVals as $val)
            {
                array_push($this->subArgsShow, true);
                if ( is_array($val) ) array_push($this->subArgsStdVal, $val[0]); else  array_push($this->subArgsStdVal, $val);
            }
            
            $this->init();
        }

        //-----------------------------------------------------------------

		function init()
		{
			$this->xmlName = "animation";
			$this->htmlName['de'] = "Animation";
			$this->htmlName['es'] = "Animación";
			$this->htmlName['en'] = "Animation";
			$this->postId = "animation";
			$this->size = 40;
			$this->maxsize = 200;
			
			$this->fixed = false;
        }
        
        //-----------------------------------------------------------------

		function addHeadEditor(&$gPar, &$stylesPath, &$cont)
		{}
		
        //-----------------------------------------------------------------

		function addToEditor(&$gPar, &$cont) 
		{
            $temp = $gPar->post_name;            
            $df = new KEText($this, $cont, $gPar);
        }
		
        //-----------------------------------------------------------------

        function addHead(&$head, &$dPar)
		{
        	

			if ( $GLOBALS["jKeyframes"] == false )
			{
				$head['base'] .= '<script type="text/javascript" src="'.$GLOBALS['root'].'js/jquery.keyframes.min.js"></script>
				';
				$GLOBALS["jKeyframes"] = true;
			}
        	
        	$head['jqDocReady'] .= '
    $.openTopTime = 2.0;

	$(".cubeContainer").css({ 
		"width" : "100%",
		"border" : "solid 1px black", 
		"position" : "relative", 
		"margin-left" : "auto", 
		"margin-right" : "auto", 
		"perspective" : "1200px" 
	});
    $("#cube").css({
    	"border" : "0px",
    	"width" : "100%",
    	"transform-style" : "preserve-3d",
    	"transform" : "translateZ( -100px ) rotateX(-20deg)",
		"backface-visibility" : "hidden"
    });
	$("figure").css({ "position" : "absolute", "background-size" : "100% 100%" });
	$("img.schuhAni").css({ 
		"position" : "absolute",
		"background-size" : "100% 100%"
	});
    
	// function to build a dynamical resizable cube inside a container
    jQuery.buildCube = function buildCube() {
    	$.cubeContWidth = $("#cube").width();
        var relContHeight = 0.5;
        $.cubeContHeight = relContHeight * $.cubeContWidth;

        var cubeWidth = 0.4; // relative groesse
        var cubeHeight = 0.1; // relative groesse
        var cubeDepth = 0.3; // relative groesse
        var absCubeWidth = $.cubeContWidth * cubeWidth;
        var absCubeHeight = $.cubeContWidth * cubeHeight;
        $.absCubeDepth = $.cubeContWidth * cubeDepth;
        var safety = 1;
        
        $(".cubeContainer").css({ "height" : $.cubeContHeight+"px" });
        $("#cube").css({ "height" : $.cubeContHeight+"px" });
      	$("figure").css({ "background-image" : "url(pic/rahmen_an.png)", "border" : "solid 2px black" });
    	$("#cube .front").css({ 
    		"background-image" : "url(pic/rahmen_an.png)",
    		"top" : ($.cubeContHeight - absCubeHeight)+"px", "left" : ($.cubeContWidth * ((1.0 - cubeWidth) / 2))+"px",
    		"width" : absCubeWidth+"px", "height" : absCubeHeight+"px",
			"transform" : "translate3d(0,0,0)"
    	});
    	$("#cube .back").css({ 
    		"background-image" : "url(pic/rahmen_an_d.png)",
    		"top" : ($.cubeContHeight - absCubeHeight)+"px", "left" : ($.cubeContWidth * ((1.0 - cubeWidth) / 2))+"px",
    		"width" : absCubeWidth+"px", "height" : (absCubeHeight + safety)+"px",
			"transform" : "rotate(180deg) translateZ(-"+$.absCubeDepth+"px)"
    	});
    	$("#cube .left").css({ 
    		"background-image" : "url(pic/rahmen_an_d.png)",
    		"top" : ($.cubeContHeight - absCubeHeight)+"px", "left" : ($.cubeContWidth * ((1.0 - cubeDepth) / 2))+"px",
    		"width" : $.absCubeDepth+"px", "height" : absCubeHeight+"px",
			"transform" : "rotateY(90deg) translate3d("+($.absCubeDepth/2)+"px,0,"+(-absCubeWidth/2 +safety)+"px)"
    	});
    	$("#cube .right").css({ 
    		"background-image" : "url(pic/rahmen_an_d.png)",
    		"top" : ($.cubeContHeight - absCubeHeight)+"px", "left" : ($.cubeContWidth * ((1.0 - cubeDepth) / 2))+"px",
    		"width" : $.absCubeDepth+"px", "height" : absCubeHeight+"px",
			"transform" : "rotateY(-90deg) translate3d("+(-$.absCubeDepth/2)+"px,0,"+(-absCubeWidth/2)+"px)"
    	});
    	$("#cube .bottom").css({ 
    		"background-image" : "url(pic/rahmen_an_d.png)",
    		"top" : ($.cubeContHeight - $.absCubeDepth)+"px",  "left" : ($.cubeContWidth * ((1.0 - cubeWidth) / 2))+"px",
    		"width" : (absCubeWidth + safety)+"px", "height" : ($.absCubeDepth + safety)+"px",
			"transform-origin" : "50% 100%",
			"transform" : "rotatex(90deg) translate3d(0,0,0)"
    	});
    	$("#cube .top").css({ 
    		"background-image" : "url(pic/rahmen_an.png)",
    		"top" : ($.cubeContHeight - $.absCubeDepth - absCubeHeight)+"px", "left" : ($.cubeContWidth * ((1.0 - cubeWidth) / 2))+"px",
    		"width" : absCubeWidth+"px", "height" : ($.absCubeDepth +safety)+"px",
			"transform-origin" : "50% 100%",
			"animation" : "openTop 5.5s",
			"animation-fill-mode" : "forwards"
    	});
        $("#cube .front, #cube .back, #cube .left, #cube .right, #cube .bottom").css({
        	"animation-name" : "boxFadeOut", "animation-duration" : "5s", "animation-fill-mode" : "forwards"
        });

        // process item Images, should quadratically
        var nrItems = 6;
        var destImageWidthRatio = 0.2;
        $.initImageWidth = $.cubeContWidth * destImageWidthRatio;
        var destImageWidth = $.initImageWidth;
        var destImageHeight = destImageWidth;
        var destImageSpace = $.initImageWidth * $.initImageWidth * 0.6;
        var maxHeight = 0;
        
        for (var i=0;i<nrItems;i++)
        {
        	// make them optically equal in size
        	var iWidth = $("img.schuh"+(i+1)).width();
        	var iHeight = $("img.schuh"+(i+1)).height();
        	// size them according to their format
        	if (iWidth > iHeight)
        	{
        		destImageWidth = $.initImageWidth;
        		destImageHeight = iHeight * ($.initImageWidth / iWidth);
        	} else {
           		destImageWidth = iWidth * ($.initImageWidth / iHeight);
            	destImageHeight = $.initImageWidth;
        	}
        	// scale them to occupy the destinationspace
        	var actSpace = destImageWidth * destImageHeight;

        	var scaleRatio = destImageSpace / actSpace;
        	destImageWidth *= scaleRatio;
        	destImageHeight *= scaleRatio;
        	if ( destImageHeight > maxHeight ) 
        		maxHeight = destImageHeight;
        	
        	$("img.schuh"+(i+1)).css({
        		"width" : destImageWidth+"px",
        		"height" : destImageHeight+"px",
            	"opacity" : "0.0",
    			"animation" : "schuh"+(i+1)+"In 3s ease-out",
    			"animation-fill-mode" : "forwards",
    			"animation-delay" : "0.5s"
            });
        }
        
        console.log("height: "+$(".cubeContainer").position().top);
        
    	$("div.menuBlackBack").css({
    		"position" : "absolute",
    		"top" : $(".cubeContainer").position().top+"px",
    		"left" : "0",
    		"width" : "100%",
    		"background-color" : "rgba(0.0, 0.0, 0.0, 1.0)",
    		"height" : (maxHeight)+"px",
    		"animation" : "menuBlackBackFade 2s",
    		"animation-fill-mode" : "forwards",
    		"animation-delay" : $.openTopTime+"s"
    	});
    };
    
    // do it!
    $.buildCube();
    
    // animation keyframes
    var keyFrames = [{
    	name: "boxFadeOut",
		"0%" : { "opacity" : "1.0" },
    	"50%" : { "opacity" : "1.0" },      
    	"100%" : { "opacity" : "0.0" }      
    }, {
        name: "menuBlackBackFade",
    	"0%" : { "background-color" : "rgba(0, 0, 0, 0.0)" },
        "100%" : { "background-color" : "rgba(0, 0, 0, 1.0)" }     		
    }, {
    	name: "openTop",
		"0%" : { "transform" : "translate3d(0, 0px,"+(-$.absCubeDepth)+"px) rotatex(-90deg)" },
		"50%" : { "transform" : "translate3d(0, 0px,"+(-$.absCubeDepth)+"px) rotatex(60deg)", "opacity" : "1.0" },
		"100%" : { "transform" : "translate3d(0, 0px,"+(-$.absCubeDepth)+"px) rotatex(60deg)", "opacity" : "0.0"  }
    }];
    
    var nrItems = 6;
    for (var i=0;i<nrItems;i++)
    {
    	keyFrames.push({
        	name: "schuh"+(i+1)+"In",
        	"0%" : { "opacity" : "0.0", "left" : ((0.5 - $.initImageWidth / 2 / $.cubeContWidth) * 100)+"%", "top" : ((1.0 - ($.initImageWidth / $.cubeContHeight)) * 100)+"%",
        		"transform" : "translate3d(0, 0, "+($.cubeContWidth * -0.3)+"px) rotateZ("+(i * -60)+"deg)" },
        	"20%" : { "opacity" : "1.0" },
        	"100%" : { "opacity" : "1.0", "left" : (i / (nrItems -1) * (1.0 - ($.initImageWidth / $.cubeContWidth / 2)) * 100)+"%",
        		"top" : ( ( ($("img.schuh"+(i+1)).height() / $.cubeContHeight / -2)  + ($.initImageWidth / $.cubeContHeight / 2.5) ) * 100)+"%",
				"transform" : "translate3d(0, 0, rotateZ(360deg)" }
    	});
    }


    $.keyframe.define(keyFrames);
      ';
    
    		// register cube function with window resize
    		$head['jqWinResize'] .= '
    			    $.buildCube();
    				';
		}
		
        //-----------------------------------------------------------------
        
		function draw(&$head, &$body, &$xmlItem, &$dPar)
		{  
			 $body .= '	
			 <div class="menuBlackBack"></div>
				<section class="cubeContainer">
					<div id="cube" class="spinning">
			 	 	    <figure class="front"></figure>
			 	 	 	<figure class="back"></figure>
			 			<figure class="right"></figure>
			 			<figure class="left"></figure>
		     			<figure class="top"></figure>
			 			<figure class="bottom"></figure>
			 	 	 </div>';
			 
			 for ($i=0;$i<6;$i++){
				 $body .= '<img class="schuhAni schuh'.($i+1).'" src="pic/schuh'.($i+1).'.png"/>';
			 }
	 	 	 	 	
			 $body .= '</section>
			 ';
		}
	}
?>