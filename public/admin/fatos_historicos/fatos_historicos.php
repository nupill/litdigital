<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; <?php echo __('Fatos Historicos');?></h2>
	<br />
    <form action="?action=del" method="post">
        <table id="fatos_historicos" class="disable_first">
        	<thead>
        		<tr>
        			<th><input type="checkbox" name="check_all" id="check_all" /></th>
        			<th><?php echo __('Ano de início');?></th>
        			<th><?php echo __('Ano de fim');?></th>
        			<th><?php echo __('Fato');?></th>
        		</tr>
        	</thead>
        </table>
	</form>
</div>
<em class="info"><?php echo __('Clique em um registro da tabela para editá-lo.');?></em>

<script type="text/javascript">
$(document).ready(function(){
    $('#fatos_historicos').loadTable({
    	sAjaxSource: "?action=getTableData",
    	aoColumns: [
    		{ "sWidth": "30px", "sName": "id", "bSortable": false },
    		{ "sWidth": "70px", "sName": "ano_inicio" },
    		{ "sWidth": "70px", "sName": "ano_fim" },
    		{ "sName": "descricao" }
    	],
    	aaSorting: [[1,'asc']],
    	sPaginationType: "two_button",
    	iDisplayLength: 15,
    	fileEditForm: "editar/",
    	fileAddForm: "cadastrar/"
    });
});
</script>