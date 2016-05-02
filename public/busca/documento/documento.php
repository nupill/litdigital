<?php
/**
 * Formulário para busca por Obra (Documento)
 */
require_once (dirname ( __FILE__ ) . '/../../../application/controllers/ControleDocumentos.php');
require_once (dirname ( __FILE__ ) . '/../../../application/controllers/ControleFontes.php');

$controle_documentos = ControleDocumentos::getInstance ();
$controle_fontes = ControleFontes::getInstance ();

$tipos = $controle_documentos->get_tipos ( null, true );
$generos = $controle_documentos->get_generos ( DOCUMENTOS_OBRA_LITERARIA_ID );
// $generos = $controle_documentos->get_generos();
// $categorias = $controle_documentos->get_categorias(DOCUMENTOS_OBRA_LITERARIA_ID);
$idiomas = $controle_documentos->get_idiomas ();

$fontes = $controle_fontes->get ();

$seculos = array (
		'I',
		'II',
		'III',
		'IV',
		'V',
		'VI',
		'VII',
		'VIII',
		'IX',
		'X',
		'XI',
		'XII',
		'XIII',
		'XIV',
		'XV',
		'XVI',
		'XVII',
		'XVIII',
		'XIX',
		'XX',
		'XXI' 
);
$seculos = array_reverse ( $seculos );
?>
<form id="formBuscaDoc" name="formBuscaDoc"
	action="<?php echo BUSCA_URI; ?>documento/" method="post"
	class="inline">
	<fieldset>
		<legend><?php echo __('Informações gerais');?></legend>
		<p>
			<label for="titulo"><?php echo __('Título');?></label> <input
				type="text" id="titulo" name="titulo" style="width: 600px"
				title="<?php echo __('Exemplo: Dom Casmurro');?>" /> <select
				id="forma_busca" name="forma_busca" style="width: 180px"
				disabled="disabled">
				<option value="1"><?php echo __('Qualquer palavra');?></option>
				<option value="2" selected="selected"><?php echo __('Frase exata');?></option>
			</select>
		</p>
		<p>
			<label for="autores"><?php echo __('Autor(es)');?><span><?php echo __('Separados por vírgula');?></span></label>
			<input type="text" id="autores" name="autores" style="width: 785px" />
		</p>
	</fieldset>
	<fieldset>
		<legend><?php echo __('Classificação');?></legend>
		<p>
			<label for="tipo"><?php echo __('Tipo');?></label> <select id="tipo"
				name="tipo">
				<option value=""><?php echo __('Todos');?></option>
				<option value="<?php echo DOCUMENTOS_OBRA_LITERARIA_ID; ?>"><?php echo __('Obra Literária');?></option>
				<option value="acervo"><?php echo __('Acervo pessoal');?></option>
			</select>
		</p>
		<p id="genero_container">
			<label for="genero"><?php echo __('Gênero');?></label> <select
				id="genero" name="genero" disabled="disabled">
				<option value=""><?php echo __('Todos');?></option>
        	<?php
									foreach ( $generos as $array => $genero ) {
										?>
            <option value="<?php echo $genero['id'] ?>"><?php echo $genero['nome']; ?></option>
            <?php
									}
									?>  
        </select>
		</p>
		<p id="tipo_acervo_container" style="display: none">
			<label for="tipo_acervo"><?php echo __('Tipo do documento');?></label>
			<select id="tipo_acervo" name="tipo_acervo">
				<option value=""><?php echo __('Todos');?></option>     
            <?php
												foreach ( $tipos as $id => $tipo ) {
													if ($id != DOCUMENTOS_OBRA_LITERARIA_ID) {
														?>
            <option value="<?php echo $id; ?>"><?php echo __($tipo); ?></option>
            <?php
													}
												}
												?>
        </select>
		</p>
		<p>
			<label for="categoria"><?php echo __('Categoria');?></label> <select
				id="categoria" name="categoria" disabled="disabled">
				<option value=""><?php echo __('Todas');?></option>
			</select>
		</p>
	</fieldset>
	<fieldset>

		<legend><?php echo __('Período');?></legend>
		<p>
			<label for="periodo_tipo"><?php echo __('Período');?></label> <select
				id="periodo_tipo" name="periodo_tipo" style="width: 100px">
				<option value="ano" selected="selected"><?php echo __('Ano');?></option>
				<option value="seculo"><?php echo __('Século');?></option>
			</select>
        <?php echo __('de');?>
        <input type="text" id="ano_inicio" name="ano_inicio"
				maxlength="4" style="width: 90px" /> <select id="seculo_inicio"
				name="seculo_inicio" style="width: 102px; display: none">
				<option value=""><?php echo __('Selecione');?></option>     
            <?php
												foreach ( $seculos as $seculo ) {
													?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
												}
												?>
        </select>
        <?php echo __('até');?>
        <input type="text" id="ano_fim" name="ano_fim" maxlength="4"
				style="width: 90px" /> <select id="seculo_fim" name="seculo_fim"
				style="width: 102px; display: none">
				<option value=""><?php echo __('Selecione');?></option>     
            <?php
												foreach ( $seculos as $seculo ) {
													?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
												}
												?>
        </select>
		</p>
	</fieldset>
	<fieldset>
		<legend><?php echo __('Outros');?></legend>
		<p>
			<label for="descricao"><?php echo __('Descrição');?></label> <input
				type="text" id="descricao" name="descricao" style="width: 510px" />
		</p>
		<p>
			<label for="idioma"><?php echo __('Idioma');?></label> <select
				id="idioma" name="idioma">
				<option value="">Todos</option>      
            <?php
												foreach ( $idiomas as $idioma ) {
													?>
            <option value="<?php echo $idioma['id']; ?>"><?php echo $idioma['descricao']; ?></option>
            <?php
												}
												?>
        </select>
		</p>
		<p>
			<label for="editora"><?php echo __('Editora');?></label> <input
				type="text" id="editora" name="editora" style="width: 375px" /> <label
				for="localeditora"><?php echo __('Local da Edição');?></label> <input
				type="text" id="localeditora" name="localeditora"
				style="width: 375px" />
		</p>

		<p>
			<label for="fonte"><?php echo __('Fonte');?></label> <select
				id="fonte" name="fonte" style="width: 795px">
				<option value=""><?php echo __('Todas');?></option>      
            <?php
												foreach ( $fontes as $fonte ) {
													if ($fonte ['id'] == 1 || $fonte ['id'] == 3 || $fonte ['id'] == 36) {
														$fonte ['descricao'] = strlen ( $fonte ['descricao'] ) > 140 ? substr ( $fonte ['descricao'], 0, 140 ) . '...' : $fonte ['descricao'];
														?>
            <option value="<?php echo $fonte['id']; ?>"><?php echo $fonte['descricao']; ?></option>
            <?php
													}
												}
												?>
        </select>
		</p>
	</fieldset>
	<p>
		<input id="docPesquisar" name=""docPesquisar"" type="submit"
			value="<?php echo __('Pesquisar');?>" />
	</p>
	<div id="status"></div>
	<div class="clear"></div>
</form>
<script type="text/javascript"
	src="<?php echo JS_URI ?>jquery-ui.custom.min.js"></script>
<link rel="stylesheet" type="text/css"
	href="<?php echo CSS_URI ?>smoothness/jquery-ui.custom.css" />

<script type="text/javascript">
$(function() {

	$('#docPesquisar').click(function() {
		if ($('#forma_busca').val() == '2' && $('#titulo').val() && $('#titulo').val().slice(0,1) != '"' && $('#titulo').val().slice(-1) != '"') {
			$('#titulo').val('"' + $('#titulo').val() + '"');
		}
	});

	function check_search_type() {
		if ($("#titulo").val()) {
			$('#forma_busca').attr('disabled', false);
		}
		else {
			$('#forma_busca').attr('disabled', true);
		}
		
	}
	
	var inp = $("#titulo")[0];
	if ("onpropertychange" in inp) {
	    inp.attachEvent('onpropertychange',$.proxy(function () {
	        if (event.propertyName == "value")
	            check_search_type();
	    }, inp));
	}
	else {
	    inp.addEventListener("input", function () {
	    	check_search_type();
	    }, false);
	}

	$('#tipo').change(function() {
		
        if ($(this).val() == 'acervo') { // Se for acervo
            $('#genero_container').hide();
            $('#tipo_acervo_container').show();
            $("select#tipo_acervo option[value='']").attr("selected", "selected");
        	$("select#categoria option[value='']").attr("selected", "selected");
            $('#categoria').attr('disabled', true);
          //  $('#categoria').addClass('disabled');
          //  $('#tipo_acervo').removeClass('disabled');
            $('#tipo_acervo').change();
            
            $('#tipo_acervo').change(function() {
          //  	$('#categoria').removeClass('disabled');
            	$('#categoria').attr('disabled', false);
            	$.get('<?php echo BUSCA_URI; ?>documento/?action=getCategorias', { tipo: $(this).val() }, function(data) {
                if (data && data.length > 0) {
             //   	$('#categoria').removeClass('disabled');
                    $('#categoria').attr('disabled', false);
                    $.each(data, function(i, categoria) {
                        if (categoria['TipoDocumento_id'] != <?php echo DOCUMENTOS_OBRA_LITERARIA_ID; ?>) {
                            $('#categoria').append(new Option(categoria['nome'], categoria['id']));
                        }
                    });
                }
            },
            'json');
            });
            
        }
        else if ($(this).val() == <?php echo DOCUMENTOS_OBRA_LITERARIA_ID; ?>) { // Se for obra literaria
        	$('#tipo_acervo_container').hide();
            $('#genero_container').show();
            $("select#genero option[value='']").attr("selected", "selected");
        	$("select#categoria option[value='']").attr("selected", "selected");
            $('#categoria').attr('disabled', true);
        //    $('#categoria').addClass('disabled');
            $('#genero').attr('disabled', false);
        //    $('#genero').addClass('disabled');
        //    $('#genero').removeClass('disabled');
        	/*
        	$('#genero').empty().append('<option selected="selected" value="">Todos</option>')
            $.get('<?php echo BUSCA_URI; ?>documento/?action=getGeneros', { tipo: $(this).val() }, function(data) {
                if (data && data.length > 0) {
                    $.each(data, function(i, genero) {
                        if (genero['TipoDocumento_id'] != <?php echo DOCUMENTOS_OBRA_LITERARIA_ID; ?>) {
                            $('#genero').append(new Option(genero['nome'], genero['id']));
                        }
                    });
                }
            },
            'json');
            */
            $('#genero').change(function() {
            	$("select#categoria option[value='']").attr("selected", "selected");
            	$('#categoria').attr('disabled', true);
            	$('#categoria').empty().append('<option selected="selected" value="">Todas</option>');
            	$.get('<?php echo BUSCA_URI; ?>documento/?action=getCategorias', { tipo: $(this).val() }, function(data) {
                if (data && data.length > 0) {
                    $.each(data, function(i, categoria) {
                        if (categoria['TipoDocumento_id'] != <?php echo DOCUMENTOS_OBRA_LITERARIA_ID; ?>) {
                            $('#categoria').append(new Option(categoria['nome'], categoria['id']));
                        }
                    });
                }
            },
            'json');
            });
            
        }
        else {
        	$('#tipo_acervo_container').hide();
        	$('#genero_container').show();
        	$("select#genero option[value='']").attr("selected", "selected");
        	$("select#categoria option[value='']").attr("selected", "selected");
        	$("select#tipo_acervo option[value='']").attr("selected", "selected");
        	$('#genero').attr('disabled', true);
            $('#categoria').attr('disabled', true);
     //       $('#tipo_acervo').addClass('disabled');
      //      $('#genero').addClass('disabled');
      //      $('#categoria').addClass('disabled');
        }
    });
	
	$('#tipo_acervo').change(function() {
		if ($('#tipo_acervo').val()) {
    		$('#categoria').attr('disabled', true);
    		$('#categoria').empty().append('<option selected="selected" value="">'+"<?php echo __('Todas');?>"+'</option>');
    	    $.get('<?php echo BUSCA_URI; ?>documento/?action=getCategorias', { tipo: $('#tipo_acervo').val() }, function(data) {
    		    if (data && data.length > 0) {
    		    	$('#categoria').attr('disabled', false);
    		        $.each(data, function(i, categoria) {
    		            $('#categoria').append(new Option(categoria['nome'], categoria['id']));
    		        });
    		    }
    	    },
    	    'json');
		}
	});

	function split(val) {
        return val.split(/,\s*/);
    }
    
    function extractLast(term) {
        return split(term).pop();
    }
	
	//Autores - auto complete:
    var cache_autor = {};
	$("#autores")
		// don't navigate away from the field on tab when selecting an item
		.bind("keydown", function(event) {
			if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function(request, response) {
				$.getJSON( "<?php echo BUSCA_URI; ?>../admin/autores/?action=search_autor", {
					term: extractLast(request.term)
				}, response);
			},
			search: function() {
				// custom minLength
				var term = extractLast(this.value);
				if (term.length < 2) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function(event, ui) {
				var terms = split(this.value);
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push(ui.item.value);
				// add placeholder to get the comma-and-space at the end
				terms.push("");
				this.value = terms.join(", ");
				return false;
			}
	}).data("autocomplete")._renderItem = function(ul, item) {
        return $("<li></li>")
        .data("item.autocomplete", item)
        .append("<a>" + item.label + "<span class='sugestao'><?php echo __('Sugestão');?></span><br /><em>" + item.desc + "</em></a>")
        .appendTo(ul);
    };

    $('#periodo_tipo').change(function() {
        if ($(this).val() == 'ano') {
            $('#seculo_inicio').hide();
            $('#seculo_fim').hide();
            $('#ano_inicio').show();
            $('#ano_fim').show();
        }
        else {
            $('#ano_inicio').hide();
            $('#ano_fim').hide();
            $('#seculo_inicio').show();
            $('#seculo_fim').show();
        }
    })
    
    $("#titulo").change( function(){
    	$("select#forma_busca[value='']").attr("selected", "selected");
  //  	$('#forma_busca').removeClass('disabled');
    	$('#forma_busca').attr('disabled', false);	
  	})
  	  	
});
   
</script>
