<?php 
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleCriticasObra.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleCriticasObra::getInstance();
$criticas_obra = $controller->get($id);

if ($criticas_obra) {
    $criticas_obra = $criticas_obra[0];
}

?>
<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
	<?php echo __('Críticas à obra'); ?> &rarr;
	<?php 
	echo isset($criticas_obra['titulo']) ? $criticas_obra['titulo'] : __('Nenhum resultado encontrado');
	?>
</div>
<div id="content">
	<?php 
	if (!$id) {
	?>
    <div id="no_results">
    	<em><?php echo __('ID não especificado'); ?></em>
    </div>
    <?php 
	}
	elseif (!$criticas_obra) {
	?>
	    <div id="no_results">
	    	<em><?php echo __('Crítica não encontrada'); ?></em>
	    </div> 
	<?php 
	}
	else {
	?>
		<h2><?php echo __('Título'); ?>: <?php echo $criticas_obra['titulo']; ?></h2>
		<em>
        <a href="<?php echo DOCUMENTOS_URI; ?>?id=<?php echo $criticas_obra['ObraLiteraria_id']; ?>"><?php echo __('Obra criticada'); ?>: <?php echo $criticas_obra['Documento_titulo']; ?></a>
        <br />
        </em>
        <p><h3><?php echo __('Informações sobre a crítica'); ?></h3></p>
		<ul>
		<?php 
		if ($criticas_obra['autor_critica']) {
    	?>
    		<li><?php echo __('Autor da crítica'); ?>: <?php echo $criticas_obra['autor_critica']; ?></li> 
    	<?php
    	}
    	?>
    	</ul>
    	<?php 
		if ($criticas_obra['nome_arquivo']) {
    	?>
    		<p><h4><a href="<?php echo CRITICAS_OBRA_URI . '?action=midias&id=' . $id; ?>" class="visualizar_obra">
        	<?php echo __('Crítica disponível para download'); ?></a></h4></p>
    	<?php
    	}
	}
    	?>
</div>
 