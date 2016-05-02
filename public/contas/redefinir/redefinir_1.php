<?php 
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleUsuarios.php');
?>

<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
    <?php echo __('Redefinição de senha');?> (<?php echo __('Passo 1 de 2');?>)
</div>

<div id="content">
	<form id="forgot" action="../?action=forgot_1" method="post" autocomplete="off">
		<fieldset>
			<legend><?php echo __('Passo 1 de 2');?>: <?php echo __('Informe seu usuário ou e-mail');?></legend>
			<p><label for="login"><?php echo __('Usuário ou e-mail');?></label>
			<input type="text" id="login" name="login" /></p>
			<br />
			<input type="submit" value="Redefinir" />
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
                $('#status').html('<span class="success">'+"<?php echo __('Verifique seu email');?>"+'.</span>');
                $('#body').load('redefinir_2.php');
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