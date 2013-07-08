var itemSubmitType = false;

function typeChosen(type) {
    //Set the type of item chosen. Returns true when you can't choose that type
    $('#itemSubmit').addClass('active');
    
    if(itemSubmitType) {
        if(itemSubmitType == 'file' || itemSubmitType == 'route') {
            return false;
        } else {
            return true;
        }
    } else {
        $('#itemSubmit .types').addClass('chosen');
        $('#itemSubmit .types .'+type).addClass('active');
        itemSubmitType = type;
        
        switch(type) {
            case 'file': $('#itemSubmit').attr('action','/archivo/crear/');
            break;
            case 'route': $('#itemSubmit').attr('action','/crear/ruta/');
            break;
            case 'event': $('#itemSubmit').attr('action','/crear/actividad/');
            break;
            case 'place': $('#itemSubmit').attr('action','/crear/lugar/');
            break;
            case 'post': $('#itemSubmit').attr('action','/crear/texto/');
            break;
        }
        return false;
    }
}

function ItemAttachForm(id) {
    var categorylist = $('#itemSubmit .category').first().html();
    if(typeof categorylist == 'undefined') {
        var categorylist = '';
    }
    
    $('#itemSubmit').remove();
    itemSubmitType = false;
    
    $('#item'+id+' .itemActions').first().after('<form id="itemSubmit" class="form-horizontal" enctype="multipart/form-data" method="POST" action=""><div class="mainData"><input id="itemSubmitName" type="text" name="name" class="title" placeholder="Título"><textarea id="itemSubmitDescription" name="description" placeholder="Descripción" class="description"></textarea></div><div id="itemDetails"></div><ul class="types unstyled"><li class="pull-left text">Adjuntar: </li><li class="pull-left post" onClick="loadPostForm();"><i class="icon-file-text-alt"></i> Texto</li><li class="pull-left event" onClick="loadEventForm();"><i class="icon-calendar-empty"></i> Actividad</li><li class="pull-left route"> <input type="file" name="file" id="Route"><i class="icon-globe"></i> Ruta</li><li class="pull-left place" onClick="loadPlaceForm();"><i class="icon-map-marker"></i> Lugar</li><li class="pull-left file"> <input type="file" name="file[]" multiple="multiple" id="File"><i class="icon-folder-open"></i> Archivos</li><li class="pull-right"><button class="btn btn-small btn-primary" type="submit" id="itemSubmitButton"><i id="itemSubmitButtonLoading"></i> <span id="itemSubmitButtonText">Publicar</span> </button></li><li class="pull-right category">'+categorylist+'</li></ul><input type="hidden" name="attachTo" value="'+id+'"></form>');
    
    $('#Route').change(RouteChange);
    $('#File').change(FileChange);
    
    if(categorylist == '') {
        $('#itemSubmitButton').attr('disabled','disabled').addClass('disabled'); 
        $('#itemSubmit .category').load('/categorias/formlist/', function(){ $('#itemSubmitButton').removeAttr('disabled').removeClass('disabled');  });
    }
}

/* Post Creation */

function loadPostForm() {
    if(typeChosen('post')) {
        return;
    }
}

/* Place Creation */
var PCgeocoder;
var PCmap;
var PCmarker;

function loadPlaceForm() {
    if(typeChosen('place')) {
        return;
    }
    
    $('#itemDetails').html('<p class="lead">Escribe un lugar para buscar en el mapa:</p><div class="input-append"><p><input id="PlaceMapSearch" type="text" placeholder="Dirección..." class="span4"><button type="button" id="mapSearchIcon" class="btn btn-primary"><i class="icon-search"></i> Localizar</button></p></div><ul aria-labelledby="dropdownMenu" role="menu" style="position: static; float: none; display: block;margin-bottom: 20px;width:90%;" class="dropdown-menu hidden" id="mapResults"></ul><div id="map" style="display:none"></div><input type="hidden" name="latitude" id="PlaceLatitude"><input type="hidden" name="longitude" id="PlaceLongitude">');
    
    $('#itemSubmit').submit(searchAction);
    $('#mapSearchIcon').click(searchAction);
}

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
        
        $('#itemSubmit').unbind();
        $('#PlaceMapSearch').focus(function(){$('#itemSubmit').submit(searchAction);})
        
        return false;
    }

function searchLocation() {
    $('#mapSearchIcon').removeClass('btn-primary').html('<i class="icon-refresh icon-spin"></i> Localizando...');
    
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

        $('#mapSearchIcon').html('<i class="icon-search"></i> Localizar');
    });
}

function mapPosition(position) { //google maps latlng object
    //If not active, create map
    if( $('#map').css('display') == 'none') {
        $('#map').css('display', 'block');
        PCmap = new google.maps.Map(document.getElementById('map'), {zoom: 15,center:position, mapTypeId: google.maps.MapTypeId.TERRAIN, streetViewControl: false});
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
}

/* Event Creation */
function loadEventForm() {
    if(typeChosen('event')) {
        return;
    }
    
    $('#itemDetails').html('<p class="lead"> <i class="icon-refresh icon-spin"></i> Espere un instante...</p>');
    $('#itemDetails').load('/actividades/crear/detalles/');
}

/* RU Route Uploading */
var RUuploadAddress = '/rutas/XHR/upload/';
var RUpreviewAddress = '/rutas/XHR/preview/';

var RUuploading = false;

$('#Route').change(RouteChange);

function RouteChange() {
    if(typeChosen('route')) {
        return;
    }
    var files = this.files;    
    var fp = $('#itemDetails');
    if(files.length) {        
        //Show the file uloading process
        var f = files[0]
        fp.append('<div id="RUloading"><p class="lead">Subiendo el archivo <small>'+f.name+'</small></p><div class="progress"><div class="bar" id="RUprogress" style="width: 0%;"></div></div>');
        
        //Start upload
        uploadRouteFile(f);
    }
}

function uploadRouteFile(file) {
    if(!RUuploading) {
        RUuploading = true;
        
        $('#itemSubmitButton').attr('disabled','disabled').addClass('disabled'); 
        $('#itemSubmitButtonText').html('Subiendo...'); 
        $('#itemSubmitButtonLoading').addClass('icon-refresh icon-spin');
    
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
    
    $('#itemDetails').html('<p class="lead"> <i class="icon-refresh icon-spin"></i> Procesando archivo...</p>')
    $('#itemDetails').load(RUpreviewAddress+token);
    
    RUuploadEnd();
}

function RUuploadFailed(evt) {
    $('#itemDetails').html('<p class="lead">Ha ocurrido un fallo en la subida.</p>');
    RUuploadEnd();
}

function RUuploadCanceled(evt) {
    $('#itemDetails').html('<p class="lead">La subida del archivo se ha cancelado.</p>');
    RUuploadEnd();
}

function RUuploadEnd() {
    RUuploading = false;
    
    $('#Route').remove();
    $('#itemSubmit .types .route').prepend('<input type="file" id="Route" name="file">');
    $('#Route').change(RouteChange);
    
    $('#itemSubmitButton').removeAttr('disabled').removeClass('disabled'); 
    $('#itemSubmitButtonText').html('Publicar'); 
    $('#itemSubmitButtonLoading').removeClass('icon-refresh icon-spin');
}

/* FU File Uploading */
var FUuploadAddress = '/archivo/XHR/upload/';
var FUpreviewAddress = '/archivo/XHR/preview/';

var FUuploading = false;
var FUcurrentFileIndex = -1;
var FUTheFiles = [];

$('#File').change(FileChange);

function FileChange() {
    if(typeChosen('file')) {
        return;
    }
    var files = this.files;    
    var fp = $('#itemDetails');
    if(files.length) {
        fp.show();
        
        //Show the files
        for(var i = 0; i < files.length; i++) {
            var f = files[i];
            var j = (i + FUTheFiles.length);
            fp.append('<div class="pv" id="UFC'+j+'"><div class="uploadmessage">Preparando '+f.name+'</div><div class="progress progress-striped"><div class="bar" id="progress'+j+'" style="width: 0%;"></div></div></div>');
        }
        
        //Start upload
        for(var i = 0; i < files.length; i++) {
            FUTheFiles.push(files[i]);
        }
        
        uploadFUTheFiles();
    }
}

function uploadFUTheFiles() {
    if(!FUuploading && FUTheFiles.length) {
        FUuploading = true;
        
        $('#itemSubmitButton').attr('disabled','disabled').addClass('disabled'); 
        $('#itemSubmitButtonText').html('Subiendo...'); 
        $('#itemSubmitButtonLoading').addClass('icon-refresh icon-spin');
    
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
    
    $('#File').remove();
    $('#itemSubmit .types .file').prepend('<input type="file" id="File" multiple="multiple" name="file[]">');
    $('#File').change(FileChange);
    
    $('#itemSubmitButton').removeAttr('disabled').removeClass('disabled'); 
    $('#itemSubmitButtonText').html('Publicar'); 
    $('#itemSubmitButtonLoading').removeClass('icon-refresh icon-spin');
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
        $('#UFC'+FUcurrentFileIndex).html('<img src="'+FUpreviewAddress + $.trim(res)+'" style="width:250px;height:190px;background-color:#AAA"><input type="text" name="file'+FUcurrentFileIndex+'Name" placeholder="Nombre..." onFocus="if(this.value == \'Nombre...\') this.value=\'\'"><textarea name="file'+FUcurrentFileIndex+'Description" placeholder="Descripción..." onFocus="if($(this).val(\'Descripción...\')) $(this).val(\'\')"></textarea><small class="btn btn-link btn-small" onClick="deleteFile('+FUcurrentFileIndex+')">(Eliminar archivo)</small><input type="hidden" name="file'+FUcurrentFileIndex+'Token" value="'+res+'"><input type="hidden" name="file'+FUcurrentFileIndex+'Delete" value="0" id="file'+FUcurrentFileIndex+'Delete">');
        
        //Placeholder problems
        var phitest = document.createElement('input');
        if(!('placeholder' in phitest)) {
            $('#UFC'+FUcurrentFileIndex+' input[type=text]').val('Nombre...')
            $('#UFC'+FUcurrentFileIndex+' textarea').val('Descripción...')
        }
        
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
    $('#FileSubmitLoading').addClass('icon-refresh icon-spin');
    
    $('#ProcessForm').attr('action','').attr('onSubmit','').submit();
}

/* Category form */

function toggleCategoryCreate() {
    var nc = $('#NewCategory');
    if(nc.css('display') == 'none') {
        $('#Category').hide();
        $('#NewCategory').show().focus();
        $('#categoryCreateButton').show();
    } else {
        $('#Category').show();
        $('#Category option:first-child').attr('selected','selected');
        $('#NewCategory').hide().attr('value','');
        $('#categoryCreateButton').hide();
    }
}

