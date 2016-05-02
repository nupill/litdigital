<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; <?php echo __('Editoras');?></h2>
	<br />
    <form action="?action=del" method="post">
        <table id="editoras" class="disable_first">
        	<thead>
        		<tr>
        			<th><input type="checkbox" name="check_all" id="check_all" /></th>
        			<th><?php echo __('Nome da Editora');?></th>
        			<th><?php echo __('Local da Editora');?></th>
        		</tr>
        	</thead>
        </table>
	</form>
</div>
<em class="info"><?php echo __('Clique em um registro da tabela para editá-lo.');?></em>

<script type="text/javascript">
$(document).ready(function(){
    $('#editoras').loadTable({
    	sAjaxSource: "?action=getTableData",
    	aoColumns: [
    		{ "sWidth": "30px", "sName": "id", "bSortable": false },
    		{ "sName": "nome" },
    		{ "sWidth": "300px", "sName": "local" }
    	],
    	aaSorting: [[1,'asc']],
    	sPaginationType: "two_button",
    	iDisplayLength: 15,
    	fileEditForm: "editar/",
    	fileAddForm: "cadastrar/"
    });
});
</script>