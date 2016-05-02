<?php 
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleCriticasObra.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleCriticasObra::getInstance();
$critica_obra = $controller->get($id);

if ($critica_obra) {
    $critica_obra = $critica_obra[0];
}

?>
<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
    Críticas à obra &rarr;
    <?php 
    echo isset($critica_obra['titulo']) ? $critica_obra['titulo'] : __('Nenhum resultado encontrado');
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
    elseif (!$critica_obra) {
    ?>
    <div id="no_results">
        <em><?php echo __('Crítica não encontrada'); ?></em>
    </div> 
    <?php 
    }
    else {
    	if ($critica_obra['tamanho'])
    		$critica_obra['tamanho'] = round($critica_obra['tamanho'] / 1024 / 1024,2);
    ?>
    <h2><a href="?id=<?php echo $id; ?>"><?php echo __('Título'); ?>: <?php echo $critica_obra['titulo']; ?></a></h2>
    <em> <a href="<?php echo DOCUMENTOS_URI; ?>?id=<?php echo $critica_obra['ObraLiteraria_id']; ?>"><?php echo __('Obra criticada'); ?>: <?php echo $critica_obra['Documento_titulo']; ?></a></em>
    <ul id="midias">
            <li>
            <a href="?action=download&id=<?php echo $id; ?>"><?php echo $critica_obra['titulo']; ?> <?php echo $critica_obra['tamanho'] . 'MB' ?></a>
            </li>
        <?php
        }
        ?>
    </ul>
</div>