<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleUsuarios.php');
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    exit(__('ID não especificado'));
}
$controle_usuarios = ControleUsuarios::getInstance();
$usuario = $controle_usuarios->get($id);
if (!$usuario) {
    exit(__('Registro não encontrado. ID inválido'));
}
$usuario = $usuario[0];

$papeis = $controle_usuarios->get_papeis();
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_USUARIOS_URI; ?>"><?php echo __('Usuários');?></a> &raquo;
	 	<?php echo __('Editar usuário');?>
	</h2>
	<br />
    <form id="update" action="<?php echo ADMIN_USUARIOS_URI; ?>?action=update&id=<?php echo $id; ?>" method="post" class="inline" autocomplete="off">
        <fieldset>
            <legend><?php echo __('Informações pessoais');?></legend>
            <p><label for="nome"><?php echo __('Nome');?>*</label>
            <input type="text" id="nome" name="nome" value="<?php echo $usuario['nome']; ?>" /></p>
            <p><label for="email">E-mail*</label>
            <input type="text" id="email" name="email" value="<?php echo $usuario['email']; ?>" /></p>
            <p><label for="profissao"><?php echo __('Profissão');?></label>
            <input type="text" id="profissao" name="profissao" value="<?php echo $usuario['profissao']; ?>" /></p>
            <div class="clear"></div>
            <p><label for="url">URL</label>
            <input type="text" id="url" name="url" value="<?php echo $usuario['url']; ?>" /></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Dados de acesso');?></legend>
            <p><label for="login"><?php echo __('Login');?>*</label>
            <input type="text" id="login" name="login" value="<?php echo $usuario['login']; ?>" /></p>
            <p><label for="senha"><?php echo __('Senha');?>*</label>
            <input type="text" id="senha_placeholder" name="senha_placeholder" title="<?php echo __('Digite a nova senha aqui');?>" onfocus="showPassword()" />
            <input type="password" id="senha" name="senha" style="display: none" onblur="hidePassword()" /></p>
            <p><label for="papel"><?php echo __('Papel');?>*</label>
            <select id="papel" name="papel">
	            <?php 
	            if ($papeis) {
	                foreach ($papeis as $papel) {
	                	if ($papel['id'] == $usuario['Papel_id']) {
	            ?>
                <option value="<?php echo $papel['id']; ?>" selected="selected"><?php echo $papel['nome']; ?></option>
	            <?php
	                	}
	                	else {
                ?>
                <option value="<?php echo $papel['id']; ?>"><?php echo $papel['nome']; ?></option>
                <?php
	                	}
	                }
	            }
	            ?>
            </select></p>
        </fieldset>
        <p><input type="submit" value="Salvar" disabled="disabled" class="disabled" /></p>
        <div id="accessibility_form">
            <a href="javascript:scrollUp()"><?php echo __('Ir ao topo');?></a> | 
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
                $('#status').html('<span class="success">'+"<?php echo __('Usuário atualizado com sucesso');?>"+'!</span>');
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

    $('#update').ajaxForm(options);

    /* Email
     ********************************************************************************************/
     
	$('#email').blur(function() {
		if ($(this).val() != '<?php echo $usuario['email']; ?>') {
			$.ajax({
			    url: '<?php echo ADMIN_USUARIOS_URI; ?>?action=email_available',
			    type: 'GET',
			    dataType: 'json',
			    success: function(response) {
			    	$('#email').parent().find('.error_box').remove(); //Remove error messages (if exists)
				    if (response.available === false) {
					    $('#email').after('<div class="error_box">'+"<?php echo __('E-mail já cadastrado');?>"+'</div>');
				    }
			    }
			});
		}
	});

    /* Login
     ********************************************************************************************/
     
    $('#login').blur(function() {
    	if ($(this).val() != '<?php echo $usuario['login']; ?>') {
	        $.ajax({
	            url: '<?php echo ADMIN_USUARIOS_URI; ?>?action=login_available',
	            type: 'GET',
	            dataType: 'json',
	            success: function(response) {
	            	$('#login').parent().find('.error_box').remove(); //Remove error messages (if exists)
	                if (response.available === false) {
	                    $('#login').after('<div class="error_box">'+"<?php echo __('Login já cadastrado');?>"+'</div>');
	                }
	            }
	        });
    	}
    });
});

/* Senha
 ********************************************************************************************/

function showPassword() {
    $('#senha_placeholder').hide();
    $('#senha').show();
    $('#senha').focus();
}

function hidePassword() {
    if ($('#senha').val() === '') {
        $('#senha').hide();
        $('#senha_placeholder').show();
        $('#senha_placeholder').val($('#senha_placeholder').attr('title')).addClass('placeholder');
    }
}
</script>
