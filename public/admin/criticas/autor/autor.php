<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
		<a href="<?php echo ADMIN_CRITICAS_URI; ?>"><?php echo __('Críticas');?></a> &raquo;
		<?php echo __('Autor');?>
	</h2>
	<br />
    <form action="?action=del" method="post">
        <table id="criticas_autor" class="disable_first">
        	<thead>
        		<tr>
        			<th><input type="checkbox" name="check_all" id="check_all" /></th>
        			<th><?php echo __('Título');?></th>
        			<th><?php echo __('Autor da crítica');?></th>
        			<th><?php echo __('Autor');?></th>
        			<th><?php echo __('Arquivo');?></th>
        		</tr>
        	</thead>
        </table>
	</form>
</div>
<em class="info"><?php echo __('Clique em um registro da tabela para editá-lo.');?></em>

<script type="text/javascript">
$(document).ready(function(){
    $('#criticas_autor').loadTable({
    	sAjaxSource: "?action=getTableData",
    	aoColumns: [
    		{ "sWidth": "30px", "sName": "id", "bSortable": false },
    		{ "sName": "titulo" },
    		{ "sWidth": "250px", "sName": "autor_critica" },
    		{ "sName": "Autor_nome_completo" },
    		{ "sName": "nome_arquivo" }
    	],
    	sPaginationType: "two_button",
    	iDisplayLength: 15,
    	fileEditForm: "editar/",
    	fileAddForm: "cadastrar/"
    });
});
</script>