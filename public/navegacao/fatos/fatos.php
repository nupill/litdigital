<?php
require_once (APPLICATION_PATH . "/controllers/ControleFatosHistoricos.php");

$controle_fatos = ControleFatosHistoricos::getInstance ();
$fatos = $controle_fatos->get ();
?>
<p><?php echo __('Filtrar por'); ?> <b><?php echo __('fato'); ?></b>:
</p>

<div id="div_fatos">
	<form id="form_fatos" action="<?php echo NAVEGACAO_URI; ?>fatos/"
		" method="post" class="inline">
		<p>
			<input type="text" id="fato" name="fato"
				title="<?php echo __("Digite o fato, selecione um dos resultados encontrados");?>"
				style="width: 780px" /> 
			<input type="hidden" id="fato_id" name="fato_id" />
		</p>
		<input type="submit" id="btnPesquisar" name="btnPesquisar"
			value="<?php echo __('Pesquisar'); ?>" />
	</form>
</div>

<script type="text/javascript"
	src="<?php echo JS_URI ?>jquery-ui.custom.min.js"></script>
<link rel="stylesheet" type="text/css"
	href="<?php echo CSS_URI ?>smoothness/jquery-ui.custom.css" />

<script type="text/javascript">
var cache = {};
$('#fato').autocomplete({
    minLength: 2,
    source: function(request, response) {
        if (request.term in cache) {
        	if (typeof(cache[request.term][0]) != 'undefined' &&
            	request.term == cache[request.term][0].value) {
    	    }
            response(cache[request.term]);
            return;
        }
        
        $.ajax({
            url: "../admin/fatos_historicos/?action=search_fato",
            dataType: "json",
            data: request,
            success: function(data) {
                cache[request.term] = data;
                if (typeof(cache[request.term][0]) != 'undefined' &&
                	request.term == cache[request.term][0].value) {
                }
                response(data);
            }
        });
    },
    select: function(event, ui) {
        $('#fato').val(ui.item.label);
        $('#fato_id').val(ui.item.id);
        $('#fato').focus('');
        return false;
    },
    change: function( event, ui ) {
   	  if ( !ui.item ) {
   		  $('#fato').val('');
             $('#fato_id').val('');
             return false;
   	  }
   	}
});
</script>

