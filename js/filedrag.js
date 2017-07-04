(function FileDrag() {
	
	var updateInfoDrawn = false;
 
    function showUpdateInfo()
    {
         var outerDiv = document.createElement('div');
         var innerDiv = document.createElement('div');
         innerDiv.style.display="table-cell";
         innerDiv.style.verticalAlign = "middle";
         innerDiv.style.textAlign = "center";
         innerDiv.innerHTML = "PLEASE WAIT, <br>GENERATING SCALED IMAGES";
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
 
        updateInfoDrawn = true;
    }
 

	// getElementById
	function $id(id) {
		return document.getElementsByClassName(id);
	}

	// output information
	function Output(msg) 
	{
		var m = $id("messages");
		m.innerHTML = msg + m.innerHTML;
	}


	// file drag hover
	function FileDragHover(e) 
	{
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}


	// file selection
	function FileSelectHandler(e) 
	{
		var name = this.name.replace("[]", "");
	
		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;
  
		UploadFile(files, name, this);
	}


	// output file information
	function ParseFile(file) 
	{
		Output(
			"<p>File information: <strong>" + file.name +
			"</strong> type: <strong>" + file.type +
			"</strong> size: <strong>" + file.size +
			"</strong> bytes</p>"
		);

		// display an image
		if (file.type.indexOf("image") == 0) {
			var reader = new FileReader();
			reader.onload = function(e) {
				Output( "<p><strong>" + file.name + ":</strong><br />" );
			}
			reader.readAsDataURL(file);
		}

		// display text
		if (file.type.indexOf("text") == 0) {
			var reader = new FileReader();
			reader.onload = function(e) {
				Output(
					"<p><strong>" + file.name + ":</strong></p><pre>" +
					e.target.result.replace(/</g, "&lt;").replace(/>/g, "&gt;") +
					"</pre>"
				);
			}
			reader.readAsText(file);
		}
	}


	// upload JPEG files
	function UploadFile(_files, postid, obj) 
	{
		var files = _files;
 
		console.log("UploadFile: "+_files);

		
        // following line is not necessary: prevents running on SitePoint servers
		//if (location.host.indexOf("sitepointstatic") >= 0) return

		var xhr = new XMLHttpRequest();
 
        // geh den nodebaum solange hoch, bis das Form tag kommt
        var thisForm = obj;
        while (thisForm.nodeName != "FORM") {
            thisForm = thisForm.parentNode;
        }

		// hol dir die ganze formulardaten
		var formData = new FormData(thisForm);
 
		if (xhr.upload) {
			// wenn die progressbar noch nicht da ist, mach eine
			var barWidth = 200;
	
			// create progress bar
			var contDiv = document.createElement("div");
			contDiv.style.display = "block";
			contDiv.style.position = "relative";
			contDiv.style.width = barWidth+"px";
			contDiv.style.height = "20px"
			contDiv.style.padding = "0px"
			contDiv.style.margin = "2px 0";
			contDiv.style.border = "1px inset #446";
			contDiv.style.borderRadius = "5px";
			contDiv.style.background = "#eee";
			contDiv.id = "progressbar";
			 
			var bar = document.createElement("div");
			bar.style.display = "block";
			bar.style.width = "0px";
			bar.style.height = "20px"
			bar.style.padding = "0px"
			bar.style.margin = "0px";
			bar.style.border = "0px";
			bar.style.borderRadius = "5px";
			bar.style.background = "#0f0";
			bar.id = "progress";
			 
			contDiv.appendChild(bar);
			 
			var textDiv = document.createElement("div");
			var textNode = document.createTextNode("0%");

			textDiv.id = "pbFileName";
			 
			textDiv.style.display = "block";
			textDiv.style.position = "absolute";
			textDiv.style.left = "2px";
			textDiv.style.top = "2px";
			 
			textDiv.appendChild(textNode);
			contDiv.appendChild(textDiv);
			 
			obj.parentNode.insertBefore(contDiv, obj.nextSibling);
 

			// progress bar
			xhr.upload.addEventListener("progress", function(e) {
                                        
				var bar = document.getElementById("progress");
				bar.style.width = (e.loaded / e.total * 200)+"px";

				var text = document.getElementById("pbFileName");
				text.innerHTML = parseInt(e.loaded / e.total * 100)+" %";
                
                var type = files[0].type.split("/")[0];
                if(e.loaded / e.total > 0.96 && updateInfoDrawn == false
                   && type != "video")
                {
                    showUpdateInfo();
                }
			}, false);
 
			// file received/failed
			xhr.onreadystatechange = function(e) 
			{
				console.log("readyState: "+xhr.readyState+" xhr.status: "+xhr.status);
				
				if (xhr.readyState == 4 && xhr.status == 200)
				{
					var bar = document.getElementById("progress");
					bar.style.width = "200px";
					
					var text = document.getElementById("pbFileName");
					text.innerHTML = "100 %";

					console.log("hochgeladen");
				}
			};

			xhr.onload = function () {
				  // Request finished. Do processing here.
				console.log("request finished?");
	            
                // reload page
               // location.reload(true); // forceGet parameter, if not true, site is loaded from cache
               window.location=window.location;
			};

				
			// start upload
            xhr.open("POST", thisForm.action, true);
			formData.append(postid, "hochladen");
			
			console.log("send form data");
            xhr.send(formData);
		}
	}

	// initialize, schmeisst den submit button weg
	function Init() 
	{
		// müssen noch angepasst werden
		var fileselect = $id("fileselect"),
			filedrag = $id("filedrag");

		for (var i=0;i<fileselect.length;i++)
		{
			// file select
			if('null' != fileselect[i].value )
			fileselect[i].addEventListener("change", FileSelectHandler, false);
		}
		
		// is XHR2 available?
		var xhr = new XMLHttpRequest();
		if (xhr.upload) {
 /*	
			// file drop
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";
  */
		}
	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}
})()
