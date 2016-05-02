<?php 
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleCriticasAutor.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleCriticasAutor::getInstance();
$criticas_autor = $controller->get($id);

if ($criticas_autor) {
    $criticas_autor = $criticas_autor[0];
}

//die(print_r($criticas_autor));

?>
<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<?php echo __('Críticas à autor');?> &rarr;
	<?php 
	echo isset($criticas_autor['titulo']) ? $criticas_autor['titulo'] : 'Nenhum resultado encontrado';
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
	elseif (!$criticas_autor) {
	?>
	    <div id="no_results">
	    	<em><?php echo __('Crítica não encontrada'); ?></em>
	    </div> 
	<?php 
	}
	else {
	?>
		<h2><?php echo __('Título'); ?>: <?php echo $criticas_autor['titulo']; ?></h2>
		<em>
        <a href="<?php echo AUTORES_URI; ?>?id=<?php echo $criticas_autor['Autor_id']; ?>"><?php echo __('Autor criticado'); ?>: <?php echo $criticas_autor['Autor_nome_completo']; ?></a>
        <br />
        </em>
        <p><h3><?php echo __('Informações sobre a crítica'); ?></h3></p>
		<ul>
		<?php 
		if ($criticas_autor['autor_critica']) {
    	?>
    		<li><?php echo __('Autor da crítica'); ?>: <?php echo $criticas_autor['autor_critica']; ?></li> 
    	<?php
    	}
    	?>
    	</ul>
    	<?php 
		if ($criticas_autor['nome_arquivo']) {
    	?>
    		<p><h4><a href="<?php echo CRITICAS_AUTOR_URI . '?action=midias&id=' . $id; ?>" class="visualizar_obra">
        	<?php echo __('Crítica disponível para download'); ?></a></h4></p>
    	<?php
    	}
	}
    	?>
</div>
 