var itemSubmitType = false;

function typeChosen(type) {
    //Set the type of item chosen. Returns true when you can't choose that type
    if(itemSubmitType) {
        if(itemSubmitType == 'file') {
            return false;
        } else {
            return true;
        }
    } else {
        $('.itemSubmit .types').addClass('chosen');
        $('.itemSubmit .types .'+type).addClass('active');
        itemSubmitType = type;
        
        switch(type) {
            case 'file': $('.itemSubmit').attr('action','/archivo/crear/');
            break;
        }
        return false;
    }
}

/* FU File Uploading */
var FUuploadAddress = '/archivo/XHR/upload/';
var FUpreviewAddress = '/archivo/XHR/preview/';

var FUuploading = false;
var FUcurrentFileIndex = -1;
var FUTheFiles = [];

$('#File').change(function(){
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
        
        $('#FileLabel').html('Más archivos');
    }
});

function uploadFUTheFiles() {
    if(!FUuploading && FUTheFiles.length) {
        FUuploading = true;
        
        $('#FileSubmit').attr('disabled','disabled').addClass('disabled'); 
        $('#FileSubmitText').html('Subiendo archivos...'); 
        $('#FileSubmitLoading').addClass('icon-refresh icon-spin');
    
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
    
    $('#FileSubmit').removeAttr('disabled').removeClass('disabled'); 
    $('#FileSubmitText').html('Publicar ahora'); 
    $('#FileSubmitLoading').removeClass('icon-refresh icon-spin');
}
function uploadFile(file) {
    var fd = new FormData();
    
    fd.append("file", file);
    
    var xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener("progress", uploadProgress, false);
    xhr.addEventListener("load", uploadComplete, false);
    xhr.addEventListener("error", uploadFailed, false);
    xhr.addEventListener("abort", uploadCanceled, false);
    
    xhr.open("POST", FUuploadAddress);
    
    xhr.send(fd);
}

function uploadProgress(evt) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 100 / evt.total);
        $('#progress'+FUcurrentFileIndex).css('width',percentComplete.toString() + '%');
    }
    else {
        $('#progress'+FUcurrentFileIndex).css('width','40%');
    }
}

function uploadComplete(evt) {
    /* This event is raised when the server send back a response */
    var res = evt.target.responseText;
    
    if(res.length > 180) {
        uploadFailed();
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

function uploadFailed(evt) {
    $('#UFC'+FUcurrentFileIndex).html('Ha ocurrido un fallo en la subida.');
    uploadNext();
}

function uploadCanceled(evt) {
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

