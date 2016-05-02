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
            		$('#status').append('<a href="" id="new_record">Novo cadastro</a> | <a href="../editar/?id=' + response.id + '" id="edit_record">Editar documento cadastrado</a>');
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
                        if (i == 'autores' || i == 'fontes' || i == 'generos' || i == 'idiomas' || i == 'editoras') {
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

    $('#add').submit(function() {
        $('#autores').selectOptions(/./); //Select all options
        $('#fontes').selectOptions(/./); //Select all options
        $('#editoras').selectOptions(/./); //Select all options
        $('#generos').selectOptions(/./);
        $('#idiomas').selectOptions(/./);
        $('#add').ajaxSubmit(options); //Submit the form through AJAX Form plugin
        return false;
    });
    
    $('#update').submit(function() {
        $('#autores').selectOptions(/./); //Select all options
        $('#fontes').selectOptions(/./); //Select all options
        $('#editoras').selectOptions(/./); //Select all options
        $('#generos').selectOptions(/./);
        $('#idiomas').selectOptions(/./);
        $('#update').ajaxSubmit(options); //Submit the form through AJAX Form plugin
        return false;
    });

    /* Autores
    ********************************************************************************************/
    
    $('#add_autor').click(function() {
        if ($('#autor').val() && $('#autor_id').val()) {
            $('#autores').addOption($('#autor_id').val(), $('#autor').val());
            $('#autor').val('');
            $('#autor_id').val('');
            $('#autor').focus('');
            $('#add_autor').addClass('disabled');
            $('#add_autor').attr('disabled', true);
            $('#rem_autor').removeClass('disabled');
            $('#rem_autor').attr('disabled', false);
        }
        return false;
    });
    $('#rem_autor').click(function() {
        //$('#autores').copyOptions('#autor');
        $('#autores').removeOption(/./, true);
        if ($('#autores option').size() == 0) {
            $('#rem_autor').addClass('disabled');
            $('#rem_autor').attr('disabled', true);
        }
        return false;
    });

    //Autor - auto complete:
    var cache_autor = {};
    $('#autor').autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term in cache_autor) {
                if (typeof(cache_autor[request.term][0]) != 'undefined' &&
                	request.term == cache_autor[request.term][0].value) {
                	$('#add_autor').removeClass('disabled');
                    $('#add_autor').attr('disabled', false);
        	    }
                response(cache_autor[request.term]);
                return;
            }
          
            $.ajax({
                url: "../../../autores/?action=search_autor",
                dataType: "json",
                data: request,
                success: function(data) {
                    cache_autor[request.term] = data;
	            	if (typeof(cache_autor[request.term][0]) != 'undefined' &&
	            		request.term == cache_autor[request.term][0].value) {
	                	$('#add_autor').removeClass('disabled');
	                    $('#add_autor').attr('disabled', false);
	        	    }
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $('#autor').val(ui.item.label);
            $('#autor').data('lastValue', $('#autor').val());
            $('#autor_id').val(ui.item.id);
            $('#add_autor').removeClass('disabled');
            $('#add_autor').attr('disabled', false);
            return false;
        }
    }).data("autocomplete")._renderItem = function(ul, item) {
		return $("<li></li>")
		.data("item.autocomplete", item)
		.append("<a>" + item.label + "<br /><em>" + item.desc + "</em></a>")
		.appendTo(ul);
    };

    $('#autor').data('lastValue', $('#autor').val());

    $('#autor').keyup(function() {
    	if ($(this).data('lastValue') != $(this).val()) {
    		$(this).data('lastValue', $(this).val());
	        $('#add_autor').addClass('disabled');
	        $('#add_autor').attr('disabled', true);
    	}
    });

    $('#autores').change(function() {
        $('#rem_autor').removeClass('disabled');
        $('#rem_autor').attr('disabled', false);
    });

    /* Fontes
    ********************************************************************************************/
    
    $('#add_fonte').click(function() {
        if ($('#fonte').val() && $('#fonte_id').val()) {
            $('#fontes').addOption($('#fonte_id').val(), $('#fonte').val());
            $('#fonte').val('');
            $('#fonte_id').val('');
            $('#fonte').focus('');
            $('#add_fonte').addClass('disabled');
            $('#add_fonte').attr('disabled', true);
            $('#rem_fonte').removeClass('disabled');
            $('#rem_fonte').attr('disabled', false);
        }
        return false;
    });
    $('#rem_fonte').click(function() {
        //$('#fontes').copyOptions('#fonte');
        $('#fontes').removeOption(/./, true);
        if ($('#fontes option').size() == 0) {
            $('#rem_fonte').addClass('disabled');
            $('#rem_fonte').attr('disabled', true);
        }
        return false;
    });

    //Fonte - auto complete:
    $('#fonte').autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term in cache) {
            	if (typeof(cache[request.term][0]) != 'undefined' &&
                	request.term == cache[request.term][0].value) {
                	$('#add_fonte').removeClass('disabled');
                    $('#add_fonte').attr('disabled', false);
        	    }
                response(cache[request.term]);
                return;
            }
            
            $.ajax({
                url: "../../../fontes/?action=search_fonte",
                dataType: "json",
                data: request,
                success: function(data) {
                    cache[request.term] = data;
                    if (typeof(cache[request.term][0]) != 'undefined' &&
                    	request.term == cache[request.term][0].value) {
                    	$('#add_fonte').removeClass('disabled');
                        $('#add_fonte').attr('disabled', false);
                    }
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $('#fonte').val(ui.item.label);
            $('#fonte').data('lastValue', $('#fonte').val());
            $('#fonte_id').val(ui.item.id);
            $('#add_fonte').removeClass('disabled');
            $('#add_fonte').attr('disabled', false);
            return false;
        }
    });

    $('#fonte').data('lastValue', $('#fonte').val());

    $('#fonte').keyup(function() {
    	if ($(this).data('lastValue') != $(this).val()) {
    		$(this).data('lastValue', $(this).val());
	        $('#add_fonte').addClass('disabled');
	        $('#add_fonte').attr('disabled', true);
    	}
    });

    $('#fontes').change(function() {
        $('#rem_fonte').removeClass('disabled');
        $('#rem_fonte').attr('disabled', false);
    });
    
    /* Editoras
     ********************************************************************************************/

    
    $('#add_editora').click(function() {
        if ($('#editora').val() && $('#editora_id').val()) {
            $('#editoras').addOption($('#editora_id').val(), $('#editora').val());
            $('#editora').val('');
            $('#editora_id').val('');
            $('#editora').focus('');
            $('#add_editora').addClass('disabled');
            $('#add_editora').attr('disabled', true);
            $('#rem_editora').removeClass('disabled');
            $('#rem_editora').attr('disabled', false);
        }
        return false;
    });
    $('#rem_editora').click(function() {
        //$('#fontes').copyOptions('#fonte');
        $('#editoras').removeOption(/./, true);
        if ($('#editoras option').size() == 0) {
            $('#rem_editora').addClass('disabled');
            $('#rem_editora').attr('disabled', true);
        }
        return false;
    });

    //Fonte - auto complete:
    $('#editora').autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term in cache) {
            	if (typeof(cache[request.term][0]) != 'undefined' &&
                	request.term == cache[request.term][0].value) {
                	$('#add_editora').removeClass('disabled');
                    $('#add_editora').attr('disabled', false);
        	    }
                response(cache[request.term]);
                return;
            }
            
            $.ajax({
                url: "../../../editoras/?action=search_editora",
                dataType: "json",
                data: request,
                success: function(data) {
                    cache[request.term] = data;
                    if (typeof(cache[request.term][0]) != 'undefined' &&
                    	request.term == cache[request.term][0].value) {
                    	$('#add_editora').removeClass('disabled');
                        $('#add_editora').attr('disabled', false);
                    }
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $('#editora').val(ui.item.label);
            $('#editora').data('lastValue', $('#fonte').val());
            $('#editora_id').val(ui.item.id);
            $('#editora_fonte').removeClass('disabled');
            $('#add_editora').attr('disabled', false);
            return false;
        }
    });

    $('#editora').data('lastValue', $('#editora').val());

    $('#editora').keyup(function() {
    	if ($(this).data('lastValue') != $(this).val()) {
    		$(this).data('lastValue', $(this).val());
	        $('#add_editora').addClass('disabled');
	        $('#add_editora').attr('disabled', true);
    	}
    });

    $('#editoras').change(function() {
        $('#rem_editora').removeClass('disabled');
        $('#rem_editora').attr('disabled', false);
    });
     
     /* Acervos
      ********************************************************************************************/

      //acervo - auto complete:
      $('#acervo').autocomplete({
          minLength: 2,
          source: function(request, response) {
              if (request.term in cache) {
              	if (typeof(cache[request.term][0]) != 'undefined' &&
                  	request.term == cache[request.term][0].value) {
          	    }
                  response(cache[request.term]);
                  return;
              }
              
              $.ajax({
                  url: "../../../acervos/?action=search_acervo",
                  dataType: "json",
                  data: request,
                  success: function(data) {
                      cache[request.term] = data;
                      if (typeof(cache[request.term][0]) != 'undefined' &&
                      	request.term == cache[request.term][0].value) {
                      }
                      response(data);
                  }
              });
          },
          select: function(event, ui) {
              $('#acervo').val(ui.item.label);
              $('#acervo_id').val(ui.item.id);
              $('#acervo').focus('');
              return false;
          },
          change: function( event, ui ) {
         	  if ( !ui.item ) {
         		  $('#acervo').val('');
                   $('#acervo_id').val('');
                   return false;
         	  }
         	}
      });
      
            

    /* Gênero
    ********************************************************************************************/

    $('#add_genero').click(function() {
        if ($('#genero').val() && $('#genero_id').val()) {
        	str = $('#genero option:selected').text();
            $('#generos').addOption($('#genero_id').val(), str); // $('#genero').val());
            $('#genero').val('');
            $('#genero_id').val('');
            $('#genero').focus('');
            $('#add_genero').addClass('disabled');
            $('#add_genero').attr('disabled', true);
            $('#rem_genero').removeClass('disabled');
            $('#rem_genero').attr('disabled', false);
        }
        return false;
    });
    
    $('#rem_genero').click(function() {
        //$('#generos').copyOptions('#genero');
        $('#generos').removeOption(/./, true);
        if ($('#generos option').size() == 0) {
            $('#rem_genero').addClass('disabled');
            $('#rem_genero').attr('disabled', true);
        }
        return false;
    });

    //genero - auto complete:
    
    var cache_genero = {};
    var pathArray = window.location.pathname.split( '/' ); //Obtém parte da url, necessário para pegar o tipo da obra em questão
    var type = pathArray[4]; //Obtém o tipo da obra com base na url quebrada anteriormente
    
    $('#genero').change(function(event) {
    	var str = "";
    	str = $('#genero option:selected').text();
    //	$('#genero').val(str);
        $('#genero').data('lastValue', str);
        $('#genero_id').val( $('#genero').val());
        $('#add_genero').removeClass('disabled');
        $('#add_genero').attr('disabled', false);
        return false;   
    });
   
  /*  $('#genero').autocomplete({
        minLength: 2,        
        source: function(request, response) {           
            if (request.term in cache) {
                if (typeof(cache[request.term][0]) != 'undefined' &&
                    request.term == cache[request.term][0].value) {
                    $('#add_genero').removeClass('disabled');
                    $('#add_genero').attr('disabled', false);
                }
                response(cache_genero[request.term]);                 
                return;
            }
            
            $.ajax({
                url: "../../../generos/?action=search_genero",
                dataType: "json",
                data: {
                    term : request.term,
                    type : type
                },
                success: function(data) {                    
                    cache[request.term] = data;
                    if (typeof(cache[request.term][0]) != 'undefined' &&
                        request.term == cache[request.term][0].value) {
                        $('#add_genero').removeClass('disabled');
                        $('#add_genero').attr('disabled', false);
                    }
                    response(data);                    
                }
            });           

        },
        select: function(event, ui) {
            $('#genero').val(ui.item.label);
            $('#genero').data('lastValue', $('#genero').val());
            $('#genero_id').val(ui.item.id);
            $('#add_genero').removeClass('disabled');
            $('#add_genero').attr('disabled', false);
            return false;
        }
    }); */

    $('#genero').data('lastValue', $('#genero').val());

    $('#genero').keyup(function() {
        if ($(this).data('lastValue') != $(this).val()) {
            $(this).data('lastValue', $(this).val());
            $('#add_genero').addClass('disabled');
            $('#add_genero').attr('disabled', true);
        }
    });

    $('#generos').change(function() {
        $('#rem_genero').removeClass('disabled');
        $('#rem_genero').attr('disabled', false);
    });

/* Idiomas
    ********************************************************************************************/
    
    $('#add_idioma').click(function() {
        if ($('#idioma').val() && $('#idioma_id').val()) {
        	str = $('#idioma option:selected').text();

            $('#idiomas').addOption($('#idioma_id').val(), str);
            $('#idioma').val('');
            $('#idioma_id').val('');
            $('#idioma').focus('');
            $('#add_idioma').addClass('disabled');
            $('#add_idioma').attr('disabled', true);
            $('#rem_idioma').removeClass('disabled');
            $('#rem_idioma').attr('disabled', false);
        }
        return false;
    });
    $('#rem_idioma').click(function() {
        //$('#idiomas').copyOptions('#idioma');
        $('#idiomas').removeOption(/./, true);
        if ($('#idiomas option').size() == 0) {
            $('#rem_idioma').addClass('disabled');
            $('#rem_idioma').attr('disabled', true);
        }
        return false;
    });

    $('#idioma').change(function(event) {
    	var str = "";
    	str = $('#idioma option:selected').text();
    //	$('#genero').val(str);
        $('#idioma').data('lastValue', str);
        $('#idioma_id').val( $('#idioma').val());
        $('#add_idioma').removeClass('disabled');
        $('#add_idioma').attr('disabled', false);
        return false;   
    });
    
    //idioma - auto complete:    
    /* $('#idioma').autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term in cache) {
                if (typeof(cache[request.term][0]) != 'undefined' &&
                    request.term == cache[request.term][0].value) {
                    $('#add_idioma').removeClass('disabled');
                    $('#add_idioma').attr('disabled', false);
                }
                response(cache_idioma[request.term]);
                return;
            }
          
            $.ajax({
                url: "../../../idiomas/?action=search_idioma",
                dataType: "json",
                data: request,
                success: function(data) {
                    cache[request.term] = data;
                    if (typeof(cache[request.term][0]) != 'undefined' &&
                        request.term == cache[request.term][0].value) {
                        $('#add_idioma').removeClass('disabled');
                        $('#add_idioma').attr('disabled', false);
                    }
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $('#idioma').val(ui.item.label);
            $('#idioma').data('lastValue', $('#idioma').val());
            $('#idioma_id').val(ui.item.id);
            $('#add_idioma').removeClass('disabled');
            $('#add_idioma').attr('disabled', false);
            return false;
        }
    }); */

    $('#idioma').data('lastValue', $('#idioma').val());

    $('#idioma').keyup(function() {
        if ($(this).data('lastValue') != $(this).val()) {
            $(this).data('lastValue', $(this).val());
            $('#add_idioma').addClass('disabled');
            $('#add_idioma').attr('disabled', true);
        }
    });

    $('#idiomas').change(function() {
        $('#rem_idioma').removeClass('disabled');
        $('#rem_idioma').attr('disabled', false);
    });
    
    /* Mídias
     ********************************************************************************************/
    
	$('.qq-upload-replace').click(function(e) {
		e.preventDefault();
		$(this).hide().next().css('display', 'block');
	});
	
	$('.qq-upload-replace-file').wrap('<form class="qq-upload-replace-form" method="post" action="../?action=upload" />');
	$('.qq-upload-replace-form').append('<span class="qq-upload-replace-status"></span>');
	
	$('.qq-upload-replace-file').change(function() {
		var upload_status = $(this).next('.qq-upload-replace-status');
		var replace_file = $(this).parent().parent().find('.qq-upload-replaced');
		$(this).parent().ajaxSubmit({
			beforeSubmit: function() {
				upload_status.removeClass('loading success error').html('').addClass('loading');
			},
			success: function(response, status) {
	            if (response.success === true) {
	            	replace_file.val(response.filename);
	            	upload_status.removeClass('loading success error').addClass('success').html('Arquivo enviado com sucesso!');
	            }
	        }, 
	        error: function(XMLHttpRequest, textStatus, errorThrown) {
	        	upload_status.removeClass('loading success error').addClass('error').html(textStatus+':<br />'+XMLHttpRequest.responseText);
	        },
	        dataType: 'json'
		});
	});
    
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
               	      '<span class="qq-upload-failed-text">Falhou</span>' +
               	      '<div class="clear"></div>' +
               	      '<p><label>Título</label>' +
            		  '<input type="text" name="titulos_arquivos[]" style="width: 367px" /></p>' +
            		  '<p><label>Fonte</label>' +
            		  '<input type="text" name="fontes_arquivos[]" style="width: 367px" /></p>' +
            		  '<div class="clear"></div>' +
            		  '<p><label>Descrição</label>' +
            		  '<input type="text" name="descricoes_arquivos[]" style="width: 767px" /></p>' +
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