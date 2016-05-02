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

function scrollUp() {
	scrollTo($('body'));
}

function removedoc(id) {
	$('#status').html('<span class="loading"></span>'); //AJAX loading gif
	if (confirm('Tem certeza que deseja excluir este registro?')) {
		$.get('../?action=del', { 'ids[]': id }, function(response) {
			if (response.error == null) {
				$('#status').html('<span class="success">Registro excluído com sucesso!</span>');
				window.location = '../';
			}
			else {
				//Print the error message
                $('#status').html('<span class="error">'+response.error+'</span>');
			}
		},
		'json');
	}
}