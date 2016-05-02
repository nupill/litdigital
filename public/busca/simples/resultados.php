<?php
//Pesquisa resultados no banco de dados
$termo = isset($_REQUEST['termo']) ? trim($_REQUEST['termo']) : '';
if (get_magic_quotes_gpc()) {
    $termo = stripslashes($termo);
}

$personalizacao = false;
if(Auth::check()){
    $personalizacao = Auth::checa_personalizacao();
}


?>
<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<a href="<?php echo BUSCA_URI; ?>"><?php echo __('Busca');?></a> &rarr;
	<?php echo __('Simples');?> (<?php echo $termo; ?>)
</div>
<div id="content">
    <?php
    if (!$termo) {
    ?>
    <div id="search_no_results">
        <em><?php echo __('Termo de busca não especificado');?></em>
        <a href="<?php echo BUSCA_URI; ?>"><?php echo __('Voltar para o formulário de busca');?></a>
    </div>
    <?php
    }
    else {
    ?>
    <div id="search_results_simples">
    	<table id="results">
    		<thead>
    			<tr>
    				<th></th>
    				<th><?php echo __('Título');?></th>
    				<th><?php echo __('Autor(es)');?></th>
    				<th><?php echo __('Tipo');?></th>
    				<th><?php echo __('Gênero');?></th>
    				<th><?php echo __('Ano');?></th>
                    <?php
                    if ($personalizacao) {
                    ?>
                    <th><?php echo __('Escore');?></th>
                    <?php
                    }
                    ?>
    			</tr>
    		</thead>
    	</table>
    </div>
    <script type="text/javascript">
    $(function() {

    	$('#results').loadTable({
        	sAjaxSource: "?action=getTableData&<?php echo make_request_url($_POST); ?>",
            aoColumns: [
        		{ "sWidth": "18px", "sName": "midias" },
        		{ "sWidth": "350px", "sName": "titulo" },
        		{ "sName": "autores_nome_usual" },
        		{ "sWidth": "100px", "sName": "nome_tipodocumento" },
        		{ "sName": "nome_genero" },
        		{ "sName": "ano_documento" }
                <?php
                if ($personalizacao) {
                ?>
                ,{ "sName": "escore" }
                <?php
			    }
			    ?>
        	],
        	allowDelete: false,
        	allowCreate: false,
        	allowUpdate: false,
            <?php
            if ($personalizacao) {
            ?>
        	aaSorting: [[6,'desc']],
        	<?php 
    		}
    		else {
    		?>
    		aaSorting: [[1,'asc']],
    		<?php
    		}
    		?>
        	sPaginationType: "two_button",
        	iDisplayLength: 30,
        	sDom: '<"top"ifr>t<"bottom"p><"clear">',
			fnInitComplete: function() {
				$('.top').prepend('<label class="normal" style="display: block"><input id="obras_digitalizadas" type="checkbox" />'+"<?php echo __('Apenas obras digitalizadas');?>"+'</label>');
		    	$('#obras_digitalizadas').change(function() {
		        	if ($(this).is(':checked')) {
		        		$.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=1&<?php echo make_request_url($_POST); ?>');
		        	}
		        	else {
		        		$.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=0&<?php echo make_request_url($_POST); ?>');
		        	}
		        	$.fn.getTable('results').fnDraw(false);
		        });
			}
        });
    });
    </script>
    <?php
    }
    ?>
</div>