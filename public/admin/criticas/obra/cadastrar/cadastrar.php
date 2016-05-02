<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_CRITICAS_URI; ?>"><?php echo __('Críticas');?></a> &raquo;
	 	<?php echo __('Cadastrar Crítica de Obra');?>
	 </h2>
	<br />
	<br />
    <form id="add" action="<?php echo ADMIN_CRITICAS_OBRA_URI; ?>?action=add" method="post" class="inline">
    	<fieldset>
    		<legend><?php echo __('Preencha os campos abaixo para cadastrar uma crítica de uma obra');?></legend>
    		<p><label for="titulo"><?php echo __('Título');?> *</label>
    		<input type="text" id="titulo" name="titulo" style="width: 780px" /></p>
    		<p><label for="obra"><?php echo __('Obra Literaria');?> *</label>
    		<input type="text" id="obra" name="obra" style="width: 500px" />
	        <input type="hidden" id="obra_id" name="obra_id" />
	        </p>
	        <div class="clear"></div>
            <label for="autor_critica"><?php echo __('Autor da crítica');?></label>
            <input type="text" id="autor_critica" name="autor_critica" style="width: 500px" />
    	</fieldset>
    	<fieldset>
            <legend><?php echo __('Mídia');?></legend>
            <div id="arquivo"></div>
            <div class="clear"></div>
        </fieldset>
    	<p><input type="submit" value="<?php echo __('Cadastrar');?>" disabled="disabled" class="disabled" /></p>
    	<div id="accessibility_form">
            <a href="javascript:history.go(-1)"><?php echo __('Voltar à página anterior');?></a>
        </div>
        <div id="status"></div>
    	<div class="clear"></div>
    </form>
</div>

<script type="text/javascript">
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
            //If no errors ocurred, print the success message
            if (response && response.error == null) {
            	$('#status').html('<span class="success"><?php echo __("Obra cadastrada com sucesso");?>!</span>');
            }
            else {
                //Highlight invalid fields
                if (response && typeof(response.error.length) == "undefined") {
                    $('#status').html('<span class="error"><?php echo __("Verifique o(s) campo(s) com problema(s)");?></span>');
                    var focus = true;
                    $.each(response.error, function(i, val) {
                        if (i == 'autores') {
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
        			if (!response) {
        				response = {};
        				response.error = '<?php echo __("Ocorreu um erro inesperado");?>';
        			}
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
    
    $('#add').ajaxForm(options); //Bind the form to the AJAX Form plugin
    
    
      /* Obra-Literaria
    ********************************************************************************************/

    //Obra-Literaria - auto complete:
    var cache_obra = {};
    $('#obra').autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term in cache_obra) {
                if (typeof(cache_obra[request.term][0]) != 'undefined' &&
                	request.term == cache_obra[request.term][0].value) {
                	$('#add_obra').removeClass('disabled');
                    $('#add_obra').attr('disabled', false);
                    
        	    }
                response(cache_obra[request.term]);
                return;
            }
          
            $.ajax({
                url: "../../../documentos/obra-literaria/?action=search_obra",
                dataType: "json",
                data: request,
                success: function(data) {
                    cache_obra[request.term] = data;
	            	if (typeof(cache_obra[request.term][0]) != 'undefined' &&
	            		request.term == cache_obra[request.term][0].value) {
	                	$('#add_obra').removeClass('disabled');
	                    $('#add_obra').attr('disabled', false);
	        	    }
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $('#obra').val(ui.item.label);
            $('#obra').data('lastValue', $('#obra').val());
            $('#obra_id').val(ui.item.id);
            $('#add_obra').removeClass('disabled');
            $('#add_obra').attr('disabled', false);
            return false;
        }
    });
});
</script>
