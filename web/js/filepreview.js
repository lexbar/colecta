/*!
 * == HTML5 Image Preview ==
 * Created By: Tomas Dostal
 * Version: 1.0 (05-12-2012)
 * Documentation: http://tomasdostal.com/projects/html5ImagePreview
 *
 * HTML structure:
 *	<div>
 *		<input type="file" name="imagefile" onchange="previewImage(this,{256,128,64},5)">
 *		<div class="imagePreview"></div>
 *	</div>
 *
 */
function previewImage(el,width,limit){
    if(typeof FileReader !== 'function' ) return;
    
    $('#fileLoading').removeClass('hidden');
    
	var files = el.files;
	var wrap = el.parentNode;
	var output = wrap.getElementsByClassName('imagePreview')[0];

    var imageType = /image.*/;
    var device = detectDevice();
    
    var file
    
    output.innerHTML = '';
    
    var processed = files.length;
    var thumbnails = totalsize = 0;
    
    for(var n = 0; n < files.length; n++) {
    	file = files[n];
    
    	// detect device
    	if (!device.android){ // Since android doesn't handle file types right, do not do this check for phones
    		if (!file.type.match(imageType)) {
    			output.innerHTML='';
    			break;
    		}
    	}
    
    	var img='';
    
    	var reader = new FileReader();
    	reader.onload = (function(aImg) {
    		return function(e) {
    			var mimeformat = e.target.result.split(';');
    			alert(e.target.result);
    			mimeformat = mimeformat[0]
    			var format = mimeformat.split('/');
    			format = format[1].toUpperCase();
    
    			// We will change this for an android
    			if (device.android){
    				format = file.name.split('.');
    				format = format[format.length-1].toUpperCase();
    			}
                if(e.total>(limit*1024*1024)) {
                    //output.innerHTML='<p>El archivo es demasiado grande!</p>';
                    //return;
                } else if (((format=='JPG')||(format=='JPEG')||(format=='PNG')||(format=='GIF'))){
					var image = document.createElement('img');
					var src = e.target.result;

					// very nasty hack for android
					// This actually injects a small string with format into a temp image.
					if (device.android){
						src = src.split(':');
						if (src[1].substr(0,4) == 'base'){
							src = src[0] + ':image/'+format.toLowerCase()+';'+src[1];
						}
					}
                    
					image.src = src;
					image.title = 'Vista previa';
					
					var li = document.createElement('li');
					li.className = 'span2';
					
					var div = document.createElement('div');
					div.className = 'thumbnail'
					
					var text = document.createElement('div');
					text.className = 'caption'
                    text.innerHTML = '<strong><small>(Vista previa)</small></strong>';
                    
                    var fit = document.createElement('div');
                    fit.style.height = (width - 20) + 'px';
                    fit.className = 'fit';
                    
                    fit.appendChild(image);
                    div.appendChild(fit);
					div.appendChild(text);
					
					li.appendChild(div);
					
					output.appendChild(li);
					
					thumbnails++;
    			} else {
                    //icon on mime type
                    var formaticon = 'icon-file';
                    if(mimeformat.match(/image\//)) {
                        formaticon = 'icon-picture';
                    } else if(mimeformat.match(/application\//)) {
                        formaticon = 'icon-file-alt';
                    } else if(mimeformat.match(/video\//)) {
                        formaticon = 'icon-facetime-video';
                    } else if(mimeformat.match(/audio\//)) {
                        formaticon = 'icon-music';
                    }
                    
                    var img = document.createElement('i');
                    img.className = formaticon;
                    
                    var li = document.createElement('li');
					li.className = 'span2';
					
					var div = document.createElement('div');
					div.className = 'thumbnail'
					
					var text = document.createElement('div');
					text.className = 'caption'
                    text.innerHTML = '<strong><small>(archivo)</small></strong>';
                    
                    var fit = document.createElement('div');
                    fit.style.height = (width - 20) + 'px';
                    fit.className = 'fit';
                    
                    fit.innerHTML = '<i class="'+formaticon+'"></i><br>';
                    div.appendChild(fit);
					div.appendChild(text);
					
					li.appendChild(div);
					
					output.appendChild(li);
    			}
    			
    			totalsize += e.total;
    			
    			processed--;
    			
    			if(processed == 0) { //last iteration
                    if(thumbnails > 2) {
                        output.style.margin = '10px 0 0 -120px';
                    } else if(thumbnails > 0) {
                        output.style.margin = '10px 0 0 -20px';
                    } else {
                        output.style.margin = '0';
                    }
                    
                    $('#fileLoading').addClass('hidden');
    			}
    		};
    	})(img);
    	reader.readAsDataURL(file);
    }    
}

// Detect client's device
function detectDevice(){
	var ua = navigator.userAgent;
	var brand = {
		apple: ua.match(/(iPhone|iPod|iPad)/),
		blackberry: ua.match(/BlackBerry/),
		android: ua.match(/Android/),
		microsoft: ua.match(/Windows Phone/),
		zune: ua.match(/ZuneWP7/)
	}

	return brand;
}