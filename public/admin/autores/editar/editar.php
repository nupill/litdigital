<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleAutores.php');
require_once(APPLICATION_PATH . '/controllers/ControleDocumentos.php');

$controle_localizacao = ControleLocalizacao::getInstance();
$paises = $controle_localizacao->getPaises();


$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    exit(__('ID não especificado'));
}

$estadosNA=NULL;
$cidadesNA=NULL;
$estadosMA=NULL;
$cidadesMA=NULL;

$controle_autores = ControleAutores::getInstance();
$controle_documentos = ControleDocumentos::getInstance();

$autor = $controle_autores->get($id);
if (!$autor) {
    exit(__('Registro não encontrado. ID inválido'));
}
$autor = $autor[0];

$fontes = $controle_autores->get_fontes($id);
$documentos = $controle_autores->get_documentos($id);

$seculos = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX', 'XXI');
$seculos = array_reverse($seculos);
$cidadeNA = $controle_autores->getCidade(0, $id);
$estadoNA =  $controle_autores->getEstado(0, $id);

$paisNA =  $controle_autores->getPais(0, $id);



if ($paisNA) {
	$estadosNA = $controle_localizacao->getEstadosPais($paisNA['id']);		
	if ($paisNA['id']==1) {
		if ($estadoNA['id']!=0) 
			$cidadesNA = $controle_localizacao->getCidades($paisNA['id'], $estadoNA['id']);
	} else  { 
		$cidadesNA = $controle_localizacao->getCidades($paisNA['id']);
	}
}
 

$cidadeMA = $controle_autores->getCidade(1, $id);
$estadoMA =  $controle_autores->getEstado(1, $id);
$paisMA =  $controle_autores->getPais(1, $id);



if ($paisMA) {
	$estadosMA = $controle_localizacao->getEstadosPais($paisMA['id']);
	if ($paisMA['id']==1) {
		if ($estadoMA['id']!=0)
				$cidadesMA = $controle_localizacao->getCidades($paisMA['id'], $estadoMA['id']);
	} else 
		$cidadesMA = $controle_localizacao->getCidades($paisMA['id']);
} 

?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
	 	<a href="<?php echo ADMIN_AUTORES_URI; ?>"><?php echo __('Autores');?></a> &raquo;
	 	<?php echo __('Editar autor');?>
	</h2>
	<br />
    <form id="update" action="<?php echo ADMIN_AUTORES_URI; ?>?action=update&id=<?php echo $id; ?>" method="post" class="inline">
    	<fieldset>
    		<legend><?php echo __('Nome');?></legend>
    		<p><label for="nome_completo"><?php echo __('Nome Completo');?>*</label>
    		<input type="text" id="nome_completo" name="nome_completo" style="width: 510px" value="<?php echo $autor['nome_completo']; ?>" /></p>
    		<p><label for="pseudonimo"><?php echo __('Pseud&#244;nimo');?><span>(<?php echo __('Separar por vírgula');?>)</span></label>
    		<input type="text" id="pseudonimo" name="pseudonimo" value="<?php echo $autor['pseudonimo']; ?>" /></p>
                <p><label for="nome_usual"><?php echo __('Nome Usual');?></label>
    		<input type="text" id="nome_usual" name="nome_usual" style="width: 510px" value="<?php echo $autor['nome_usual']; ?>" /></p>
    	</fieldset>
    	<fieldset>
    		<legend><?php echo __('Escritor Regional');?></legend>
    		<p><label for="catarinense"><br /><input type="checkbox" id="catarinense" name="catarinense" <?php if ($autor['catarinense']) echo 'checked="checked"'; ?> /> Catarinense</label></p>
    		<p><label for="piauiense"><br /><input type="checkbox" id="piauiense" name="piauiense" <?php if ($autor['piauiense']) echo 'checked="checked"'; ?> /> Piauiense</label></p>
    	</fieldset>
    	<fieldset>
    		<legend><?php echo __('Ano/Século de Nascimento');?></legend>
    		<p><label for="ano_nascimento"><?php echo __('Ano de Nascimento');?></label>
    		<input type="text" id="ano_nascimento" name="ano_nascimento" value="<?php echo $autor['ano_nascimento']; ?>" /></p>
    		<p><label for="seculo_nascimento"><?php echo __('S&eacute;culo de Nascimento');?></label>
    		<select id="seculo_nascimento" name="seculo_nascimento">
    			<option value=""><?php echo __('Selecione');?></option>		
    		   	<?php 
    		   	foreach ($seculos as $seculo) {
    		   	?>
    		   	<option value="<?php echo $seculo; ?>" <?php if ($autor['seculo_nascimento'] == $seculo) echo 'selected="selected"'; ?>>
    		   	<?php echo $seculo; ?>
    		   	</option>
    		   	<?php
    		   	}
    		   	?>
    		</select></p>
    	</fieldset>
    	<fieldset>	    		
    		<legend><?php echo __('Local de Nascimento');?></legend>
    		<p><label for="pais_nascimento"><?php echo __('País de Nascimento');?></label>
    		<select id="paisN" name="paisN">
    			<option value=""><?php echo __('Selecione');?></option>
    		
 			<?php 
                foreach ($paises as $pais) {
                ?>
                <option <?php if ($pais['id'] == $paisNA['id']) {echo "selected='selected'";} ?> value="<?php echo $pais['id']; ?>"><?php echo $pais['nome']; ?></option>
                <?php
                }
                ?>
			</select> 
			</p>
			<input type="hidden" id="paisN_id" name="paisN_id" value="<?php if ($paisNA) echo $paisNA['id']; else echo 0;?>" />
			 
			<p><label for="estado_nascimento"><?php echo __('Estado de Nascimento');?></label>
			<select id="estadoN" name="estadoN" width: "100"  style="width: 100px" >
				<option value=""><?php echo __('Selecione');?></option>
			<?php 
				if ($estadosNA) {
                foreach ($estadosNA as $estado) {
                	
                ?>
                <option <?php if ($estado['id'] == $estadoNA['id']) {echo "selected='selected'";} ?> value="<?php echo $estado['id']; ?>"><?php echo $estado['sigla']; ?></option>
                <?php
                } }
                ?>
			</select>
			<input type="hidden" id="estadoN_id" name="estadoN_id" value="<?php if ($estadoNA) echo $estadoNA['id']; else echo 0;?>" /> 
			
			</p>
			
			<p><label for="cidade_nascimento"><?php echo __('Cidade de Nascimento');?></label>
			<select id="cidadeN" name="cidadeN" >
				<option value=""><?php echo __('Selecione');?></option>
				<?php 
				if ($cidadesNA) {
                foreach ($cidadesNA as $cidade) {
                ?>
                <option <?php if ($cidade['id'] == $cidadeNA['id']) {echo "selected='selected'";} ?> value="<?php echo $cidade['id']; ?>"><?php echo $cidade['nome']; ?></option>
                <?php
                }}
                ?>
			</select>
			<input type="hidden" id="cidadeN_id" name="cidadeN_id" value="<?php if ($cidadeNA) echo $cidadeNA['id']; else echo 0;?>"/></p>
			
			<p><label for="detalhes_nasc"><?php echo __('Detalhes sobre o nascimento');?></label>
    		<input maxlength="255" type="text" id="detalhes_nasc" name="detalhes_nasc" style="width: 690px" <?php if ($autor['detalhes_nasc'])  echo 'value="'.$autor['detalhes_nasc'];  ?>"/></p>
    		
    	</fieldset>
    	<fieldset>
    		<legend><?php echo __('Ano/Século de Falecimento');?></legend>
    		<p><label for="ano_morte"><?php echo __('Ano da Morte');?></label>
    		<input type="text" id="ano_morte" name="ano_morte" value="<?php echo $autor['ano_morte']; ?>" /></p>
    		<p><label for="seculo_morte"><?php echo __('S&eacute;culo da Morte');?></label>
    		<select id="seculo_morte" name="seculo_morte">
    			<option value=""><?php echo __('Selecione');?></option>		
				<?php 
    		   	foreach ($seculos as $seculo) {
    		   	?>
    		   	<option value="<?php echo $seculo; ?>" <?php if ($autor['seculo_morte'] == $seculo) echo 'selected="selected"'; ?>>
    		   	<?php echo $seculo; ?>
    		   	</option>
    		   	<?php
    		   	}
    		   	?>
    		</select></p>
    	</fieldset>
    	<fieldset>
    	<legend><?php echo __('Local de Falecimento');?></legend>    		
    		<p><label for="pais_falecimento"><?php echo __('País de Falecimento');?></label>
    		<select id="paisM" name="paisM">
    			<option value=""><?php echo __('Selecione');?></option>
    		
 			<?php 
                foreach ($paises as $pais) {
                ?>
                <option <?php if ($pais['id'] == $paisMA['id']) {echo "selected='selected'";} ?> value="<?php echo $pais['id']; ?>"><?php echo $pais['nome']; ?></option>
                <?php
                }
                ?>
			</select> 
			<input type="hidden" id="paisM_id" name="paisM_id" value="<?php if ($paisMA) echo $paisMA['id']; else echo 0;?>"/>
			
    		<p><label for="estado_falecimento"><?php echo __('Estado de Falecimento');?></label>
			<select id="estadoM" name="estadoM" width: "100"  style="width: 100px" >
				<option value=""><?php echo __('Selecione');?></option>
				<?php 
				if ($estadosMA) {
                foreach ($estadosMA as $estado) {
                ?>
                <option <?php if ($estado['id'] == $estadoMA['id']) {echo "selected='selected'";} ?> value="<?php echo $estado['id']; ?>"><?php echo $estado['sigla']; ?></option>
                <?php
                }}
                ?>
			</select>
			<input type="hidden" id="estadoM_id" name="estadoM_id" value="<?php if ($estadoMA) echo $estadoMA['id']; else echo 0;?>"/>
			<p><label for="cidade_falecimento"><?php echo __('Cidade de Falecimento');?></label>
			
			<select id="cidadeM" name="cidadeM" >
				<option value=""><?php echo __('Selecione');?></option>
				<?php 
				if ($cidadesMA) {
                foreach ($cidadesMA as $cidade) {
                ?>
                <option <?php if ($cidade['id'] == $cidadeMA['id']) {echo "selected='selected'";} ?> value="<?php echo $cidade['id']; ?>"><?php echo $cidade['nome']; ?></option>
                <?php
                } }
                ?>
			</select>
			<input type="hidden" id="cidadeM_id" name="cidadeM_id" value="<?php if ($cidadeMA) echo $cidadeMA['id']; else echo 0;?>"/> </p>  
			
			<p><label for="detalhes_morte"><?php echo __('Detalhes sobre o falecimento');?></label>
			<input maxlength="255" type="text" id="detalhes_morte" name="detalhes_morte" style="width: 690px" <?php if ($autor['detalhes_morte'])  echo 'value="'.$autor['detalhes_morte']; ?>"/> </p>
    		  		
    	</fieldset>
    	<fieldset>
    		<legend><?php echo __('Outras informações');?></legend>
    		<p><label for="fonte"><?php echo __('Fontes');?>*</label>
            <input type="text" id="fonte" name="fonte" title="<?php echo __('Digite o nome da fonte, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista');?>" style="width: 780px" />
            <input type="hidden" id="fonte_id" name="fonte_id" />
            <button id="add_fonte" disabled="disabled" class="disabled" title="<?php echo __('Adicionar à lista');?>">+</button>
            <div class="clear"></div>
            <select id="fontes" name="fontes[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left">
            <?php 
            if ($fontes) {
                foreach ($fontes as $fonte) {
            ?>    
                <option value="<?php echo $fonte['id']; ?>"><?php echo $fonte['descricao']; ?></option>
            <?php  
                }
            }
            ?>
            </select>
            <button id="rem_fonte" disabled="disabled" class="disabled" title="<?php echo __('Remover da lista');?>">-</button>
            </p>
    		<div class="clear"></div>
    		<p><label for="descricao"><?php echo __('Descrição');?></label>
    		<textarea id="descricao" name="descricao" style="width: 785px"><?php echo $autor['descricao']; ?></textarea></p>
    		<p><label for="sexo"><?php echo __('Sexo');?>*</label>
    		<select id="sexo" name="sexo">
	    		<option value="" <?php if (!$autor['sexo']) { echo 'selected="selected"'; } ?>></option>
	    		<option value="M" <?php if ($autor['sexo'] == 'M') { echo 'selected="selected"'; } ?>><?php echo __('Masculino');?></option>
	    		<option value="F" <?php if ($autor['sexo'] == 'F') { echo 'selected="selected"'; } ?>><?php echo __('Feminino');?></option>
	    		<option value="I" <?php if ($autor['sexo'] == 'I') { echo 'selected="selected"'; } ?>><?php echo __('Indefinido');?></option>
			</select></p>
    	</fieldset>
    	<p><input type="submit" value="<?php echo __('Salvar');?>" disabled="disabled" class="disabled" /></p>
    	<div id="accessibility_form">
            <a href="javascript:scrollUp()"><?php echo __('Ir ao topo');?></a> | 
            <a href="javascript:history.go(-1)"><?php echo __('Voltar à página anterior');?></a>
        </div>
        <div id="status"></div>
    	<div class="clear"></div>
    	<em>* <?php echo __('Campos obrigatórios');?></em>
    </form>
    <br />
    <hr />
    <h3><?php echo __('Obras do autor');?></h3>
    <table id="obras">
		<thead>
			<tr>
				<th><?php echo __('Título');?></th>
				<th><?php echo __('Tipo');?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		if ($documentos) {
		    foreach ($documentos as $documento) {
		?>
			<tr id="<?php echo $documento['id']; ?>" class="<?php echo $documento['TipoDocumento_id']; ?>">
				<td><?php echo $documento['titulo']; ?></td>
				<td><?php echo $documento['tipo']; ?></td>
			</tr>
		<?php 
		    }
		}
		?>
		</tbody>
	</table>
	<!--
	<br />
    <hr />
    <h3>Log</h3>
    <table id="log">
		<thead>
			<tr>
				<th>Data</th>
				<th>Ação</th>
				<th>Autor</th>
			</tr>
		</thead>
		<tbody>
			<tr style="cursor: default">
				<td>10/05/2010 - 08:23</td>
				<td>CADASTRO</td>
				<td>Jonatan</td>
			</tr>
			<tr style="cursor: default">
				<td>10/05/2010 - 14:53</td>
				<td>EDIÇÃO</td>
				<td>Roberto</td>
			</tr>
			<tr style="cursor: default">
				<td>02/09/2010 - 11:02</td>
				<td>EDIÇÃO</td>
				<td>Jonatan</td>
			</tr>
		</tbody>
	</table>
	 -->
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
    			$('#status').html('<span class="success"><?php echo __("Autor atualizado com sucesso");?>!</span>');
    		}
    		else {
    			//Highlight invalid fields
    			if (response && typeof(response.error.length) == "undefined") {
    				$('#status').html('<span class="error"><?php echo __("Verifique o(s) campo(s) com problema(s)");?></span>');
    				var focus = true;
    				$.each(response.error, function(i, val) {
    					if (i == 'fontes') {
                            $('#' + i).next().after('<div class="clear"></div>' +
                                                    '<div class="error_box" style="width: 780px">'+val+'</div>');
                        }
    					else {
    						$('#' + i).after('<div class="error_box">'+val+'</div>');
    					}
    					if (focus) {
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

    $('#update').submit(function() {
        $('#fontes').selectOptions(/./); //Select all options
        $('#update').ajaxSubmit(options); //Submit the form through AJAX Form plugin
        return false;
    });
    
 /* Locais de morte */
    
    $('#paisM').change(function() {
		$('#estadoM_id').val('');
        $('#cidadeM_id').val('');       
		$('#paisM_id').val( $('#paisM').val());
        $('#estadoM').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
		$('#cidadeM').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
	    
		if ($('#paisM').val()) {
			$.get('<?php echo ADMIN_AUTORES_URI; ?>?action=getEstados', { paisid: $('#paisM').val() }, function(data) {
	        	if (data && data.length > 0) {
	        		$('#estadoM').attr('disabled', false);
	        		$.each(data, function(i, estado) {
	        			$('#estadoM').append(new Option(estado['sigla'], estado['id']));
	        		});
	        	}
	        },
	       	'json');
        	if ($('#pais_id').val()!=1) {
		    	$.get('<?php echo ADMIN_AUTORES_URI; ?>?action=getCidades', { estadoid: $('#estadoM_id').val(), paisid: $('#paisM_id').val()}, function(data) {
		    		if (data && data.length > 0) {
		    			$('#cidadeM').attr('disabled', false);
		    			$.each(data, function(i, cidade) {
		    				$('#cidadeM').append(new Option(cidade['nome'], cidade['id']));
		    			});
		    		}
		  		},
		   		'json');
			}
		}	 
		var str = "";
		str = $('#paisM option:selected').text();
		$('#paisM').data('lastValue', str);
		return false;   
		});

	   $('#estadoM').change(function() {
			if ($('#estadoM').val()) {
		        $('#estadoM_id').val( $('#estadoM').val());
		        $('#cidadeM_id').val('');
				$('#cidadeM').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');					
	    	    $.get('<?php echo ADMIN_AUTORES_URI; ?>?action=getCidades', { estadoid: $('#estadoM_id').val(), paisid: $('#paisM_id').val()}, function(data) {
	    		    if (data && data.length > 0) {
	    		    	$('#cidadeM').attr('disabled', false);
	    		        $.each(data, function(i, cidade) {
	    		            $('#cidadeM').append(new Option(cidade['nome'], cidade['id']));
	    		        });
	    		    }
	    	    },
	    	    'json');		
			}
	     	var str = "";
	     	str = $('#estadoM option:selected').text();
	         $('#estadoM').data('lastValue', str);
				
	    
	});

	  

	   $('#cidadeM').change(function() {
			if ($('#estadoM').val() || $('#paisM').val()) {
		        $('#cidadeM_id').val( $('#cidadeM').val());	
			}
			var str = "";
	     	str = $('#localM option:selected').text();
	     	 $('#cidadeM').data('lastValue', str);
	         return false; 
	});

	   /* Locais de nascimento */
	    
$('#paisN').change(function() {
		$('#estadoN_id').val('');
        $('#cidadeN_id').val('');       
		$('#paisN_id').val( $('#paisN').val());
        $('#estadoN').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
		$('#cidadeN').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');
	    
		if ($('#paisN').val()) {
			$.get('<?php echo ADMIN_AUTORES_URI; ?>?action=getEstados', { paisid: $('#paisN').val() }, function(data) {
	        	if (data && data.length > 0) {
	        		$('#estadoN').attr('disabled', false);
	        		$.each(data, function(i, estado) {
	        			$('#estadoN').append(new Option(estado['sigla'], estado['id']));
	        		});
	        	}
	        },
	       	'json');
        	if ($('#pais_id').val()!=1) {
		    	$.get('<?php echo ADMIN_AUTORES_URI; ?>?action=getCidades', { estadoid: $('#estadoN_id').val(), paisid: $('#paisN_id').val()}, function(data) {
		    		if (data && data.length > 0) {
		    			$('#cidadeN').attr('disabled', false);
		    			$.each(data, function(i, cidade) {
		    				$('#cidadeN').append(new Option(cidade['nome'], cidade['id']));
		    			});
		    		}
		  		},
		   		'json');
			}
		}	 
		var str = "";
		str = $('#paisN option:selected').text();
		$('#paisN').data('lastValue', str);
		return false;   
		});
		
		   $('#estadoN').change(function() {
				if ($('#estadoN').val()) {
			        $('#estadoN_id').val( $('#estadoN').val());
			        $('#cidadeN_id').val('');
					$('#cidadeN').empty().append('<option selected="selected" value="">'+"<?php echo __('Selecione');?>"+'</option>');					
		    	    $.get('<?php echo ADMIN_AUTORES_URI; ?>?action=getCidades', { estadoid: $('#estadoN_id').val(), paisid: $('#paisN_id').val()}, function(data) {
		    		    if (data && data.length > 0) {
		    		    	$('#cidadeN').attr('disabled', false);
		    		        $.each(data, function(i, cidade) {
		    		            $('#cidadeN').append(new Option(cidade['nome'], cidade['id']));
		    		        });
		    		    }
		    	    },
		    	    'json');		
				}
		     	var str = "";
		     	str = $('#estadoN option:selected').text();
		         $('#estadoN').data('lastValue', str);
					
		    
		});

		  

		   $('#cidadeN').change(function() {
				if ($('#estadoN').val() || $('#paisN').val()) {
			        $('#cidadeN_id').val( $('#cidadeN').val());	
				}
				var str = "";
		     	str = $('#localN option:selected').text();
		     	 $('#cidadeN').data('lastValue', str);
		         return false; 
		});

			 /* Fontes
     ********************************************************************************************/
     
    $('#add_fonte').click(function() {
        if ($('#fonte').val() && $('#fonte_id').val()) {
            $('#fontes').addOption($('#fonte_id').val(), $('#fonte').val());
            $('#fonte').val('');
            $('#fonte_id').val('');
            $('#fonte').focus('');
            $('#add_fonte').addClass('disabled');
            $('#add_fonte').attr('disabled', true);
            $('#rem_fonte').removeClass('disabled');
            $('#rem_fonte').attr('disabled', false);
        }
        return false;
    });
    $('#rem_fonte').click(function() {
        //$('#fontes').copyOptions('#fonte');
        $('#fontes').removeOption(/./, true);
        if ($('#fontes option').size() == 0) {
            $('#rem_fonte').addClass('disabled');
            $('#rem_fonte').attr('disabled', true);
        }
        return false;
    });

    //Fonte - auto complete:
    var cache = {};
    $('#fonte').autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term in cache) {
            	if (typeof(cache[request.term][0]) != 'undefined' &&
                	request.term == cache[request.term][0].value) {
                	$('#add_fonte').removeClass('disabled');
                    $('#add_fonte').attr('disabled', false);
        	    }
                response(cache[request.term]);
                return;
            }
            
            $.ajax({
            	url: "<?php echo ADMIN_FONTES_URI; ?>?action=search_fonte",
                dataType: "json",
                data: request,
                success: function(data) {
                    cache[request.term] = data;
                    if (typeof(cache[request.term][0]) != 'undefined' &&
                    	request.term == cache[request.term][0].value) {
                    	$('#add_fonte').removeClass('disabled');
                        $('#add_fonte').attr('disabled', false);
                    }
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $('#fonte').val(ui.item.label);
            $('#fonte').data('lastValue', $('#fonte').val());
            $('#fonte_id').val(ui.item.id);
            $('#add_fonte').removeClass('disabled');
            $('#add_fonte').attr('disabled', false);
            return false;
        }
    });

    $('#fonte').data('lastValue', $('#fonte').val());

    $('#fonte').keyup(function() {
    	if ($(this).data('lastValue') != $(this).val()) {
    		$(this).data('lastValue', $(this).val());
	        $('#add_fonte').addClass('disabled');
	        $('#add_fonte').attr('disabled', true);
    	}
    });

    $('#fontes').change(function() {
        $('#rem_fonte').removeClass('disabled');
        $('#rem_fonte').attr('disabled', false);
    });
    
    // AUTOCOMPLETE SECULO
    
    $('#ano_nascimento').keyup(function(){
    	
    	var seculos = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX', 'XXI'];
    	
    	var primeiros = $(this).val().substring(0,2);
    	var ultimos = $(this).val().substring(2,4);
    	var seculo = 0;
    	
    	if (ultimos == 00) {
    		seculo = primeiros;
    	}
    	else {
    		seculo = parseInt(primeiros) + parseInt(1);
    	}
    	
		if( $("#seculo_nascimento").containsOption(seculos[seculo])){
			$("#seculo_nascimento").val(seculos[seculo]).attr('selected',true);
    		
    	}
    });
    
    $('#ano_morte').keyup(function(){
    	
    	var seculos = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX', 'XXI'];
    	
    	var primeiros = $(this).val().substring(0,2);
    	var ultimos = $(this).val().substring(2,4);
    	var seculo = 0;
    	
    	if (ultimos == 00) {
    		seculo = primeiros;
    	}
    	else {
    		seculo = parseInt(primeiros) + parseInt(1);
    	}
    	
		if( $("#seculo_morte").containsOption(seculos[seculo])){
			$("#seculo_morte").val(seculos[seculo]).attr('selected',true);
    		
    	}
    });
    

    /* Obras
     ********************************************************************************************/

    $('#obras').loadTable({
    	aoColumns: [
            { "sWidth": "750px", "sName": "titulo" },
            { "sName": "tipo" },
        ],
        allowCreate: false,
    	allowDelete: false,
    	allowUpdate: false,
    	bServerSide: false,
    	sPaginationType: "two_button",
    	//fileEditForm: "<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>editar/",
    	fnDrawCallback: function() {
            $("#obras tbody td").each(function() {
                var id = $(this).parent().attr('id');
                var type = $(this).parent().attr('class');
                type = type.split(' ');
                type = type[0]; //first class
                var uri = get_document_uri(type) + 'editar/?id=' + id;
                $(this).html('<a href="' + uri + '">' + $(this).text() + '&nbsp;</a>');
            });
        }
    });

    /**
     * Return the document URI according to its ID
     * @param $id - Type ID
     * @return string - URI
     */
    function get_document_uri(id) {
        switch (id) {
            case '<?php echo DOCUMENTOS_AUDIOVISUAIS_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_AUDIOVISUAIS_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_BIBLIOTECA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_BIBLIOTECA_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_COMPROVANTES_ADAPTACOES_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_ADAPTACOES_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_COMPROVANTES_EDICOES_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_EDICOES_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_CORRESPONDENCIAS_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_CORRESPONDENCIAS_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_COMPROVANTES_CRITICA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_CRITICA_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_HISTORIA_EDITORIAL_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_EDICOES_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_ILUSTRACOES_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_ILUSTRACOES_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_MEMORABILIA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_MEMORABILIA_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_ESBOCOS_NOTAS_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_ESBOCOS_NOTAS_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_OBJETOS_ARTE_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_OBJETOS_ARTE_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_OBRA_LITERARIA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_ORIGINAIS_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_ORIGINAIS_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_PUBLICACOES_IMPRENSA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_PUBLICACOES_IMPRENSA_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_VIDA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_VIDA_URI; ?>';
                break;
            case '<?php echo DOCUMENTOS_OBRA_ID; ?>':
                return '<?php echo ADMIN_DOCUMENTOS_OBRA_URI; ?>';
                break;
            default:
                return '<?php echo ADMIN_DOCUMENTOS_URI; ?>';
        }
    }
    
    /* Log
     ********************************************************************************************/
    
    /*$('#log').loadTable({
    	allowCreate: false,
    	allowDelete: false,
    	allowUpdate: false,
    	bServerSide: false,
    	sPaginationType: "two_button",
    	aoColumns: [
    		{ "sWidth": "180px", "sType": "date-euro" },
    		null,
    		null
    	]
    });*/


    
});
</script>