<?php
/**
 * Formulário para busca Simples
 */
?>
<form action="<?php echo BUSCA_URI; ?>simples/" method="post" class="inline">
	<fieldset>
		<legend><?php echo __('Pesquisa simples');?></legend>
		<p><label for="termo"><?php echo __('Informações gerais sobre títulos de obras e autores');?></label>
		<input type="text" id="termo" name="termo" style="width: 700px" title="<?php echo __('Exemplo: Machado de Assis');?>" /></p>
		<p><label for="forma_busca"><?php echo __('Forma de busca');?></label>
		<select id="forma_busca" name="forma_busca" style="width: 180px">
			<option value="1"><?php echo __('Qualquer palavra');?></option>
			<option value="2" selected="selected"><?php echo __('Frase exata');?></option>
		</select></p>
	</fieldset>
	<p><input type="submit" value="<?php echo __('Pesquisar');?>" id="pesquisar" /></p>
    <div id="status"></div>
    <div class="clear"></div>
</form>
<script type="text/javascript">
$(function() {
	$('#pesquisar').click(function() {
		if ($('#forma_busca').val() == '2' && $('#termo').val() && $('#termo').val().slice(0,1) != '"' && $('#termo').val().slice(-1) != '"') {
			$('#termo').val('"' + $('#termo').val() + '"');
		}
	});
	
	/*
	$('#forma_busca').change(function() {
		if ($(this).val() == '1') { //Qualquer palavra
			if ($('#termo').val() != $('#termo').attr('title')) {
				$('#termo').val($('#termo').val().slice(1,-1));
			}
			else {
				$('#termo').attr('title', 'Exemplo: Machado de Assis');
				$('#termo').val($('#termo').attr('title'));
			}
		}
		else { //Frase exata
			if ($('#termo').val() != $('#termo').attr('title')) {
				if ($('#termo').val() && $('#termo').val().slice(0,1) != '"' && $('#termo').val().slice(-1) != '"') {
					$('#termo').val('"' + $('#termo').val() + '"');
				}
			}
			else {
				$('#termo').attr('title', 'Exemplo: "Machado de Assis"');
				$('#termo').val($('#termo').attr('title'));
			}
		}
	});

	function check_search_type() {
		if ($("#termo").val()) {
			$('#forma_busca').attr('disabled', false);
		}
		else {
			$('#forma_busca').attr('disabled', true);
		}
		if ($("#termo").val().slice(0,1) == '"' && $("#termo").val().slice(-1) == '"') {
			$('#forma_busca').val('2'); //Frase exata
		}
		else {
			$('#forma_busca').val('1'); //Qualquer palavra
		}
	}
	
	//$('#termo').keyup(check_search_type);

	var inp = $("#termo")[0];
	if ("onpropertychange" in inp) {
	    inp.attachEvent('onpropertychange', $.proxy(function () {
	        if (event.propertyName == "value")
	            check_search_type();
	    }, inp));
	}
	else {
	    inp.addEventListener("input", function () {
	    	check_search_type();
	    }, false);
	}
	*/
});
</script>