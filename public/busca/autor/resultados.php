<?php
?>
<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<a href="<?php echo BUSCA_URI; ?>"><?php echo __('Busca');?></a> &rarr;
	Autor
</div>
<div id="content">
    <div id="search_results">
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
</div>