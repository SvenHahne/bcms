var actRes = 0;
var gSizes;
var gResizeFact = 0.7;
var imgLoadCount = 0;
var origImgPar = [];
var allImgsOnSite = [];
var allImgData = [];
var gReDirUrl = "";

//---------------------------------------------------------------//
//           lazy loading and dynamic resolution changing        //
//---------------------------------------------------------------//

function lazyLoad(sizes)
{	
    allImgsOnSite = document.getElementsByTagName("IMG");
    imgLoadCount = allImgsOnSite.length;

    for (var i=0;i<allImgsOnSite.length;i++)
    {
        allImgsOnSite[i].onload = onloadHandler;
        //console.log(allImgsOnSite[i].getAttribute("data-src"));
        allImgData.push( [allImgsOnSite[i].getAttribute("data-src"), "", ""] );
                
        // get the filename and dirname of the data-src
        if (allImgsOnSite[i].getAttribute("data-src")!== null)
        {
            allImgData[i][1] = allImgsOnSite[i].getAttribute("data-src").replace(/^.*[\\\/]/, '');
            allImgData[i][2] = allImgsOnSite[i].getAttribute("data-src").substring(0, allImgsOnSite[i].getAttribute("data-src").lastIndexOf("\/")+1);
        }
    }
    
    if (typeof sizes !== 'undefined') gSizes = sizes;
   
    //       alert("lazyLoade");	
	checkImgVisible();

    document.onscroll = function(){ checkImgVisible(); }
    document.body.onresize = function(){ checkImgVisible(); }
      
    return false;
}


function onloadHandler()
{	
    // optionally: "this" contains current image just loaded
    imgLoadCount--;
        
    if (imgLoadCount === 0 || allImgsLoaded)
        checkImgVisible();
    
    return false;
}


function isElementInViewport (el)
{
    if (typeof jQuery === "function" && el instanceof jQuery) {
        el = el[0];
    }
    
    var rect = el.getBoundingClientRect();

//	console.log("window.innerHeight: "+window.innerHeight+" window.innerWidth"+window.innerWidth);
//	console.log(rect);
    
    return (
            (0 < rect.left < (window.innerWidth || document.documentElement.clientWidth))
            || (0 < rect.right < (window.innerWidth || document.documentElement.clientWidth))
            || (0 < rect.top < (window.innerHeight || document.documentElement.clientHeight))
            || (0 < rect.bottom < (window.innerHeight || document.documentElement.clientHeight))
            );
            
//    return (
//            rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
//            rect.left > 0 &&
//            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
//            rect.right < (window.innerWidth || document.documentElement.clientWidth)
//            );
}


function checkImgVisible()
{	
    // get the prefix for the actual size
    var ind = 0;
    var imgPrfx = "";
    var resImgPrfx = "";
    var elementsToDelete = [];
    
    for (var i=0;i<allImgsOnSite.length;i++)
    {    
        // if the img has a data-src attribute and is visible
        if (allImgData[i] != null && allImgData[i][0] !== null && isElementInViewport(allImgsOnSite[i]))
        {
	   		 // get the size on screen of the image
	    	// check to which prefix it corresponds
	    	ind = 0;
	    	imgPrfx = "";
	    	while (allImgsOnSite[i].width < (gSizes[ind] * gResizeFact) && ind < gSizes.length)
	    		ind++;

	   		for (var j=0;j<ind;j++) imgPrfx += "k";
	    	    
            // get the prefix of the actual src
            var srcFileName = allImgsOnSite[i].src.replace(/^.*[\\\/]/, '');
            var srcPrfx = srcFileName.split("_");

            if (srcPrfx.length > 0)
            {
                srcPrfx = srcPrfx[0];
                srcPrfx = srcPrfx.replace(/[^k]+/, '');
            } else {
                srcPrfx = "";
            }

            if (srcFileName == "placeholder.png")
            {
                if (imgPrfx != "") resImgPrfx = imgPrfx+"_";

                // if there is the initial placeholder, set a new src
                allImgsOnSite[i].src = allImgData[i][2]+resImgPrfx+allImgData[i][1];
				allImgsOnSite[i].removeAttribute("style"); // remove initial style tag
		
                allImgsOnSite[i].style.width = allImgsOnSite[i].getAttribute("data-width");
                allImgsOnSite[i].style.height = allImgsOnSite[i].getAttribute("data-height");
                allImgsOnSite[i].style.marginTop = "-"+allImgsOnSite[i].getAttribute("data-margin-top")+"%";
                allImgsOnSite[i].style.marginLeft = "-"+allImgsOnSite[i].getAttribute("data-margin-left")+"%";
                
                // error handler, if image invalid
				if ( allImgData[i][1] != "") {
                    imgLoadErrorHandler(allImgsOnSite[i], allImgData[i][2]+allImgData[i][1]);
				}
            } else if(srcPrfx != "" && srcPrfx != imgPrfx)
            {
				// if the screen size has change and a img with higher resolution is needed
                // load it!
                if (imgPrfx != "") resImgPrfx = imgPrfx+"_";
                allImgsOnSite[i].src = allImgData[i][2]+resImgPrfx+allImgData[i][1];
				if ( allImgData[i][1] != "" ) {
                    imgLoadErrorHandler(allImgsOnSite[i], allImgData[i][2]+allImgData[i][1]);
				}
            }
        }
    }
    
    // delete all replaced imgs
    for (var i=0;i<elementsToDelete.length;i++)
    {
        elementsToDelete[i].parentNode.removeChild(elementsToDelete[i]);
    }

    return false;
}


// if the resize process is still working and the image doesn´t load,
// load the original instead
function imgLoadErrorHandler(img, altPath)
{
    img.onerror = function()
    {
        img.src = altPath;
    };
    
    return false;
}


//---------------------------------------------------------------//
//              generate downscaled images                       //
//---------------------------------------------------------------//

var allImgsLoaded = false;

function showUpdateInfo()
{
    var outerDiv = document.createElement('div');
    var innerDiv = document.createElement('div');
    innerDiv.style.display="table-cell";
    innerDiv.style.verticalAlign = "middle";
    innerDiv.style.textAlign = "center";
    innerDiv.innerHTML = "PLEASE WAIT, <br>UPDATING IMAGES";
    innerDiv.style.fontFamily = "KelsonSansRegular, Georgia, serif";
    innerDiv.style.color = "#009ee3";
    
    outerDiv.style.position = "fixed";
    outerDiv.style.display = "table";
    outerDiv.style.top = ((window.innerHeight -200) / 2)+"px";
    outerDiv.style.left = ((window.innerWidth -200) / 2)+"px";
    outerDiv.style.zIndex = "1000";
    outerDiv.style.width = "200px";
    outerDiv.style.height = "200px";
    outerDiv.style.border = "solid 4px red";
    outerDiv.style.borderRadius = "10px";
    outerDiv.style.background = "#FFF";
    outerDiv.appendChild(innerDiv);
    document.body.appendChild(outerDiv);
}


function changeRes()
{
    // set new html tag width
    document.body.style.width = gSizes[actRes]+'px';
    
    if (!allImgsLoaded)
    {
        // get all imgs and set new src to force reload
        imgLoadCount = allImgsOnSite.length;
        
        //console.log("imgLoadCount: "+imgLoadCount);

        for (var i=0;i<allImgsOnSite.length;i++)
        {
            allImgsOnSite[i].src = "pic/placeholder.png";
            allImgsOnSite[i].onload = onloadHandlerResize;
        }
        
    } else {
        onloadHandlerResize();
    }
    
    return false;
}


function onloadHandlerResize()
{
    // optionally: "this" contains current image just loaded
    if (!allImgsLoaded) imgLoadCount--;
    
    if (imgLoadCount === 0 || allImgsLoaded)
    {
        allImgsLoaded = true;
        imgLoadCount = allImgsOnSite.length;

        // reapply masonry -> most time consuming
        $("#mason-cont").masonry();
        
        // go through all images, get the rendered size, multiply a bit to have some safety
        for (var i=0;i<allImgsOnSite.length;i++){
            origImgPar[i].push(Math.floor(allImgsOnSite[i].clientWidth * 1.1));
         //   console.log(Math.floor(allImgsOnSite[i].clientWidth * 100));
        }
        
        actRes++;

        if (actRes < gSizes.length)
        {
            changeRes();
        } else
        {
            // build form with all parameters and reload the site
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", gReDirUrl);
            form.setAttribute("name", "resize");
            form.setAttribute("enctype", "multipart/form-data");
            form.setAttribute("accept-charset", "utf-8");

            var nameField = document.createElement("input");
            nameField.setAttribute("type", "hidden");
            nameField.setAttribute("name", "doResizeImgs");
            form.appendChild(nameField);

            for (var i=0;i<allImgsOnSite.length;i++)
            {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", allImgsOnSite[i].getAttribute("data-src"));
                var val = "";
                for (var j=0;j<gSizes.length;j++)
                {
                    val += origImgPar[i][j+1];
                    if(j!=gSizes.length-1) val += "_";
                }
                
                hiddenField.setAttribute("value", val);
                form.appendChild(hiddenField);
            }

            document.body.appendChild(form);
            form.submit();
        }
    }
    
    return false;
}


// start function
function optiImg(sizes, reDirUrl, resizeImgFact)
{
    if (typeof sizes !== 'undefined')
    {
        gSizes = sizes;
        gReDirUrl = reDirUrl;
		gResizeFact = resizeImgFact;

        // get all images, save original paths
        allImgsOnSite = document.getElementsByTagName("IMG");
        for (var i=0;i<allImgsOnSite.length;i++)
        {
            allImgsOnSite[i].src = "";
//	    allImgsOnSite[i].removeAttribute("style");
	   		origImgPar.push([allImgsOnSite[i].getAttribute("data-src")]);
        }

        showUpdateInfo();
        
        // start the changeRes Callback Loop
        changeRes();
    }
    
    return false;
}
