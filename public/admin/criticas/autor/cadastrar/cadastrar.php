<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_CRITICAS_URI; ?>"><?php echo __('Críticas');?></a> &raquo;
	 	<?php echo __('Cadastrar Crítica de Autor');?>
	 </h2>
	<br />
	<br />
    <form id="add" action="<?php echo ADMIN_CRITICAS_AUTOR_URI; ?>?action=add" method="post" class="inline">
    	<fieldset>
    		<legend><?php echo __('Preencha os campos abaixo para cadastrar uma crítica a um autor');?></legend>
    		<p><label for="titulo"><?php echo __('Título');?> *</label>
    		<input type="text" id="titulo" name="titulo" style="width: 780px" /></p>
    		<p><label for="autor"><?php echo __('Autor');?> *</label>
    		<input type="text" id="autor" name="autor" style="width: 500px" />
	        <input type="hidden" id="autor_id" name="autor_id" />
	       	</p>
	        <div class="clear"></div>
            <label for="autor_critica"><?php echo __('Autor da crítica');?></label>
            <input type="text" id="autor_critica" name="autor_critica" style="width: 500px"/>
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
    	<em>* <?php echo __('Campos obrigatórios');?></em>
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
    		$('#fontes').selectOptions(/./); //Select all options
    	}, 
        success: function(response, status) {
    		$('.error_box').remove(); //Remove error messages (if exists)
            //If no errors ocurred, print the success message
    		if (response.error == null) {
    			$('#status').html('<span class="success"><?php echo __("Crítica cadastrada com sucesso");?>!</span>');
    		}
    		else {
    			//Highlight invalid fields
    			if (typeof(response.error.length) == "undefined") {
    				$('#status').html('<span class="error"><?php echo __("Verifique o(s) campo(s) com problema(s)");?></span>');
    				var focus = true;
    				$.each(response.error, function(i, val) {
    					$('#' + i).after('<div class="error_box">'+val+'</div>');
    					if (focus) {
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
    
    $('#add').ajaxForm(options); //Bind the form to the AJAX Form plugin

     /* Autores
    ********************************************************************************************/
  
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
    });
});
</script>