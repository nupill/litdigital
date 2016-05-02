<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleCriticasAutor.php');
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    exit(__('ID não especificado'));
}
$controle_criticas = ControleCriticasAutor::getInstance();
$critica = $controle_criticas->get($id);
if (!$critica) {
    exit(__('Registro não encontrado. ID inválido'));
}
$critica = $critica[0];
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_CRITICAS_URI; ?>"><?php echo __('Críticas');?></a> &raquo;
	 	<?php echo __('Editar Crítica à Obra');?>
	</h2>
	<br />
	<br />
    <form id="update" action="<?php echo ADMIN_CRITICAS_AUTOR_URI; ?>?action=update&id=<?php echo $id; ?>" method="post" class="inline">
        <fieldset>
    		<p><label for="titulo"><?php echo __('Título');?> *</label>
    		<input type="text" id="titulo" name="titulo" style="width: 900px" value="<?php echo $critica['titulo'] ?>"/></p>
    		<p><label for="autor"><?php echo __('Autor');?> *</label>
    		<input type="text" id="autor" name="autor" style="width: 500px" value="<?php echo $critica['Autor_nome_completo'] ?>" disabled="disabled" /></p>
    		<div class="clear"></div>
            <p><label for="autor_critica"><?php echo __('Autor da crítica');?></label>
            <input type="text" id="autor_critica" name="autor_critica" style="width: 300px" value="<?php echo $critica['autor_critica'] ?>"/></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Mídias');?></legend>
            <div id="arquivo"></div>
            <div class="clear"></div>
        </fieldset>
        <p><input type="submit" value="<?php echo __('Salvar');?>" disabled="disabled" class="disabled" /></p>
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
                $('#status').html('<span class="success"><?php echo __("Crítica atualizada com sucesso");?>!</span>');
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

    $('#update').ajaxForm(options);
});
</script>
