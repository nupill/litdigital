<?php 
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleUsuarios.php');
?>

<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>">Início</a> &rarr;
    <?php echo __('Redefinição de senha'); ?> (<?php echo __('Passo 2 de 2'); ?>)
</div>

<div id="content">
	<form id="forgot" action="../?action=forgot_2" method="post" autocomplete="off">
		<fieldset>
			<legend><?php echo __('Passo 2 de 2'); ?>: <?php echo __('Informe o código e a nova senha');?></legend>
			<p><label for="code"><?php echo __('Código recebido por email');?></label>
			<input type="text" id="code" name="code" /></p>
			<p><label for="code"><?php echo __('Nova senha');?></label>
			<input type="password" id="password" name="password" /></p>
			<p><label for="code_check"><?php echo __('Repita a nova senha');?></label>
			<input type="password" id="password_check" name="password_check" /></p>
			<br />
			<input type="submit" value="Confirmar" />
			<div id="status"></div>
		</fieldset>
	</form>
</div>

<script type="text/javascript">
$(function() {

    /* Form submission (AJAX + JSON)
    ********************************************************************************************/

    //Define the options (functions) to handle the submit and response
    var options = {
        beforeSubmit: function() {
            $('#status').html('<span class="loading"></span>'); //AJAX loading gif
        },
        success: function(response, status) {
            $('.error_box').remove(); //Remove error messages (if exists)
            //If no errors ocurred, print the success message
            if (response && response.error == null) {
                $('#status').html('<span class="success">'+"<?php echo __('Senha alterada com sucesso');?>"+'!</span>');
            }
            else {
                if (!response) {
        			response = {};
        			response.error = "<?php echo __('Ocorreu um erro inesperado');?>";
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

    $('#forgot').ajaxForm(options);
});
</script>