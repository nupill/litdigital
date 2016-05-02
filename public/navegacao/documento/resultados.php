<?php

/*require_once(dirname(__FILE__) . '/../../../application/controllers/ControleDocumentos.php');

$controle_documentos = ControleDocumentos::getInstance();
$tipos = $controle_documentos->get_tipos(null, false);*/

?>

<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<a href="<?php echo NAVEGACAO_DOCUMENTO_URI; ?>"><?php echo __('Navegação');?></a> &rarr;
	<?php echo __('Documento');?>
</div>

<?php

$alfabeto = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X','Y','Z');
$target = NAVEGACAO_DOCUMENTO_URI;

echo '<div align="center" class="naveg_div" >';
for ($i=0; $i<sizeof($alfabeto); $i++) {
	if (isset($_GET['letra']) && $_GET['letra'] == $alfabeto[$i]) {
		echo "<a href=$target?letra={$alfabeto[$i]} class='active'>{$alfabeto[$i]}</a>";
	}
	else {
		echo "<a href=$target?letra={$alfabeto[$i]}>{$alfabeto[$i]}</a>";
	}
}
echo '</div>';
?>

<div id="content">
    <div id="search_results">
    	<table id="results">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __('Título da obra');?></th>
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
            sAjaxSource: "?action=getTableData&pseudoParam=<?php echo time(); ?>&<?php echo make_request_url($_GET); ?>",
            aoColumns: [
                { "sWidth": "18px", "sName": "midias" },
                { "sWidth": "350px", "sName": "titulo" },
                { "sName": "autores_nome_completo" },
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
            	$('.top').prepend('<label class="normal" style="display: block"><input id="obras_digitalizadas" type="checkbox" />'+" <?php echo __('Apenas obras digitalizadas');?>"+'</label>');
                $('#obras_digitalizadas').change(function() {
                    if ($(this).is(':checked')) {
                        $.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=1&<?php echo make_request_url($_POST); ?>');
                    }
                    else {
                        $.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=0&<?php echo make_request_url($_POST); ?>');
                    }
                    $.fn.getTable('results').fnDraw();
                });
            }
        });  
    });
    </script>
</div>
