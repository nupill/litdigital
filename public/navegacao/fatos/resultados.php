<?php 
require_once(APPLICATION_PATH . "/controllers/ControleFatosHistoricos.php");

$fato_id = isset($_REQUEST['fato_id']) ? trim($_REQUEST['fato_id']) : '';
$fato = isset($_REQUEST['fato']) ? trim($_REQUEST['fato']) : '';
$controle_fatos = ControleFatosHistoricos::getInstance();
$fatos = $controle_fatos->get();
?>

<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<a href="<?php echo NAVEGACAO_DOCUMENTO_URI; ?>"><?php echo __('Navegação');?></a> &rarr;
	<?php echo __('Selecione Fato');?>
</div>

<div id="div_fato" style="margin-top: 20px">
	<p><?php echo __('Filtrar por');?> <b><?php echo __('fato');?></b>:</p>
	<form id="form_fatos" action="<?php echo NAVEGACAO_URI; ?>fatos/"
		" method="post" class="inline">
		<p>
			<input type="text" id="fato" name="fato"  style="width: 780px"
	            	<?php  
	            		if ($fato) 
	            			echo " value="."\"".$fato."\""; 
	            	?>  
	         	/>
	            <input type="hidden" id="fato_id" name="fato_id"   
	            	<?php  
	            		if ($fato_id) 
	            			echo " value="."\"".$fato_id."\""; 
	            	?> 
	            />			
		</p>
		<input type="submit" id="btnPesquisar" name="btnPesquisar"
			value="<?php echo __('Pesquisar'); ?>" />
	</form>
</div>

<div id="content">
<?php
    if (!$fato_id) {
    ?>
    <div id="search_no_results">
        <em><?php echo __('Fato não especificado');?></em>
    </div>
    <?php
    }
    else {
    	?>

    <div id="search_results_fato">
    	<table id="results">
            <thead>
                <tr>
                    <th><?php echo __('Nome');?></th>
    				<th><?php echo __('Nascimento');?></th>
                </tr>
            </thead>
        </table>
    </div>

    <script type="text/javascript">
    $(function() {

        $('#results').loadTable({
            sAjaxSource: "?action=getTableData&<?php echo make_request_url($_POST); ?>",
            aoColumns: [
                { "sWidth": "450px", "sName": "nome_usual" },
                { "sName": "loc_nasc" }
            ],
            allowDelete: false,
            allowCreate: false,
            allowUpdate: false,
            aaSorting: [[0,'asc']],
            sPaginationType: "two_button",
            iDisplayLength: 30,
            sDom: '<"top"ifr>t<"bottom"p><"clear">'
        });
        
    });
    </script>
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
            url: "../../admin/fatos_historicos/?action=search_fato",
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
    <?php
    }
    ?>
</div>
