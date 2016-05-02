<?php 
require_once(dirname(__FILE__) . '/../../application/controllers/ControleEditoras.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleDocumentos.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleEditoras::getInstance();
$controller_documentos = ControleDocumentos::getInstance();

$editora = $controller->get($id);
$periodico = $controller->periodico($id);
$documentos = $controller->get_documentos($id);

if ($editora) {
    $editora = $editora[0];
}
?>

<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<?php echo __('Editoras');?> &rarr;
	<?php 	
		echo isset($editora['nome']) ? $editora['nome'] : 'Nenhum resultado encontrado';
	?>
</div>

<div id="content">
	<?php 
	if (!$id) {
	?>
    <div id="no_results">
		<em><?php echo __('ID não especificado');?></em>
	</div>
    <?php 
	}
	elseif (!$editora) {
	?>
    <div id="no_results">
		<em><?php echo __('Editora não encontrada');?></em>
	</div> 
	<?php 
	}
	else {
	?>
	<h2><?php echo $editora['nome']; ?></h2>
	<br /> <br />
	<h3><?php echo __('Informações sobre a editora');?></h3>
	<ul>
		<?php
		if ($periodico) 
			echo "<li>".__('Trata-se de um periódico') . "</li>";
		
		
		if ($editora['local']) {
			echo "<li>".__('Local').": " . $editora['local'] . "</li>";
		}
		
		if ($editora['descricao']) {
			echo "<li>".__('Descrição').": " . $editora['descricao'] . "</li>";
		}
    	?>
	</ul>	
	<h3><?php echo __('Obras da Editora/Periódico');?></h3>
	<br /> <label class="normal" style="display: block"> <input
		id="obras_digitalizadas" type="checkbox" /> 
		<?php echo __('Apenas obras digitalizadas');?> 
    </label>
	<table id="obras">
		<thead>
			<tr>
				<th></th>
				<th><?php echo __('Título');?></th>
				<th><?php echo __('Tipo');?></th>
				<th><?php echo __('Gênero');?></th>
				<th><?php echo __('Ano');?></th>
			</tr>
		</thead>
		<tbody>
        <?php
        if ($documentos) {
            foreach ($documentos as $documento) {
            	if ($documento['midias'] == 0) {
                	$documento['midias'] = '<img src="' .IMAGES_URI . 'ico_download2_disabled.png" alt="Não disponível para visualização" title="Não disponível para visualização" />';
                	
                }
                else {
                	$documento['midias'] = '<a href="'.DOCUMENTOS_URI.'?action=midias&id='.$documento['id'].'" alt="'.__('Visualizar obra').'" title="'.__('Visualizar obra').'">
                       		  <img src="' .IMAGES_URI . 'ico_download2.png" />
                       		  </a>';
                }
        ?>
            <tr id="<?php echo $documento['id']; ?>"
				class="<?php echo $documento['tipo']; ?>">
				<td><?php echo $documento['midias'] ?></td>
				<td><?php echo $documento['titulo']; ?></td>
				<td><?php echo $documento['tipo']; ?></td>
				<td><?php echo $documento['genero']; ?></td>
                
                <?php 
                $ano = '-';
                if ($documento['ano_documento']) {
	                $ano = $documento['ano_documento'];
	            }
	            elseif ($documento['seculo_documento']) {
	                $ano = $documento['seculo_documento'];
	            }
                ?>
                
                <td><?php echo $ano; ?></td>
			</tr>
        <?php 
            }
        }
        ?>
        </tbody>
	</table>
	
	
	<script type="text/javascript">
$(document).ready(function(){
	$('#obras').loadTable({
	    aoColumns: [
	    	{ "sWidth": "18px", "sName": "midias" },
	        { "sWidth": "550px", "sName": "titulo" },
	        { "sName": "tipo" },
	        { "sName": "genero" },
	        { "sName": "ano" }
	    ],
	    aaSorting: [[1,'asc']],
	    allowCreate: false,
	    allowDelete: false,
	    allowUpdate: false,
	    bServerSide: false,
	    sPaginationType: "two_button",
	    fnDrawCallback: function() {
	        $("#obras tbody td").each(function() {
	            var id = $(this).parent().attr('id');
	            var type = $(this).parent().attr('class');
	            type = type.split(' ');
	            type = type[0]; //first class
	            var uri = '<?php echo DOCUMENTOS_URI; ?>?id=' + id;
	            $(this).html('<a href="' + uri + '">' + $(this).html() + '&nbsp;</a>');
	        });
	    }
	});

	$.fn.dataTableExt.afnFiltering.push(
	    function(oSettings, aData, iDataIndex) {
		    if (oSettings.sTableId == 'obras') {
			    if ($(aData[0]).filter('a').length > 0 || !$('#obras_digitalizadas').attr('checked')) {
				    return true;
			    }
			    else {
				    return false
			    }
			}
	    	return true;
    	}
    );

	$('#obras_digitalizadas').click(function() {
		$("#obras").dataTable().fnDraw();
	});

});
</script>
	<?php 
	}
	?>