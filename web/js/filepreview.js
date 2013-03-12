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
function previewImage(el,widths,limit){
    if(typeof FileReader !== 'function' ) return;
    
	var files = el.files;
	var wrap = el.parentNode;
	var output = wrap.getElementsByClassName('imagePreview')[0];

	output.innerHTML='<i class="icon-spinner icon-spin icon-2x pull-left"></i>';

	var file = files[0];
	var imageType = /image.*/;

	// detect device
	var device = detectDevice();

	if (!device.android){ // Since android doesn't handle file types right, do not do this check for phones
		if (!file.type.match(imageType)) {
			output.innerHTML='';
			return;
		}
	}

	var img='';

	var reader = new FileReader();
	reader.onload = (function(aImg) {
		return function(e) {
			output.innerHTML='';

			var format = e.target.result.split(';');
			format = format[0].split('/');
			format = format[1].toUpperCase();

			// We will change this for an android
			if (device.android){
				format = file.name.split('.');
				format = format[format.length-1].toUpperCase();
			}
            if(e.total>(limit*1024*1024)) {
                output.innerHTML='<p>El archivo es demasiado grande!</p>';
                return;
            } else if (((format=='JPG')||(format=='JPEG')||(format=='PNG')||(format=='GIF'))){
				for (var size in widths){
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
                    image.className = 'thumbnail';
					image.width = widths[size];
					image.title = 'Vista previa';
					output.appendChild(image);
					
					var text = document.createElement('p');
                    text.innerHTML = '<strong><small>(Vista previa)</small></strong>';
                    output.appendChild(text);
				}
			} else {
                output.innerHTML='';
                return;
			}
		};
	})(img);
	reader.readAsDataURL(file);
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