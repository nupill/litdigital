<?php 
require_once(dirname(__FILE__) . '/../../application/controllers/ControleDocumentos.php');
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$id_midia = isset($_REQUEST['id_midia']) ? $_REQUEST['id_midia'] : false;
$controller = ControleDocumentos::getInstance();
$documento = $controller->get($id);

$autores = array();
$midias = array();

if ($documento) {
    $documento = $documento[0];
    $autores = $controller->get_autores($id, array('id', 'nome_completo'));
    if ($id_midia){
    	$midias = $controller->get_midias('', '', $id_midia);
    	$total_midias = $controller->get_midias($id);
    } else {
    	$midias = $controller->get_midias($id);
    }
}
?>
<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
    Documentos &rarr;
    <?php 
    echo isset($documento['titulo']) ? $documento['titulo'] : __('Nenhum resultado encontrado');
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
    elseif (!$documento) {
    ?>
    <div id="no_results">
        <em><?php echo __('Documento não encontrado'); ?></em>
    </div> 
    <?php 
    }
    else {
    ?>
    <h2><a href="?id=<?php echo $id; ?>"><?php echo __('Título'); ?>: <?php echo $documento['titulo']; ?> </a></h2>
	<em>
    <?php 
    for ($i=0; $i<sizeof($autores); $i++) {
    ?>
    	<a href="<?php echo AUTORES_URI; ?>?id=<?php echo $autores[$i]['id']; ?>"><?php echo __('Autor'); ?>: <?php echo $autores[$i]['nome_completo']; ?></a>
    <?php
	    if ($i<sizeof($autores)-1) {
	       echo ', ';   
	    } 
    }
    ?>
    </em>
	<div id="fb-root"></div>
	<div class="fb-like" data-href="<?php echo DOCUMENTOS_URI?>?action=midias&id=<?php echo $id; ?>" data-width="The pixel width of the plugin" data-height="The pixel height of the plugin" data-colorscheme="light" data-layout="button_count" data-action="recommend" data-show-faces="true" data-send="false"></div>
    <ul id="midias">
        <?php 
        $count = 1;
        foreach ($midias as $midia) {
        	if ($midia['titulo']) {
        	    $midia['titulo'] = '(' . $midia['titulo'] . ')';
        	}
        	$ext = explode('.', $midia['nome_arquivo']);
        	$ext = $ext[sizeof($ext)-1];
        	
        	//$midia['tamanho'] = $midia['tamanho'] / 1024 / 1024;
        	
        ?>
            <li>
            <a href="?action=download&id=<?php echo $midia['id'];  ?>" target="_blank" class="<?php echo $ext; ?>"><?php if (!$id_midia) { echo __("Parte ") . $count; } ?> <?php echo $midia['titulo']; ?> <?php echo format_bytes($midia['tamanho']); ?></a>
            <?php 
            if ($midia['descricao']) {
            ?>
            <br /><em><?php echo $midia['descricao']; ?></em>
            <?php
            }
            ?>
            </li>
        <?php
            $count++;
        }
        ?>
    </ul>
	<?php 
	}
	?>
	<?php
	if ($id_midia && sizeof($total_midias)>1){
	?>
		<a href="<?php echo DOCUMENTOS_URI . '?id=' . $id. '&action=midias'?>" alt="Mídias" title="Mídias do documento"><?php echo __('Consulte as outras mídias disponíveis para essa obra'); ?></a>
	<?php }?>
</div>