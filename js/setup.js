$(document).ready(function() {
				  var browserWidth = $(window).width();
				  				  
				  // gallery
				  var galleryPicWidth, galleryPicHeight;
				  
				  if ( browserWidth < 500 ) {
					galleryPicWidth = 80;
				  } else if ( browserWidth >= 500 && browserWidth < 1500 ) {
					galleryPicWidth = 110;
				  } else {
					galleryPicWidth = 200;
				  }
				  galleryPicHeight = galleryPicWidth;
				  
				  // lade bilder für die bilder gallerien
				  $(this).find('a').each(function(index, value) {
										 if ( value.id == "a_gallery" )
										 {
											var subDiv = value.firstChild;
											var rw = subDiv.style.width.split("px")[0];
											var rh = subDiv.style.height.split("px")[0];
										 
											var img = document.createElement("img");
											subDiv.style.width = galleryPicWidth+"px";
											subDiv.style.height = galleryPicHeight+"px";
											subDiv.style.display = "inline-block";
											subDiv.style.overflow = "hidden";
											img.setAttribute( "src", value );

											if ( (rw / rh) > 1.0 ) 
											{
												img.style.height = galleryPicHeight+"px";
												img.style.marginLeft = Math.floor( (galleryPicWidth - rw * galleryPicHeight / rh ) * 0.5 
																				  )+"px";
											} else {
												img.style.width = galleryPicWidth+"px";
												img.style.marginTop = Math.floor( (galleryPicHeight - rh * galleryPicWidth / rw ) * 0.25 
																				 )+"px";
											}
											subDiv.appendChild( img );										 
										 }
										 });

				  
				  $(function() {
					$('#gallery a').lightBox();
					});
				  
				  })