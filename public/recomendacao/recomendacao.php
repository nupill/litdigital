<?php

?>
<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a>
</div>
<div id="content">

	<div>
		<table id="results">
			<thead>
				<tr>
    				<th><?php echo __('Título'); ?></th>
    				<th><?php echo __('Autor'); ?></th>
    				<th><?php echo __('Tipo'); ?></th>
    				<th><?php echo __('Escore'); ?></th>
				</tr>
			</thead>
		</table>
	</div>
	<script type="text/javascript">
    $(function() {

    	$('#results').loadTable({
        	sAjaxSource: "?action=getRecomendacoes",
            aoColumns: [
        		{ "sWidth": "350px", "sName": "titulo", "sClass": "center" },
        		{ "sName": "nome_completo" },
        		{ "sWidth": "130px", "sName": "nome" },
        		{ "sName": "escorePerfil" }
        	],
        	allowDelete: false,
        	allowCreate: false,
        	allowUpdate: false,
        	aaSorting: [[4,'desc']],
        	sPaginationType: "two_button",
        	iDisplayLength: 30,
        
        });
    });
    </script>

</div>