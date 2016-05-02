<?php 
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleCriticasAutor.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleCriticasAutor::getInstance();
$critica_autor = $controller->get($id);

if ($critica_autor) {
    $critica_autor = $critica_autor[0];
}

?>
<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
    <?php echo __('Críticas à autor'); ?> &rarr;
    <?php 
    echo isset($critica_autor['titulo']) ? $critica_autor['titulo'] : 'Nenhum resultado encontrado';
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
    elseif (!$critica_autor) {
    ?>
    <div id="no_results">
        <em><?php echo __('Crítica não encontrada'); ?></em>
    </div> 
    <?php 
    }
    else {
    	if ($critica_autor['tamanho'])
    		$critica_autor['tamanho'] = round($critica_autor['tamanho'] / 1024 / 1024,2);
    ?>
    <h2><a href="?id=<?php echo $id; ?>"><?php echo __('Título')?>: <?php echo $critica_autor['titulo']; ?></a></h2>
    <em> <a href="<?php echo AUTORES_URI; ?>?id=<?php echo $critica_autor['Autor_id']; ?>"><?php echo __('Autor criticado')?>: <?php echo $critica_autor['Autor_nome_completo']; ?></a></em>
    <ul id="midias">
            <li>
            <a href="?action=download&id=<?php echo $id; ?>"><?php echo $critica_autor['titulo']; ?> <?php echo $critica_autor['tamanho'] . 'MB' ?></a>
            </li>
        <?php
        }
        ?>
    </ul>
</div>