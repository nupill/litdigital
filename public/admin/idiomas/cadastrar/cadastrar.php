<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_IDIOMAS_URI; ?>"><?php echo __('Idiomas');?></a> &raquo;
	 	<?php echo __('Cadastrar idioma');?>
	 </h2>
	<br />
	<br />
    <form id="add" action="<?php echo ADMIN_IDIOMAS_URI; ?>?action=add" method="post" class="inline">
    	<fieldset>
    		<legend><?php echo __('Preencha os campos abaixo para incluir um idioma');?></legend>
    		<p><label for="descricao"><?php echo __('Nome do Idioma');?>*</label>
    		<input type="text" id="descricao" name="descricao" style="width: 400px" /></p>
    		<p></p>
    		<p><label for="iso"><?php echo __('ISO');?>*</label>
    		<input type="text" id="iso" name="iso" style="width: 200px" /></p>
    		<p></p>
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
            	$('#status').html('<span class="success">'+"<?php echo __('Idioma cadastrado com sucesso');?>"+'!</span>');
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