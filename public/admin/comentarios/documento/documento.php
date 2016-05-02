<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
		<a href="<?php echo ADMIN_COMENTARIOS_URI; ?>"><?php echo __('Comentários');?></a> &raquo;
		<?php echo __('Documento');?>
	</h2>
	<br />
    <form action="?action=del" method="post">
        <table id="comentarios_documento" class="disable_first">
        	<thead>
        		<tr>
        			<th><input type="checkbox" name="check_all" id="check_all" /></th>
        			<th><?php echo __('Data');?></th>
        			<th><?php echo __('Documento');?></th>
        			<th><?php echo __('Título do comentário');?></th>
        			<th><?php echo __('Autor do comentário');?></th>
        			<th><?php echo __('Votos');?></th>
        			<th><?php echo __('Denúncias');?></th>
        		</tr>
        	</thead>
        </table>
	</form>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('#comentarios_documento').loadTable({
    	sAjaxSource: "?action=getTableData",
    	aoColumns: [
    		{ "sWidth": "30px", "sName": "id", "bSortable": false },
    		{ "sWidth": "130px", "sName": "data_inclusao" },
    		{ "sName": "documento" },
    		{ "sName": "titulo" },
    		{ "sName": "usuario" },
    		{ "sName": "score", "bSortable": false },
    		{ "sName": "denuncia", "bSortable": false }
    	],
    	sPaginationType: "two_button",
    	iDisplayLength: 20000,
    	aaSorting: [[1,'desc']],
    	allowCreate: false,
    	allowUpdate: false,
    	fnDrawCallback: function() {
			$("#comentarios_documento tbody td:not(:first-child)").each(function() {
	    		var idDocumento = $(this).parent().children().filter(':first-child').children().get(0).id;
	    		var idComentario = $(this).parent().children().filter(':first-child').children().get(0).value;
				$(this).html('<a href="../../../documentos/?id=' + idDocumento + '#' + idComentario + '">' + $(this).text() + '&nbsp;</a>');
			});
		}
    });
});
</script>