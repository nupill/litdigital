function scrollTo(element, callback, offset) {
	var position = element.offset().top;
	if (offset) {
		position+= offset;
	}
	$('html, body').animate({
		scrollTop: position
	}, 1000, function() {
	    if (callback) {
		    callback();
	    }
	});
}

// Comment vote and reply buttons
function bind_buttons() {

	/*
	 * Comment vote
	 */
	
	$('.vote_up').unbind('click').click(function() {
		var self = $(this);
		var id = self.parent().attr('id');
		$.ajax({
			url: "?action=vote_up_comment&id=" + id,
			cache: false,
			dataType: 'json',
			beforeSubmit: function() {
	            $('#status').html('<span class="loading"></span>'); //AJAX loading gif
	    	},
			success: function(response, status) {
				if (response && response.score) {
				    var score_element = self.parent().find('.score');
				    score_element.text(response.score);
				    score_element.removeClass('negative').removeClass('positive');
				 	if (response.score > 0) {
				 	    score_element.addClass('positive');
					}
				 	else if (response.score < 0) {
				 	    score_element.addClass('negative');
				 	}
				 	self.parent().find('.vote_down').removeClass('active');
				 	//self.toggleClass('active');
				 	if (self.hasClass('active')) {
						self.removeClass('active');
						self.attr('title', 'Voto positivo');
					}
				 	else {
						self.addClass('active');
						self.attr('title', 'Anular voto positivo');
				 	}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
			     
	    	}
	 	});
	});

	$('.vote_down').unbind('click').click(function() {
		var self = $(this);
		var id = self.parent().attr('id');
		$.ajax({
			url: "?action=vote_down_comment&id=" + id,
			cache: false,
			dataType: 'json',
			beforeSubmit: function() {
	            $('#status').html('<span class="loading"></span>'); //AJAX loading gif
	    	},
			success: function(response, status) {
				if (response && response.score) {
				    var score_element = self.parent().find('.score');
				    score_element.text(response.score);
				    score_element.removeClass('negative').removeClass('positive');
				 	if (response.score > 0) {
				 	    score_element.addClass('positive');
					}
				 	else if (response.score < 0) {
				 	    score_element.addClass('negative');
				 	}
				 	self.parent().find('.vote_up').removeClass('active');
				 	//self.toggleClass('active');
				 	if (self.hasClass('active')) {
						self.removeClass('active');
						self.attr('title', 'Voto negativo');
					}
				 	else {
						self.addClass('active');
						self.attr('title', 'Anular voto negativo');
				 	}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
			     
	    	}
	 	});
	});
	
	
	/*
	 * Comment report (flag as unappropriated)
	 */
	
//	$('.comment_flag').unbind('click').click(function() {
//		var self = $(this);
//		var id = self.parent().attr('id');
//		console.log('Flag ' + id);
//	});
	
	$('.comment_flag').each(function() {
		var id = $(this).parent().attr('id');
		$(this).qtip('destroy');
		$(this).qtip({
			id: 'flag-' + id,
			content: {
				text: '<form id="flag_form" method="post" action="?action=flag_comment&id=' + id + '">' + 
					  '    <label for="reason">Informe o motivo da denúncia (opcional)</label>' +
					  '    <textarea id="reason" name="reason"></textarea>' +
					  '    <input type="submit" value="Enviar" />' +
					  '    <span id="flag_status"></span>' +
					  '</form>',
				title: {
					text: 'Reportar comentário inapropriado',
					button: true
				}
			},
			position: {
				my: 'center',
				at: 'center',
				target: $(window)
			},
			show: {
				event: 'click',
				solo: true,
				modal: true
			},
			hide: false,
			events: {
				render: function(event, api) {
					//Define the options (functions) to handle the submit and response
				    var options = {
				        beforeSubmit: function() {
				    		$('#flag_status').html('<span class="loading"></span>'); //AJAX loading gif
				    	}, 
				        success: function(response, status) {
				            //If no errors ocurred, print the success message
				    		if (response && response.error == null) {
				    			$('#flag_status').html('<span class="success">Denúncia registrada com sucesso!</span>');

				    		}
				    		else {
				        		if (!response) {
				        			response = {};
				        			response.error = 'Ocorreu um erro inesperado';
				        		}
				        		//Print the error message
				        		$('#flag_status').html('<span class="error">'+response.error+'</span>');
				    		}
				    	},
				    	error: function(XMLHttpRequest, textStatus, errorThrown) {
				        	//Print the error message if the request fails (possible reasons: "timeout", "error", "notmodified" and "parsererror")
				    		$('#flag_status').html('<span class="error">'+textStatus+':<br />'+XMLHttpRequest.responseText+'</span>');
				    	},
				        dataType: 'json'
				    };
				    
				    $('#flag_form').ajaxForm(options); //Bind the form to the AJAX Form plugin
				}
			},
			style: 'ui-tooltip-light ui-tooltip-rounded dialog_flag'
		});
	});
	

	/*
	 * Reply
	 */
	
	$('.reply').click(function() {
		//var id = $(this).parent().attr('id');
		var id = $('#comment_id').val();
		
		$('#reply_form').remove();
		
		$(this).parent().parent().append('' +
		'<form id="reply_form" method="post" action="?action=reply_comment&id=' + $('#id').val() + '&parent_id=' + id + '">' +
	    '    <p>Insira sua resposta abaixo</p>' +
	    '	 <textarea id="reply" name="reply" maxlenght="4000"></textarea>' +
	    ' 	 <input type="submit" value="Enviar" />' +
	    ' 	 <input type="button" id="cancel" value="Cancelar" />' +
	    '    <span id="reply_status"></span>' +
	    '</form>');

		scrollTo($('#reply'), function() {
			$('#reply').focus();
		}, -50);
		
		$('#cancel').click(function() {
			$(this).parent().fadeOut(function() {
				$(this).remove();
			});
		});

		//Define the options (functions) to handle the submit and response
	    var options = {
	        beforeSubmit: function() {
	    		$('#reply_status').html('<span class="loading"></span>'); //AJAX loading gif
	    	}, 
	        success: function(response, status) {
	            //If no errors ocurred, print the success message
	    		if (response && response.error == null) {
	    			$('#reply_status').html('<span class="success">Resposta adicionada com sucesso!</span>');
	    			$('#reply_form').fadeOut(function() {

						$(this).parent().append('' +
		    			'<div class="comment_reply">' +
			    	    ' 	<div class="comment_reply_header">' +
			    		'     	<span class="user">' + response.usuario + '</span>' +
			    		'     	<em>' + response.data_inclusao + '</em>' +
			    		'     	<div id="' + response.id + '">' +
			    		'	     	<span class="score">0</span>' +
			    		'		    <button class="vote_up" title="Voto positivo">Up</button>' +
			    		'		    <button class="vote_down" title="Voto negativo">Down</button>' +
			    		'			<button class="comment_flag" title="Denunciar">Flag</button>' +
			    		'     	</div>' +
			    		'    </div>' +
			    		'    <div class="reply_content">' +
			    		'     	<p>' + response.conteudo + '</p>' +
			    		'     </div>' +
			    	    '</div>');

						bind_buttons();
		    			
	    				$(this).remove();
	    			});
	    			
	    		}
	    		else {
	        		if (!response) {
	        			response = {};
	        			response.error = 'Ocorreu um erro inesperado';
	        		}
	        		//Print the error message
	        		$('#reply_status').html('<span class="error">'+response.error+'</span>');
	    		}
	    	},
	    	error: function(XMLHttpRequest, textStatus, errorThrown) {
	        	//Print the error message if the request fails (possible reasons: "timeout", "error", "notmodified" and "parsererror")
	    		$('#reply_status').html('<span class="error">'+textStatus+':<br />'+XMLHttpRequest.responseText+'</span>');
	    	},
	        dataType: 'json'
	    };
	    
	    $('#reply_form').ajaxForm(options); //Bind the form to the AJAX Form plugin
	});
}

$(function() {

	/*
	 * Add
	 */
	
	//Define the options (functions) to handle the submit and response
    var options = {
        beforeSubmit: function() {
    		$('#status').html('<span class="loading"></span>'); //AJAX loading gif
    	}, 
        success: function(response, status) {
            //If no errors ocurred, print the success message
    		if (response && response.error == null) {
    			$('#status').html('<span class="success">Comentário adicionado com sucesso!</span>');

				$('#comment_count').text(parseInt($('#comment_count').text())+1);
				$('#comment_form').get(0).reset();

    			/*
				$('#comments').prepend('' +
    			'<div class="comment">' +
	    	    ' 	<div class="comment_header">' +
	    		'     	<span class="user">' + response.usuario + '</span>' +
	    		'     	<em>' + response.data_inclusao + '</em>' +
	    		'     	<div id="' + response.id + '">' +
	    		'	     	<span class="score">0</span>' +
	    		'		    <button class="vote_up">Up</button>' +
	    		'		    <button class="vote_down">Down</button>' +
	    		'	     	<button class="reply">Responder</button>' +
	    		'     	</div>' +
	    		'    </div>' +
	    		'    <div class="comment_content">' +
	    		'     	<h4>' + response.titulo + '</h4>' +
	    		'     	<p>' + response.conteudo + '</p>' +
	    		'     </div>' +
	    	    '</div>');
	    	    */

	    	    $('#comments').prepend('' +
	    	    '<div class="comment">' + 
	    	    '	<div class="comment_header">' +
			    ' 		<h4>' + response.titulo + '</h4>'+
			    ' 		<input type="hidden" id="comment_id" value="' + response.id + '" />' +
				'   	<button class="reply">Responder</button>' +
			    '	</div>' +
    			'	<div class="comment_reply">' +
	    	    ' 		<div class="comment_reply_header">' +
	    		'     		<span class="user">' + response.usuario + '</span>' +
	    		'     		<em>' + response.data_inclusao + '</em>' +
	    		'     		<div id="' + response.id + '">' +
	    		'	     		<span class="score">0</span>' +
	    		'		    	<button class="vote_up" title="Voto positivo">Up</button>' +
	    		'		    	<button class="vote_down" title="Voto negativo">Down</button>' +
	    		'				<button class="comment_flag" title="Denunciar">Flag</button>' +
	    		'     		</div>' +
	    		'   	</div>' +
	    		'    	<div class="reply_content">' +
	    		'     		<p>' + response.conteudo + '</p>' +
	    		'     	</div>' +
	    	    '	</div>' +
	    	    '</div>');
				
				bind_buttons();
    		}
    		else {
        		if (!response) {
        			response = {};
        			response.error = 'Ocorreu um erro inesperado';
        		}
        		//Print the error message
        		$('#status').html('<span class="error">'+response.error+'</span>');
    		}
    	},
    	error: function(XMLHttpRequest, textStatus, errorThrown) {
        	//Print the error message if the request fails (possible reasons: "timeout", "error", "notmodified" and "parsererror")
    		$('#status').html('<span class="error">'+textStatus+':<br />'+XMLHttpRequest.responseText+'</span>');
    	},
        dataType: 'json'
    };

	$('#comment_form').submit(function() {
		if ($('#title').hasClass('placeholder')) {
			$('#title').val('');
		}
		if ($('#comment').hasClass('placeholder')) {
			$('#comment').val('');
		}
		$('#comment_form').ajaxSubmit(options); //Submit the form through AJAX Form plugin
		return false;
	});
    
    bind_buttons();
});