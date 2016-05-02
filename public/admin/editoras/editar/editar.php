<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleLocalizacao.php');
$controle_localizacao = ControleLocalizacao::getInstance();
$paises = $controle_localizacao->getPaises();
// $estados = $controle_localizacao->getEstados();


require_once(APPLICATION_PATH . '/controllers/ControleEditoras.php');
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    exit(__('ID não especificado'));
}
$controle_editoras = ControleEditoras::getInstance();
$editora = $controle_editoras->get($id);
if (!$editora) {
    exit(__('Registro não encontrado. ID inválido'));
}
$editora = $editora[0];
$cidades = $controle_editoras->getLocalCidades($id, array('id', 'nome'));
$estados =  $controle_editoras->getLocalEstados($id, array('id', 'sigla', 'pais_id'));
$localPaises =  $controle_editoras->getLocalPaises($id, array('id', 'nome'));


?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_FATOS_HISTORICOS_URI; ?>"><?php echo __('Editora');?></a> &raquo;
	 	Editar editora
	</h2>
	<br />
	<br />
    <form id="update" action="<?php echo ADMIN_EDITORAS_URI; ?>?action=update&id=<?php echo $id; ?>" method="post" class="inline">
        <fieldset>
            <legend><?php echo __('Utilize os campos abaixo para editar a Editora/Periódico');?></legend>
            <p><label for="nome"><?php echo __('Nome da Editora');?>*</label>
            <input type="text" id="ano_inicio" name="nome" style="width: 800px" value="<?php echo $editora['nome']; ?>" /></p>
            <p><label for="periodico"><br /><input type="checkbox" id="periodico" name="periodico" <?php if ($editora['periodico']) echo 'checked="checked"'; ?> /> <?php echo __('Periódico');?></label></p>
            
            
            <p><label for="localantigo"><?php echo __('Local da Editora/Periódico cadastrado (Verificar):');?></label></p>
            <p><label for="localantigov"><?php echo $editora['local']; ?></label></p>
            <p></p>
    		
            <div>
    		<p><label for="local"><?php echo __('Local da Editora/Periódico');?>*</label> 
    		
    		<select id="pais" name="pais">
    			<option value=""><?php echo __('Selecione');?></option>
    		
 			<?php 
                foreach ($paises as $pais) {
                ?>
                <option value="<?php echo $pais['id']; ?>"><?php echo $pais['nome']; ?></option>
                <?php
                }
                ?>
			</select> 
			<input type="hidden" id="pais_id" name="pais_id" />
			
			<select id="estado" name="estado" width: "100"  style="width: 100px" >
				<option value=""><?php echo __('Selecione');?></option>
			</select>
			<input type="hidden" id="estado_id" name="estado_id" />
			<select id="cidade" name="cidade" >
				<option value=""><?php echo __('Selecione');?></option>
			</select>
			<input type="hidden" id="cidade_id" name="cidade_id" />
            <button id="add_local"  title="<?php echo __('Adicionar à lista');?>">+</button>
             <div class="clear"></div>
             <select id="locais" name="locais[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left">
             		<?php
             		
                    if ($cidades) {
                        foreach ($cidades as $localCidade) {
                        	
                    ?>
                    <option value="<?php echo $localCidade['id']."L1"; ?>"><?php echo $controle_localizacao->getCidadeEstadoString($localCidade['id']); ?></option>
                    <?php 
                        }
                    }
                    ?>
                    <?php 
                     if ($estados) {
                        foreach ($estados as $localEstado) {
                    ?>
                    <option value="<?php echo $localEstado['id']."L2"; ?>"><?php echo $localEstado['sigla'].", ".$controle_localizacao->getNomePais($localEstado['pais_id']); ?></option>
                    <?php  
                        }
                     }
                     ?>
                     <?php
                        if ($localPaises) {
                        foreach ($localPaises as $localPais) {
                    ?>
                    <option value="<?php echo $localPais['id']."L3"; ?>"><?php echo $localPais['nome']; ?></option>
                    
                    <?php
                        }
                    }
                    ?>
                </select>
             <button id="rem_local" disabled="disabled" class="disabled" title="<?php echo __('Remover da lista');?>">-</button>
			</p>
    		</div>
    		<p><label for="descricao"><?php echo __('Descrição');?></label>    		
    		<textarea id="descricao" name="descricao" style="width: 785px"><?php echo $editora['descricao']; ?></textarea></p>
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
	            	$('#status').html('<span class="success">'+"<?php echo __('Editora alterada com sucesso');?>"+'!</span>');
	            }
	            else {
	                //Highlight invalid fields
	                if (response && typeof(response.error.length) == "undefined") {
	                    $('#status').html('<span class="error">'+"<?php echo __('Verifique o(s) campo(s) com problema(s)');?>"+'</span>');
	                    var focus = true;
	                    $.each(response.error, function(i, val) {
	                    	if (i == 'nome') {
	                            $('#' + i).next().after('<div class="clear"></div>' +
	                                                    '<div class="error_box" style="width: 780px">'+val+'</div>');
	                        } else if (i == 'locais') {
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

	    $('#add').submit(function() {
	        $('#locais').selectOptions(/./);
	        $('#add').ajaxSubmit(options); //Submit the form through AJAX Form plugin
	        return false;
	    });
	    
	    $('#update').submit(function() {
	        $('#locais').selectOptions(/./);
	        $('#update').ajaxSubmit(options); //Submit the form through AJAX Form plugin
	        return false;
	    });
	    
	    /* locais
	     ********************************************************************************************/
	     
	     $('#add_local').click(function() {
		     str ='';
		     id ='';
		     
	         if ($('#pais').val() && $('#pais_id').val()) {
		         if ($('#pais_id').val()==1) {
			         /* Brasil */
			         if ( $('#cidade_id').val()) {
			        	 str = $('#cidade option:selected').text() + ", ";
			        	 id =  $('#cidade_id').val() + "L1";
			         } 
			         if ( $('#estado_id').val()) {
			        	 str = str + $('#estado option:selected').text();
			        	 id =  id + $('#estado_id').val();
				         id = id + "L2";
			         }  else {
			        	 str = str + $('#pais option:selected').text();
			        	 id =  id + $('#pais_id').val();
				         id = id + "L3";
				         
			         }
		         } else
		         {
			         /* Exterior - Cidade, Pais */
			         if ($('#cidade_id').val()) {
			        	 str = $('#cidade option:selected').text() + ", ";
			        	 id =  $('#cidade_id').val() + "L1";
			         }
			         if ( $('#estado_id').val()) {
			        	 str = str + $('#estado option:selected').text();
			        	 if (!$('#cidade_id').val())
				        	 str = str + ", ";
			        	 id =  id + $('#estado_id').val();
				         id = id + "L2";
			         }  
			         id = id + $('#pais_id').val();
			         id = id  + "L3";
			         str = str + $('#pais option:selected').text();
		         }
		         $('#locais').append(new Option(str, id));
			         
		       /*   $('#locais').addOption(id, str); */
	             $('#cidade').val('');
	             $('#estado').val('');
	             $('#pais').val('');
	             $('#cidade_id').val('');
	             $('#estado_id').val('');
	             $('#pais_id').val('');
	             $('#add_local').addClass('disabled');
	             $('#add_local').attr('disabled', true);
	             $('#rem_local').removeClass('disabled');
	             $('#rem_local').attr('disabled', false);
	         }
	         return false;
	     });
	     
	     $('#rem_local').click(function() {
	         //$('#locais').copyOptions('#local');
	         $('#locais').removeOption(/./, true);
	         if ($('#locais option').size() == 0) {
	             $('#rem_local').addClass('disabled');
	             $('#rem_local').attr('disabled', true);
	         }
	         return false;
	     });

	     

	     $('#cidade').data('lastValue', $('#local').val());
	     $('#estado').data('lastValue', $('#local').val());
	     $('#pais').data('lastValue', $('#local').val());

	     $('#cidade').keyup(function() {
	         if ($(this).data('lastValue') != $(this).val()) {
	             $(this).data('lastValue', $(this).val());
	             $('#add_local').addClass('disabled');
	             $('#add_local').attr('disabled', true);
	         }
	     });
	     $('#estado').keyup(function() {
	         if ($(this).data('lastValue') != $(this).val()) {
	             $(this).data('lastValue', $(this).val());
	             $('#add_local').addClass('disabled');
	             $('#add_local').attr('disabled', true);
	         }
	     });
	     $('#pais').keyup(function() {
	         if ($(this).data('lastValue') != $(this).val()) {
	             $(this).data('lastValue', $(this).val());
	             $('#add_local').addClass('disabled');
	             $('#add_local').attr('disabled', true);
	         }
	     });

	     // Essa deixa
	     $('#locais').change(function() {
	         $('#rem_local').removeClass('disabled');
	         $('#rem_local').attr('disabled', false);
	     });

	    $('#pais').change(function() {
			if ($('#pais').val()) {
		        $('#pais_id').val( $('#pais').val());
		        $('#estado_id').val();
		        $('#cidade_id').val();
		        
				$('#estado').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
				$.get('<?php echo ADMIN_EDITORAS_URI; ?>?action=getEstados', { paisid: $('#pais').val() }, function(data) {
	        		if (data && data.length > 0) {
	        			$('#estado').attr('disabled', false);
	        			 $.each(data, function(i, estado) {
	        		    	$('#estado').append(new Option(estado['sigla'], estado['id']));
	        			});
	        		    }
	        	    },
	        	    'json');
        	    
	    		$('#cidade').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
        	    
				if ($('#pais_id').val()!=1) {
		    	    $.get('<?php echo ADMIN_EDITORAS_URI; ?>?action=getCidades', { estadoid: $('#estado_id').val(), paisid: $('#pais_id').val()}, function(data) {
		    		    if (data && data.length > 0) {
		    		    	$('#cidade').attr('disabled', false);
		    		        $.each(data, function(i, cidade) {
		    		            $('#cidade').append(new Option(cidade['nome'], cidade['id']));
		    		        });
		    		    }
		    	    },
		    	    'json');
				}	
			}
			var str = "";
			str = $('#pais option:selected').text();
			$('#pais').data('lastValue', str);
			$('#add_local').removeClass('disabled');
			$('#add_local').attr('disabled', false);
			return false;   
		});

	   $('#estado').change(function() {
			if ($('#estado').val()) {
		        $('#estado_id').val( $('#estado').val());
				$('#cidade').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');					
	    	    $.get('<?php echo ADMIN_EDITORAS_URI; ?>?action=getCidades', { estadoid: $('#estado_id').val(), paisid: $('#pais_id').val()}, function(data) {
	    		    if (data && data.length > 0) {
	    		    	$('#cidade').attr('disabled', false);
	    		        $.each(data, function(i, cidade) {
	    		            $('#cidade').append(new Option(cidade['nome'], cidade['id']));
	    		        });
	    		    }
	    	    },
	    	    'json');		
			}
	     	var str = "";
	     	str = $('#estado option:selected').text();
	         $('#estado').data('lastValue', str);
	         $('#add_local').removeClass('disabled');
	         $('#add_local').attr('disabled', false);
				
	    
	});

	  

	   $('#cidade').change(function() {
			if ($('#estado').val() || $('#pais').val()) {
		        $('#cidade_id').val( $('#cidade').val());	
			}
			var str = "";
	     	str = $('#local option:selected').text();
	     	 $('#cidade').data('lastValue', str);
	         $('#add_local').removeClass('disabled');
	         $('#add_local').attr('disabled', false);
	         return false; 
	});

	   $.fn.addOption = function(optText, optValue){
		    var option = new Option(optText, optValue);
		    return this.append(option);
		};

		$.fn.selectOption = function(toSelect){
		 var $option = this.find("option[value='"+toSelect+"']");    
		    if($option.length > 0){  
		        //if option with the value passed on found then select it      
		        $option.prop("selected","selected");
		    }else{
		        alert("option not found");
		    }
		};
	
});
</script>