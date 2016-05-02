<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
    Cadastro
</div>

<div id="content">
<form id="add" action="../?action=add" method="post" class="inline" autocomplete="off">
    <?php
    if (Auth::check()) {
    ?>
    <p><?php echo __('Não é possível cadastro com usuário logado');?>.</p>
    <?php
    }
    else {
    ?>
    <h3><?php echo __('Por que criar uma conta?');?></h3>
    <p><?php echo __('O uso da biblioteca digital não exige cadastramento. O cadastramento é apenas necessário para utilizar os serviços de personalização e anotação da biblioteca.
    O serviço de personalização permite que o resultado de suas buscas na biblioteca leve em consideração o seu perfil de usuários (suas preferências em termos de
    autores e gêneros literários). O sistema de anotação permite que você crie notas (pessoais ou públicas) nos textos disponibilizados.');?></p>
    <div class="clear"></div>
    <br />
    <fieldset>
        <legend><?php echo __('Informações pessoais');?></legend>
        <p><label for="nome"><?php echo __('Nome completo');?>*</label>
        <input type="text" id="nome" name="nome" style="width: 270px" /></p>
        <p><label for="email"><?php echo __('Email');?>*</label>
        <input type="text" id="email" name="email" style="width: 270px" /></p>
        <p><label for="profissao"><?php echo __('Profissão');?></label>
        <input type="text" id="profissao" name="profissao" style="width: 270px" /></p>
        <p><label for="url">URL</label>
        <input type="text" id="url" name="url" style="width: 270px" /></p>
    </fieldset>
    <fieldset>
        <legend><?php echo __('Dados de acesso');?></legend>
        <p><label for="login_cadastro"><?php echo __('Login');?>*</label>
        <input type="text" id="login_cadastro" name="login_cadastro" style="width: 270px" /></p>
        <p><label for="senha_cadastro"><?php echo __('Senha');?>*</label>
        <input type="password" id="senha_cadastro" name="senha_cadastro" style="width: 270px" /></p>
        <p><label for="repete_senha"><?php echo __('Repita a senha');?>*</label>
        <input type="password" id="repete_senha" name="repete_senha" style="width: 270px" /></p>
    </fieldset>

    <p><input type="submit" value="Cadastrar" disabled="disabled" class="disabled" /></p>

    <div id="status"></div>
    <div class="clear"></div>
    <em>* <?php echo __('Campos obrigatórios');?></em>
    <?php
	}
	?>
</form>
</div>

<script type="text/javascript">
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
                $('#status').html('<span class="success">'+"<?php echo __('Verifique seu email para completar o cadastro');?>"+'.</span>');
				<?php
				/*
                $.post('<?php echo CONTAS_URI; ?>?action=login', { usuario: $('#login_cadastro').val(), senha: $.md5($('#senha_cadastro').val()) });
                window.setTimeout("window.location = '<?php echo ROOT_URI; ?>';", 3000);
                */
				?>
            }
            else {
                //Highlight invalid fields
                if (response && typeof(response.error.length) == "undefined") {
                    $('#status').html('<span class="error">'+"<?php echo __('Verifique o(s) campo(s) com problema(s)');?>"+'</span>');
                    var focus = true;
                    $.each(response.error, function(i, val) {
                        $('#' + i).after('<div class="error_box">'+val+'</div>');
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

    $('#add').ajaxForm(options);
});
</script>