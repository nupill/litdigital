<?php 
require_once(APPLICATION_PATH . "/controllers/ControleDocumentos.php");
require_once(APPLICATION_PATH . "/controllers/ControleAcervos.php");

$acervo_id = isset($_REQUEST['acervo']) ? trim($_REQUEST['acervo']) : '';

$controle_documentos = ControleDocumentos::getInstance();
$controle_acervos = ControleAcervos::getInstance();
$acervos = $controle_acervos->get();
?>

<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<a href="<?php echo NAVEGACAO_DOCUMENTO_URI; ?>"><?php echo __('Navegação');?></a> &rarr;
	<?php echo __('Selecione Acervo');?>
</div>

<div id="div_acervo" style="margin-top: 20px">
	<p><?php echo __('Filtrar por');?> <b><?php echo __('acervo');?></b>:</p>
	<form id="form_acervo" action="<?php echo NAVEGACAO_URI; ?>acervo/"" method="post" class="inline">
		<select id="acervo" name="acervo">
			<option value=""><?php echo __('Selecione');?></option>
		    <?php 
		    foreach ($acervos as $acervo) {
		    	if ($acervo_id == $acervo['id']) {
		    	?>
					<option value="<?php echo $acervo['id'] ?>" selected="selected"><?php echo $acervo['descricao']; ?></option>
			    <?php  	
		    	}
		    	else {
		    	?>
					<option value="<?php echo $acervo['id'] ?>"><?php echo $acervo['descricao']; ?></option>
			    <?php	
		    	}
		    }
		    ?>  
		</select>
		<input type="submit" id="submit_acervo" name="submit_acervo" value="<?php echo __('Pesquisar');?>" />
	</form>
</div>

<div id="content">
<?php
    if (!$acervo_id) {
    ?>
    <div id="search_no_results">
        <em><?php echo __('Acervo não especificado');?></em>
    </div>
    <?php
    }
    else {
    	?>

    <div id="search_results_acervo">
    	<table id="results">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __('Título');?></th>
    				<th><?php echo __('Autor(es)');?></th>
    				<th><?php echo __('Tipo');?></th>
    				<th><?php echo __('Gênero');?></th>
    				<th><?php echo __('Ano');?></th>
                </tr>
            </thead>
        </table>
    </div>

    <script type="text/javascript">
    $(function() {
    	
    	var oTable = $('#results').loadTable({
            sAjaxSource: "?action=getTableData&pseudoParam=<?php echo time(); ?>&<?php echo make_request_url($_REQUEST); ?>",
            aoColumns: [
                        		{ "sWidth": "18px", "sName": "midias" },
        		{ "sWidth": "350px", "sName": "titulo" },
        		{ "sName": "autores_nome_usual" },
        		{ "sWidth": "100px", "sName": "nome_tipodocumento" },
        		{ "sName": "nome_genero" },
        		{ "sName": "ano_documento" }
            ],
            allowDelete: false,
            allowCreate: false,
            allowUpdate: false,
            aaSorting: [[1,'asc']],
            sPaginationType: "two_button",
            iDisplayLength: 30,
            sDom: '<"top"ifr>t<"bottom"p><"clear">',
            oLanguage: {
                "sProcessing": "<?php echo __('Carregando');?>...",
                "sLengthMenu": "<?php echo __('Exibir _MENU_ resultados');?>",
                "sZeroRecords": "<?php echo __('Nenhum resultado encontrado');?>",
                "sInfo": "<?php echo __('Exibindo _START_ a _END_ de _TOTAL_ resultados');?>",
                "sInfoEmpty": "<?php echo __('Nenhum resultado');?>",
                "sInfoFiltered": "<?php echo __('(filtrados de _MAX_ resultados)');?>",
                "sInfoPostFix": "",
                "sSearch": "<?php echo __('Procurar nos resultados');?>:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst":    "<?php echo __('Primeira');?>",
                    "sPrevious": "<?php echo __('Anterior');?>",
                    "sNext":     "<?php echo __('Próxima');?>",
                    "sLast":     "<?php echo __('Última');?>"
                }
            },
            fnInitComplete: function() {
//            	$('.top').prepend('<label class="normal" style="display: block"><input id="obras_digitalizadas" type="checkbox" /> Apenas obras digitalizadas</label>');
//                $('#obras_digitalizadas').change(function() {
//                    if ($(this).is(':checked')) {
//                        $.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=1&<?php echo make_request_url($_POST); ?>');
//                    }
//                    else {
//                        $.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=0&<?php echo make_request_url($_POST); ?>');
//                    }
//                    $.fn.getTable('results').fnDraw();
//                });
            }
        });
    });
    </script>
    <?php
    }
    ?>
</div>
