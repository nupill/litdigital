<?php 
require_once(dirname(__FILE__) . '/../../application/controllers/ControleDocumentos.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleUsuarios.php');

?>
<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
    <?php echo __('Meu perfil'); ?>
</div>

<?php
if (!Auth::check()){
     exit(__('Precisa estar logado'));
}

require_once(APPLICATION_PATH . "/controllers/ControleUsuarios.php");
$controllerdoc = ControleDocumentos::getInstance();
$controller = ControleUsuarios::getInstance();
$id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
$usuario = $controller->get($id);
$usuario = $usuario[0];
$recomendacoes = $controllerdoc->get_obras_recomendadas($id,0, 100);
?>

<div id="content">
	    <h3><?php echo __('Recomendações'); ?></h3>
	    <table id="recomendacoes">
			<thead>
				<tr>
					<th><?php echo __('Titulo'); ?></th>
	                <th><?php echo __('Autor'); ?></th>
					<th><?php echo __('Tipo'); ?></th>
					<th><?php echo __('Ano'); ?></th>
					<th><?php echo __('Escore'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			if ($recomendacoes) {
			    foreach ($recomendacoes as $recomendacao) {
			?>
				<tr id="<?php echo $recomendacao['id']; ?>">
					<td><?php echo $recomendacao['titulo']; ?></td>
	                <td><?php echo $recomendacao['nome_completo']; ?></td>
					<td><?php echo $recomendacao['nome']; ?></td>
					<td><?php echo $recomendacao['ano_publicacao_inicio']; ?></td>
					<td><?php echo round($recomendacao['escorePerfil'], 2); ?></td>
				</tr>
			<?php
			    }
			}
			?>
			</tbody>
		</table>
</div>

<script type="text/javascript">
function scrollTo(element, callback, offset) {
    var position = element.offset().top;
    if (offset) {
        position+= offset;
    }
    $('html, body').animate({
        scrollTop: position
    }, 1000, function() {
        if (callback) {
            callback();
        }
    });
}

$(function() {


    $('#recomendacoes').loadTable({
		aaSorting: [[ 4, "desc" ]],
    	aoColumns: [
            { "sWidth": "400px", "sName": "titulo" },
            { "sWidth": "200px", "sName": "autor" },
            {"sWidth": "80px",  "sName": "tipo" },
			{"sWidth": "80px",  "sName": "ano" },
			{"sWidth": "80px",  "sName": "escore" },
        ],
        allowCreate: false,
    	allowDelete: false,
    	allowUpdate: false,
    	bServerSide: false,
    	sPaginationType: "two_button",
		fnDrawCallback: function() {
            $("#recomendacoes tbody td").each(function() {
                var id = $(this).parent().attr('id');
                var type = $(this).parent().attr('class');
                type = type.split(' ');
                type = type[0]; //first class
                var uri = '<?php echo DOCUMENTOS_URI . '?id='?>' + id;
			
                $(this).html('<a href="' + uri + '">' + $(this).text() + '&nbsp;</a>');
            });
        }
    	//fileEditForm: "editar/",
    });
});
</script>