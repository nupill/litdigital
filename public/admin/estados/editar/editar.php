<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleEstados.php');
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    exit(__('ID não especificado'));
}
$controle = ControleEstados::getInstance();
$estadocorr = $controle->get($id);
$paiscorr =  $controle->getPais($id, array('id', 'nome'))[0];

if (!$estadocorr) {
    exit(__('Registro não encontrado. ID inválido'));
}
$estadocorr = $estadocorr[0];

require_once(APPLICATION_PATH . '/controllers/ControleLocalizacao.php');
$controle_localizacao = ControleLocalizacao::getInstance();
$paises = $controle_localizacao->getPaises();



?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_ESTADOS_URI; ?>"><?php echo __('Estados');?></a> &raquo;
	 	<?php echo __('Editar Estado');?>
	</h2>
	<br />
	<br />
    <form id="update" action="<?php echo ADMIN_ESTADOS_URI; ?>?action=update&id=<?php echo $id; ?>" method="post" class="inline">
        <fieldset>
            <legend><?php echo __('Utilize os campos abaixo para editar a cidade');?></legend>
            <p><label for="pais"><?php echo __('País');?>*</label>
            <select id="pais" name="pais">
    			<option value=""><?php echo __('Selecione');?></option>
 			<?php 
                foreach ($paises as $pais) {
                ?>
                <option <?php if ($pais['id'] == $paiscorr['id']) {echo "selected='selected'";} ?> value="<?php echo $pais['id']; ?>"><?php echo $pais['nome']; ?></option>
                <?php
                }
                ?>
			</select> 
			<input type="hidden" id="pais_id" name="pais_id" />
			</p>
            <p><label for="estado"><?php echo __('Nome do estado');?>*</label>
            <input type="text" id="estado" name="estado" style="width: 400px" value="<?php echo $estadocorr['nome']; ?>" /></p>
            </fieldset>
            <fieldset>
    		<legend><?php echo __('Coordenadas do Estado/Região');?><a href="http://www.latlong.net/" target="_blank"> <?php echo __('(Consulte aqui)');?></a></legend>
            
            <p><label for="latitude"><?php echo __('Latitude');?>*</label>
            <input type="text" id="latitude" name="latitude" style="width: 200px" value="<?php echo $estadocorr['lat']; ?>" /></p>
			
			<p><label for="longitude"><?php echo __('Longitude');?>*</label>
            <input type="text" id="longitude" name="longitude" style="width: 200px" value="<?php echo $estadocorr['lng']; ?>" /></p>
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
                $('#status').html('<span class="success">'+"<?php echo __('Cidade atualizado com sucesso');?>"+'!</span>');
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

    $('#estado').change(function() {
		if ($('#estado').val()) {
	        $('#estado_id').val( $('#estado').val());
			$('#samethan').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
    	    $.get('<?php echo ADMIN_CIDADES_URI; ?>?action=getCidades', { estadoid: $('#estado_id').val(), paisid: $('#pais_id').val()}, function(data) {
    		    if (data && data.length > 0) {
    		    	$('#samethan').attr('disabled', false);
    		        $.each(data, function(i, cidade) {
    		            $('#samethan').append(new Option(cidade['nome'], cidade['id']));
    		        });
    		    }
    	    },
    	    'json');		
		} else {
			if ($('#pais').val()!=1) { 
				$('#samethan').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
	    	    $.get('<?php echo ADMIN_CIDADES_URI; ?>?action=getCidades', { estadoid: $('#estado_id').val(), paisid: $('#pais_id').val()}, function(data) {
	    		    if (data && data.length > 0) {
	    		    	$('#samethan').attr('disabled', false);
	    		        $.each(data, function(i, cidade) {
	    		            $('#samethan').append(new Option(cidade['nome'], cidade['id']));
	    		        });
	    		    }
	    	    },
	    	    'json');
			}
		}			
});
    $('#pais').change(function() {
		if ($('#pais').val()) {
			$('#estado').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
	        $('#pais_id').val( $('#pais').val());
			if ($('#pais').val()) {
				$.get('<?php echo ADMIN_CIDADES_URI; ?>?action=getEstados', { paisid: $('#pais').val() }, function(data) {
        		    if (data && data.length > 0) {
        		    	$('#estado').attr('disabled', false);
        		        $.each(data, function(i, estado) {
        		            $('#estado').append(new Option(estado['nome'], estado['id']));
        		        });
        		    }
        	    },
        	    'json');
    			
			} 
			if ($('#pais_id').val()!=1) {
				$('#samethan').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
	    	    $.get('<?php echo ADMIN_CIDADES_URI; ?>?action=getCidades', { estadoid: $('#estado_id').val(), paisid: $('#pais_id').val()}, function(data) {
	    		    if (data && data.length > 0) {
	    		    	$('#samethan').attr('disabled', false);
	    		        $.each(data, function(i, cidade) {
	    		            $('#samethan').append(new Option(cidade['nome'], cidade['id']));
	    		        });
	    		    }
	    	    },
	    	    'json');
			}	
		}
		return false;   
	});
});
</script>
