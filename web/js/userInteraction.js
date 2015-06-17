var itemSubmitType = 'text';
var unsaved = false;

function typeChosen(type) { //Set the type of item chosen    
    /*$(window).bind('beforeunload', function(){
      return 'Seguro que quieres irte sin publicar?';
    });
    $('#itemSubmit').submit( function(){
        window.onbeforeunload = null;
    });*/
    
    
    $('#itemForm').removeClass('compressed'); //Expand the form if compressed
    
    if(itemSubmitType == 'map') {
        $('#RouteFile').remove();
        $('#RouteFileHandler').html('<input type="file" id="RouteFile" name="file">');
        $('#RouteFile').change(RouteChange);
    }
    else if(itemSubmitType == 'text') {
        $('#FilesFile').remove();
        $('#FilesFileHandler').html('<input type="file" id="FilesFile" multiple="multiple" name="file[]">');
        $('#FilesFile').change(FileChange);
    }
    
    if(itemSubmitType == type) {
        return false;
    } else {
        if(unsaved && !confirm('¿Seguro que quieres descartar la publicación?\nSólo puedes publicar un tipo de contenido al mismo tiempo.'))
        {
            return false;
        }
        
        unsaved = false;
        
        if(itemSubmitType != '')
        {
            $('#'+itemSubmitType+'Handler').removeClass('active');
            $('#'+itemSubmitType+'Form').addClass('hiddenForm');  
        }
        
        $('#'+type+'Form').removeClass('hiddenForm');
        $('#'+type+'Handler').addClass('active');
                
        itemSubmitType = type;
        
        switch(type) {
            case 'event': 
                $('#eventForm input, .itemTypeEvent select').change(function(){ unsaved = true })
            break;
            case 'map':
                $('#RouteFile').change(RouteChange);
            break;
        }
        return false;
    }
}

/* Place Creation */
var PCgeocoder;
var PCmap;
var PCmarker;

function searchAction() {
        if(typeof google === 'object' && typeof google.maps === 'object') {
            searchLocation();
        } else {
            //Load Asynchronously GMaps
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.src = "http://maps.googleapis.com/maps/api/js?sensor=false&callback=searchLocation";
            document.body.appendChild(script);
        }
        
        $('#itemSubmit').unbind().submit( function(){
            window.onbeforeunload = null;
        });
        $('#PlaceMapSearch').focus(function(){$('#itemSubmit').submit(searchAction);})
        
        return false;
    }

function searchLocation() {
    $('#mapSearchIcon').removeClass('btn-primary').addClass('btn-default').html('<i class="fa fa-refresh fa-spin"></i> Localizando...');
    
    if(!PCgeocoder) {
        PCgeocoder = new google.maps.Geocoder();
    }
    
    var address = $('#PlaceMapSearch').val();
    
    $('#mapResults').addClass('hidden');
    
    PCgeocoder.geocode( { 'address': address}, function(results, status) {
    
        if (status == google.maps.GeocoderStatus.OK) {
            
            if(results.length > 1) {
                $('#mapResults').html('');
                for(var i = 0; i < results.length; i++) {
                    $('#mapResults').append('<li id="mapResult'+(i + 1)+'"><a tabindex="-1" href="#result'+i+'" onClick="mapPosition(new google.maps.LatLng('+results[i].geometry.location.lat()+', '+results[i].geometry.location.lng()+'));$(\'#itemSubmitName\').val(\''+results[i].formatted_address+'\');$(\'#mapResults\').addClass(\'hidden\');return false;"> '+results[i].formatted_address+'</a></li>');
                }
                
                $('#mapResults').removeClass('hidden');
            } else {
                mapPosition(results[0].geometry.location);
                $('#itemSubmitName').val(results[0].formatted_address);
            }
            
        } else {
            if(status == 'ZERO_RESULTS') {
                alert('No hemos encontrado ningun sitio con esas palabras. \nPrueba de nuevo');
            } else {
                alert('Ha ocurrido un error: ' + status);
            }
        }

        $('#mapSearchIcon').html('<i class="fa fa-search"></i> Localizar');
    });
}

function mapPosition(position) { //google maps latlng object
    //If not active, create map
    if( $('#ItemSubmitMap').css('display') == 'none') {
        $('.itemTypePlace .help-block').html('<i class="fa fa-location-arrow"></i> Puedes pulsar en otro lugar del mapa para establecer la localización');
        $('#ItemSubmitMap').css('display', 'block');
        PCmap = new google.maps.Map(document.getElementById('ItemSubmitMap'), {zoom: 15,center:position, mapTypeId: google.maps.MapTypeId.TERRAIN, streetViewControl: false});
        google.maps.event.addListener(PCmap, 'click', function(event) {
            mapPosition(event.latLng);
        });
        
        $('#PlaceLatitude').val(position.lat());
        $('#PlaceLongitude').val(position.lng());
    } else {
        PCmap.panTo(position);
        $('#PlaceLatitude').val(position.lat());
        $('#PlaceLongitude').val(position.lng());
    }
    
    if(!PCmarker) {
        PCmarker = new google.maps.Marker({
            map: PCmap,
            position:position
        });
    } else {
        PCmarker.setPosition(position);
    }
    
    unsaved = true
}

/* RU Route Uploading */
var RUuploadAddress = '/rutas/XHR/upload/';
var RUpreviewAddress = '/rutas/XHR/preview/';

var RUuploading = false;

function RouteChange() {
    var files = this.files;    
    
    if(files.length) {        
        //Show the file uloading process
        var f = files[0]
        $('#RUmessage').html('<p class="lead">Subiendo el archivo <small>'+f.name+'</small></p> <div class="progress"><div class="progress-bar" id="RUprogress" style="width: 0%;"></div>');
        $('#RUaddon').html('');
        
        //Start upload
        uploadRouteFile(f);
    }
    
    unsaved = true;
}

function uploadRouteFile(file) {
    if(!RUuploading) {
        RUuploading = true;
        
        $('#itemSubmitButton').attr('disabled','disabled').addClass('disabled'); 
        $('#itemSubmitButtonText').html('Subiendo...'); 
        $('#itemSubmitButtonLoading').addClass('fa fa-refresh fa-spin');
    
        uploadRoute(file);
    }
}

function uploadRoute(file) {
    var fd = new FormData();
    
    fd.append("file", file);
    
    var xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener("progress", RUuploadProgress, false);
    xhr.addEventListener("load", RUuploadComplete, false);
    xhr.addEventListener("error", RUuploadFailed, false);
    xhr.addEventListener("abort", RUuploadCanceled, false);
    
    xhr.open("POST", RUuploadAddress);
    
    xhr.send(fd);
}

function RUuploadProgress(evt) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 100 / evt.total);
        $('#RUprogress').css('width',percentComplete.toString() + '%');
    }
    else {
        $('#RUprogress').css('width','40%');
    }
}

function RUuploadComplete(evt) {
    /* This event is raised when the server send back a response */
    var token = evt.target.responseText;
    
    $('#RUmessage').html('');
    
    $('#RUaddon').html('<div class="col-lg-offset-2 col-sm-offset-3 col-sm-9 col-lg-10"><p class="lead"> <i class="fa fa-refresh fa-spin"></i> Procesando archivo...</p></div>')
    $('#RUaddon').load(RUpreviewAddress+token);
    
    RUuploadEnd();
}

function RUuploadFailed(evt) {
    $('#RUmessage').html('<p class="lead">Ha ocurrido un fallo en la subida.</p>');
    RUuploadEnd();
}

function RUuploadCanceled(evt) {
    $('#RUmessage').html('<p class="lead">La subida del archivo se ha cancelado.</p>');
    RUuploadEnd();
}

function RUuploadEnd() {
    RUuploading = false;
    
    //$('#RouteFile').remove();
    $('#itemSubmit .itemTypeRoute .form-controls').prepend('<input type="file" id="RouteFile" name="file">');
    $('#RouteFile').change(RouteChange);
    
    $('.itemTypePlace .help-block').html('Puedes seleccionar otro archivo diferente.');
    
    $('#itemSubmitButton').removeAttr('disabled').removeClass('disabled'); 
    $('#itemSubmitButtonText').html('Publicar'); 
    $('#itemSubmitButtonLoading').removeClass('fa fa-refresh fa-spin');
}

/* FU File Uploading */
var FUuploadAddress = '/archivo/XHR/upload/';
var FUpreviewAddress = '/archivo/XHR/preview/';

var FUuploading = false;
var FUcurrentFileIndex = -1;
var FUTheFiles = [];

function FileChange() {
    var files = this.files;    
    var fp = $('#FUaddon');
    if(files.length) {      
        for(var i = 0; i < files.length; i++) {
            var f = files[i];
            var j = (i + FUTheFiles.length);
            fp.append('<div id="UFC'+j+'"><div class="uploadmessage">Preparando '+f.name+'</div><div class="progress"><div role="progressbar"  class="progress-bar" id="progress'+j+'" style="width: 0%;"></div></div></div>');
        }
        
        //Start upload
        for(var i = 0; i < files.length; i++) {
            FUTheFiles.push(files[i]);
        }
        
        uploadFUTheFiles();
    }
    
    unsaved = true;
}

function uploadFUTheFiles() {
    if(!FUuploading && FUTheFiles.length) {
        FUuploading = true;
        
        $('#itemSubmitButton').attr('disabled','disabled').addClass('disabled'); 
        $('#itemSubmitButtonText').html('Subiendo...'); 
        $('#itemSubmitButtonLoading').addClass('fa fa-refresh fa-spin');
    
        uploadNext();
    }
}
function uploadNext() {
    FUcurrentFileIndex++;
    var i = FUcurrentFileIndex;
    
    if(FUTheFiles.length <= i){ 
        //We reached the end
        FUcurrentFileIndex--;
        endOfUploads();
        return;
    } 
    
    $('#UFC'+i+' .uploadmessage').html('Subiendo...');
    
    uploadFile(FUTheFiles[i]);
}
function endOfUploads() {
    FUuploading = false;
    
    $('#FilesFile').remove();
    $('#FilesFileHandler').prepend('<input type="file" id="FilesFile" multiple="multiple" name="file[]">');
    $('#FilesFile').change(FileChange);
    
    $('#itemSubmitButton').removeAttr('disabled').removeClass('disabled'); 
    $('#itemSubmitButtonText').html('Publicar'); 
    $('#itemSubmitButtonLoading').removeClass('fa fa-refresh fa-spin');
    
    $('#textForm').attr('action','/archivo/crear/');
}
function uploadFile(file) {
    var fd = new FormData();
    
    fd.append("file", file);
    
    var xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener("progress", FUuploadProgress, false);
    xhr.addEventListener("load", FUuploadComplete, false);
    xhr.addEventListener("error", FUuploadFailed, false);
    xhr.addEventListener("abort", FUuploadCanceled, false);
    
    xhr.open("POST", FUuploadAddress);
    
    xhr.send(fd);
}

function FUuploadProgress(evt) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 100 / evt.total);
        $('#progress'+FUcurrentFileIndex).css('width',percentComplete.toString() + '%');
    }
    else {
        $('#progress'+FUcurrentFileIndex).css('width','40%');
    }
}

function FUuploadComplete(evt) {
    /* This event is raised when the server send back a response */
    var res = evt.target.responseText;
    
    if(res.length > 180) {
        FUuploadFailed();
    } else {
        $('#UFC'+FUcurrentFileIndex).html('<hr><div class="row"><div class="col-sm-12 col-md-4"><img src="'+FUpreviewAddress + $.trim(res)+'" class="img-responsive"></div><div class="col-sm-12 col-md-8"><input type="text" class="form-control" name="file'+FUcurrentFileIndex+'Name" placeholder="Nombre..."><textarea rows="4" class="form-control" name="file'+FUcurrentFileIndex+'Description" placeholder="Descripción..."></textarea><small class="btn btn-link btn-small" style="color:red" onClick="deleteFile('+FUcurrentFileIndex+')">(Eliminar archivo)</small><input type="hidden" name="file'+FUcurrentFileIndex+'Token" value="'+res+'"><input type="hidden" name="file'+FUcurrentFileIndex+'Delete" value="0" id="file'+FUcurrentFileIndex+'Delete"></div></div>');
        
        uploadNext();
    }
}

function FUuploadFailed(evt) {
    $('#UFC'+FUcurrentFileIndex).html('Ha ocurrido un fallo en la subida.');
    uploadNext();
}

function FUuploadCanceled(evt) {
    $('#UFC'+FUcurrentFileIndex).html('La subida del archivo se ha cancelado.');
    uploadNext();
}

function deleteFile(i) {
    $('#UFC'+i).hide();
    $('#file'+i+'Delete').val('1');
}

function process() {
    if(FUuploading) return;
    
    $('#FileControlGroup').remove();
    $('#FileSubmit').attr('disabled','disabled').addClass('disabled'); 
    $('#FileSubmitText').html('Enviando...'); 
    $('#FileSubmitLoading').addClass('fa fa-refresh fa-spin');
    
    $('#ProcessForm').attr('action','').attr('onSubmit','').submit();
}

function privacyToggle()
{
    if($('.privacyToggle').val() == '1'){
        $('.privacyToggle').val('0');
        $('.privacyToggleButton').html('<i class="fa fa-lock"></i> Sólo usuarios');
    }else{
        $('.privacyToggle').val('1');
        $('.privacyToggleButton').html('<i class="fa fa-unlock"></i> Abierto');
    }
}