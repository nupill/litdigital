<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_FATOS_HISTORICOS_URI; ?>"><?php echo __('Fatos Históricos');?></a> &raquo;
	 	<?php echo __('Cadastrar fato');?>
	 </h2>
	<br />
	<br />
    <form id="add" action="<?php echo ADMIN_FATOS_HISTORICOS_URI; ?>?action=add" method="post" class="inline">
    	<fieldset>
    		<legend><?php echo __('Preencha os campos abaixo para incluir um fato histórico');?></legend>
    		<p><label for="ano_inicio"><?php echo __('Ano do início');?>*</label>
    		<input type="text" id="ano_inicio" name="ano_inicio" style="width: 135px" maxlength="4" /></p>
    		<p><label for="ano_fim"><?php echo __('Ano do fim');?>*</label>
    		<input type="text" id="ano_fim" name="ano_fim" style="width: 135px" maxlength="4" /></p>
    		<p><label for="fato"><?php echo __('Fato');?>*</label>
    		<input type="text" id="fato" name="fato" style="width: 600px" /></p>
    	</fieldset>
    	<p><input type="submit" value="Cadastrar" disabled="disabled" class="disabled" /></p>
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
        }, 
        success: function(response, status) {
            $('.error_box').remove(); //Remove error messages (if exists)
            //If no errors ocurred, print the success message
            if (response && response.error == null) {
            	$('#status').html('<span class="success">'+"<?php echo __('Fato cadastrado com sucesso');?>"+'!</span>');
            }
            else {
                //Highlight invalid fields
                if (response && typeof(response.error.length) == "undefined") {
                    $('#status').html('<span class="error">'+"<?php echo __('Verifique o(s) campo(s) com problema(s)');?>"+'</span>');
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
        				response.error = "<?php echo __('Ocorreu um erro inesperado');?>";
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
    
});
</script>