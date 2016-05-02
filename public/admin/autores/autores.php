<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; <?php echo __('Autores');?></h2>
	<br />
    <form action="?action=del" method="post">
        <table id="autores" class="disable_first">
        	<thead>
        		<tr>
        			<th><input type="checkbox" name="check_all" id="check_all" /></th>
        			<th><?php echo __('Nome');?></th>
        			<th><?php echo __('Nome completo');?></th>
        			<th><?php echo __('Pseudônimo');?></th>
        			<th><?php echo __('Catarinense');?></th>
        		</tr>
        	</thead>
        </table>
	</form>
</div>
<em class="info"><?php echo __('Clique em um registro da tabela para editá-lo.');?></em>

<script type="text/javascript">
$(document).ready(function(){
    $('#autores').loadTable({
    	sAjaxSource: "?action=getTableData",
    	aoColumns: [
    		{ "sWidth": "30px", "sName": "id", "bSortable": false },
    		{ "sWidth": "450px", "sName": "nome_usual" },
    		{ "bVisible": false, "sName": "nome_completo" },
    		{ "sName": "pseudonimo" },
    		{ "sWidth": "100px", "sName": "catarinense" }
    	],
    	aaSorting: [[1,'asc']],
    	sPaginationType: "two_button",
    	iDisplayLength: 15,
    	//allowUpdate: false,
    	fileEditForm: "editar/",
    	fileAddForm: "cadastrar/",
    	/*fnDrawCallback: function() {
    		$("#autores tbody td:not(:first-child)").each(function() {
        		var id = $(this).parent().children().filter(':first-child').children().get(0).value;
				$(this).html('<a href="editar/?id=' + id + '">' + $(this).text() + '&nbsp;</a>');
    		});
    	}*/
    });
});
</script>