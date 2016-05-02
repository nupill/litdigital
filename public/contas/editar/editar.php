<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
    <?php echo __('Meu perfil');?>
</div>

<?php
if (!Auth::check()){
     exit(__('Precisa estar logado'));
}

require_once(APPLICATION_PATH . "/controllers/ControleUsuarios.php");
$controller = ControleUsuarios::getInstance();
$id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
$usuario = $controller->get($id);
$usuario = $usuario[0];
$generos = $controller->get_generos_preferidos($id);
$autores = $controller->get_autores_preferidos($id);
$obras = $controller->get_obras_visualizadas($id);
?>

<div id="content">
<form id="edit" action="../?action=update" method="post" autocomplete="off" class="inline">
    <fieldset>
        <legend><?php echo __('Informações pessoais');?></legend>
        <p><label for="nome"><?php echo __('Nome completo');?>*</label>
        <input type="text" id="nome" name="nome" style="width: 270px" value="<?php echo $usuario['nome']; ?>" /></p>
        <p><label for="email"><?php echo __('Email');?>*</label>
        <input type="text" id="email" name="email" style="width: 270px" value="<?php echo $usuario['email']; ?>" /></p>
        <p><label for="url"><?php echo __('URL');?></label>
        <input type="text" id="url" name="url" style="width: 270px" value="<?php echo $usuario['url']; ?>" /></p>
        <p><label for="profissao"><?php echo __('Profissão');?></label>
        <input type="text" id="profissao" name="profissao" style="width: 270px" value="<?php echo $usuario['profissao']; ?>" /></p>
    </fieldset>
    <fieldset>
        <legend><?php echo __('Dados de acesso');?></legend>
        <p><label for="login">Login</label>
        <input type="text" id="login" name="login" style="width: 270px" disabled="disabled" class="disabled" value="<?php echo $usuario['login']; ?>" /></p>
         <p><label for="senha_cadastro"><?php echo __('Nova Senha');?></label>
        <input type="password" id="senha_cadastro" name="senha_cadastro" style="width: 270px" /></p>
        <p><label for="repete_senha"><?php echo __('Repita a nova senha');?></label>
        <input type="password" id="repete_senha" name="repete_senha" style="width: 270px" /></p>
    </fieldset>
    <fieldset>
        <legend><?php echo __('Anotação');?></legend>
         <?php
	    if (isset($usuario['anotacao']) && $usuario['anotacao']) {
	    ?>
	    <p><label><input type="checkbox" name="anotacao" id="anotacao" checked="checked" /> <?php echo __('Habilitar ferramenta de anotação');?></label>
	    <em><?php echo __('Habilite esta opção para que obras literárias possam ser acessadas através do DLNotes');?>.</em></p>
	    <?php
	    }
	    else {
	    ?>
	    <p><label><input type="checkbox" name="anotacao" id="anotacao" /> <?php echo __('Habilitar ferramenta de anotação');?></label>
	    <em><?php echo __('Habilite esta opção para que obras literárias possam ser acessadas através do DLNotes');?>.</em></p>
	    <?php
	    }
	    ?>
    </fieldset>
    <fieldset>
    	<legend><?php echo __('Adaptabilidade e Recomendação');?></legend>
	    <?php
	    if (isset($usuario['personalizacao']) && $usuario['personalizacao']) {
	    ?>
	    <p><label><input type="checkbox" name="personalizacao" id="personalizacao" checked="checked" /> <?php echo __('Visualizar resultados adaptados');?></label>
	    <em><?php echo __('Habilite esta opção para que as buscas levem em consideração suas preferências por Autores e Gêneros');?>.</em></p>
	    <?php
	    }
	    else {
	    ?>
	    <p><label><input type="checkbox" name="personalizacao" id="personalizacao" /> <?php echo __('Visualizar resultados adaptados');?></label>
	    <em><?php echo __('Habilite esta opção para que as buscas levem em consideração suas preferências por Autores e Gêneros');?>.</em></p>
	    <?php
	    }
	    ?>
	    <div class="clear"></div> 
	    <br />
	     <h3><?php echo __('Histórico de Obras Acessadas');?></h3>
	    <table id="obras">
			<thead>
				<tr>
	                <th></th>
					<th><?php echo __('Obra');?></th>
	                <th><?php echo __('Autor');?></th>
					<th><?php echo __('Genero');?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			if ($obras) {
			    foreach ($obras as $obra) {
			?>
				<tr id="<?php echo $obra['idObra']; ?>" class="<?php echo $obra['idObra']; ?>">
	                <td><img src="<?php echo IMAGES_URI; ?>error.png" alt="Remover obra visualizada" title="Remover obra visualizada" onclick="confirmExclusaoObra(<?php echo $id?>,<?php echo $obra['idObra']; ?>)" /></td>
					<td><?php echo $obra['titulo']; ?></td>
	                <td><?php echo $obra['nome_completo']; ?></td>
					<td><?php echo $obra['nomeGenero']; ?></td> 
				</tr>
			<?php
			    }
			}
			?>
			</tbody>
		</table>
		<em><?php echo __('Remova da lista abaixo as obras que você acessou, mas que ela não foi de seu agrado');?></em>
	</fieldset>
    <div class="clear"></div>
    <p><input type="submit" value="Atualizar" disabled="disabled" class="disabled" /></p>
    <div id="status"></div>
    <div class="clear"></div>
    <em>* <?php echo __('Campos obrigatórios');?></em>
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
                $('#status').html('<span class="success">'+"<?php echo __('Usuário atualizado com sucesso');?>"+'!</span>');
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

    $('#edit').ajaxForm(options);

   $('#obras').loadTable({
    	aoColumns: [
            { "sWidth": "18px", "sName": "id", "bSortable": false },
            { "sWidth": "500px", "sName": "Titulo" },
            { "sWidth": "200px", "sName": "Autor" },
			{ "sWidth": "200px", "sName": "Genero" }
        ],
        allowCreate: false,
    	allowDelete: false,
    	allowUpdate: false,
    	bServerSide: false,
    	sPaginationType: "two_button",
    	//fileEditForm: "editar/",
    	fnDrawCallback: function() {
            $("#obras tbody td").each(function() {
                var id = $(this).parent().attr('id');
                var type = $(this).parent().attr('class');
                type = type.split(' ');
                type = type[0];
                function confirmExclusaoObra(idObra, idUsuario) {
                    if (confirm("<?php echo __('Tem certeza que deseja excluir essa visualização de obra?');?>")) {
                        location.href='../?action=remove_obra_visualizada&id='+idUsuario+'&obra='+idObra;
                    }
                }
                //first class
                //var uri = get_document_uri(type) + 'editar/?id=' + id;
                //$(this).html('<a href="' + uri + '">' + $(this).text() + '&nbsp;</a>');
            });
        }
    });
});

function confirmExclusaoObra(idUsuario, idObra) {
                    if (confirm("<?php echo __('Tem certeza que deseja excluir essa visualização de obra?');?>")) {
                        location.href='../?action=remove_obra_visualizada&id='+idUsuario+'&obra='+idObra;
                    }
}

function confirmExclusaoGenero(idUsuario, idGenero) {
    if (confirm("<?php echo __('Tem certeza que deseja excluir esse gênero preferido?');?>")) {
        location.href='../?action=remove_genero&id='+idUsuario+'&genero='+idGenero;
    }
}

function confirmExclusaoAutor(idUsuario, idAutor) {
    if (confirm("<?php echo __('Tem certeza que deseja excluir esse autor preferido?');?>")) {
        location.href='../?action=remove_autor&id='+idUsuario+'&autor='+idAutor;
    }
}
</script> 