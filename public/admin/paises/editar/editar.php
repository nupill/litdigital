<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControlePaises.php');
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    exit(__('ID não especificado'));
}
$controle = ControlePaises::getInstance();
$pais = $controle->get($id);
$fields = array();
$fields[0] = 'sameThan';
$samethan = $controle->get($id,$fields)[0]['sameThan'];

$paises = $controle->get();

if (!$pais) {
    exit(__('Registro não encontrado. ID inválido'));
}
$pais = $pais[0];

?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_PAISES_URI; ?>"><?php echo __('Paises');?></a> &raquo;
	 	<?php echo __('Editar pais');?>
	</h2>
	<br />
	<br />
    <form id="update" action="<?php echo ADMIN_PAISES_URI; ?>?action=update&id=<?php echo $id; ?>" method="post" class="inline">
        <fieldset>
            <legend><?php echo __('Utilize os campos abaixo para editar o Pais');?></legend>
            <p><label for="nome"><?php echo __('Nome do Pais');?>*</label>
            <input type="text" id="nome" name="nome" style="width: 400px" value="<?php echo $pais['nome']; ?>" /></p>
         </fieldset>
         <fieldset>   
            <legend><?php echo __('Coordenadas do País');?><a href="http://www.latlong.net/" target="_blank"> <?php echo __('(Consulte aqui)');?></a></legend>
            
    		<p><label for="latitude"><?php echo __('Latitude');?></label>
            <input type="text" id="latitude" name="latitude" style="width: 150px" value="<?php echo $pais['lat']; ?>" /></p>
			<p><label for="longitude"><?php echo __('Logitude');?></label>
            <input type="text" id="longitude" name="longitude" style="width: 150px" value="<?php echo $pais['lng']; ?>" /></p> 
    	</fieldset>

        <p><input type="submit" value="Salvar" disabled="disabled" class="disabled" /></p>
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
                $('#status').html('<span class="success">'+"<?php echo __('País editado com sucesso');?>"+'!</span>');
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
});
</script>