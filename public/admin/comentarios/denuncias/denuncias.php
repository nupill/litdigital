<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
		<a href="<?php echo ADMIN_COMENTARIOS_URI; ?>"><?php echo __('Comentários');?></a> &raquo;
		<?php echo __('Denúncias');?>
	</h2>
	<br />
    <form action="?action=del" method="post">
        <table id="comentarios_denuncia" class="disable_first">
        	<thead>
        		<tr>
        			<th><input type="checkbox" name="check_all" id="check_all" /></th>
        			<th><?php echo __('Título comentário');?></th>
        			<th><?php echo __('Motivo');?></th>
        			<th><?php echo __('Autor da denúncia');?></th>
        		</tr>
        	</thead>
        </table>
	</form>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('#comentarios_denuncia').loadTable({
    	sAjaxSource: "?action=getTableData",
    	aoColumns: [
    		{ "sWidth": "30px", "sName": "id", "bSortable": false },
    		{ "sWidth": "250px", "sName": "titulo" },
    		{ "sName": "motivo" },
    		{ "sName": "usuario" }
    	],
    	sPaginationType: "two_button",
    	iDisplayLength: 20000,
    	allowCreate: false,
    	allowUpdate: false
    });
});
</script>