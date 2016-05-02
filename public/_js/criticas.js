var cache = {};
var arquivos_timeout;
var arquivos_timeout_statement;
$(function() {
    
    /* Form submission (AJAX + JSON)
    ********************************************************************************************/
    
    //Define the options (functions) to handle the submit and response
    var options = {
        beforeSubmit: function() {
            $('.error_box').css('visibility', 'hidden'); //Hide error messages (if exists)
            $('#status').html('<span class="loading"></span>'); //AJAX loading gif
        }, 
        success: function(response, status) {
            $('.error_box').remove(); //Remove error messages (if exists)
            //If no errors occurred, print the success message
            if (response.error == null) {
            	if (typeof($('#add').get(0)) != 'undefined') {
            		$('#status').html('<span class="success">Documento cadastrado com sucesso!</span>');
            	}
            	else {
            		$('#status').html('<span class="success">Documento atualizado com sucesso!</span>');
            	}
            }
            else {
                //Highlight invalid fields
                if (typeof(response.error.length) == "undefined") {
                    $('#status').html('<span class="error">Verifique o(s) campo(s) com problema(s)</span>');
                    var focus = true;
                    $.each(response.error, function(i, val) {
                        if (i == 'autores' || i == 'fontes') {
                            $('#' + i).next().after('<div class="clear"></div>' +
                                                    '<div class="error_box" style="width: 780px">'+val+'</div>');
                        }
                        else {
                            $('#' + i).after('<div class="error_box">'+val+'</div>');
                        }
                        if (focus && $('#' + i).get(0)) {
                            scrollTo($('#' + i), function() {
                                $('#' + i).focus();
                            }, -25);
                            focus = false;
                        }
                    });
                }
                else {
                    //Print the error message
                    $('#status').html('<span class="error">'+response.error+'</span>');
                }
            }
        }, 
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //Print the error message if the request fails (possible reasons: "timeout", "error", "notmodified" and "parsererror")
            $('#status').html('<span class="error">'+textStatus+':<br />'+XMLHttpRequest.responseText+'</span>');
        },
        dataType: 'json'
    };

    /* Mídias
     ********************************************************************************************/
    
	new qq.FileUploader({
        element: $('#arquivo')[0],
        action: '../?action=upload',
        onSubmit: function(id, fileName) {
            /* Alternativa encontrada para a falta de eventos (onAddToList) */
        	if (arquivos_timeout) {
                eval(arquivos_timeout_statement);
            	clearTimeout(arquivos_timeout);
            	arquivos_timeout = null;
            }
        	arquivos_timeout_statement = 'bind_arquivos("arquivo_'+id+'")';
        	arquivos_timeout = setTimeout(arquivos_timeout_statement, 300);
			//bind_arquivos("arquivo_"+id);
        },
        onComplete: function(id, fileName, responseJSON) {
            if (responseJSON.success === true) {
            	/* Alternativa encontrada para a falta de eventos (onAddToList) */
                if (!$('#arquivo_'+id)[0]) {
					setTimeout("$('#arquivo_"+id+"').val('"+responseJSON.filename+"');", 300);
                }
                else {
                	$('#arquivo_'+id).val(responseJSON.filename);
                }
            	//$('#arquivo_'+id).val(responseJSON.filename);
            }
        },
        messages: {
            typeError: "{file} possui formato inválido. Apenas {extensions} são permitidos.",
            sizeError: "{file} excedeu o tamanho de arquivo (Máximo: {sizeLimit}).",
            emptyError: "{file} é vazio. Selecione os arquivos novamente.",
            minSizeError: "{file} é muito pequeno. O tamanho mínimo é {minSizeLimit}.",
            onLeave: "Os arquivos estão sendo enviados. Ao sair, o upload será cancelado."
        },
        template: '<div class="qq-uploader">' + 
		          '<div class="qq-upload-drop-area"><span>Arraste e solte os arquivos aqui para enviá-los</span></div>' +
		          '<div class="qq-upload-button">Selecionar arquivos</div>' +
		          '<ul class="qq-upload-list"></ul>' + 
		          '</div>',
        fileTemplate: '<li>' +
        			  '<span class="qq-upload-title">Arquivo</span>' +
        			  '<a href="#" class="qq-upload-remove" onclick="$(this).parent().remove(); return false">Remover</a>' +
                	  '<span class="qq-upload-file"></span>' +
                	  '<span class="qq-upload-spinner"></span>' +
                	  '<span class="qq-upload-size"></span>' +
               	 	  '<a class="qq-upload-cancel" href="#">Cancelar</a>' +
               	      '<div class="clear"></div>' +
             	      '</li>'
    });
	
	setInterval(watchUploads, 1000);
});

function bind_arquivos(id) {
//Fontes das mídias são diferentes do que as dos documentos!
//	$('.fontes_arquivos').autocomplete({
//        source: function(request, response) {
//            if (request.term in cache) {
//                response(cache[request.term]);
//                return;
//            }
//            $.ajax({
//                url: "../../../fontes/?action=search_fonte",
//                dataType: "json",
//                data: request,
//                success: function(data) {
//                    cache[request.term] = data;
//                    response(data);
//                }
//            });
//        }
//    });
	if (id) {
		$('.qq-upload-list').first().find('li:last-child').append('<input type="hidden" id="'+id+'" name="arquivos[]" />');
	}
//	$('.qq-upload-remove').click(function() {
//		//TODO: cancel file upload
//		$(this).parent().remove();
//		return false;
//	});
}

function watchUploads() {
	if ($('.qq-upload-cancel').size() > 0) {
        $('input[type=submit]').addClass('disabled');
        $('input[type=submit]').attr('disabled', true);
	}
	else {
        $('input[type=submit]').removeClass('disabled');
        $('input[type=submit]').attr('disabled', false);
	}
}

//Desabilita o enter no textarea do título dos documentos
$("#titulo").keypress(function (e) {
    if (e.keyCode == 13) {
        var targetType = e.originalTarget ? e.originalTarget.type.toLowerCase() : e.srcElement.tagName.toLowerCase();
        if (targetType == "textarea") {
            e.preventDefault();
            e.stopPropagation();
        }
    }
});