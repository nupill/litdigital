<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; <?php echo __('Críticas');?></h2>
    <a href="<?php echo ADMIN_CRITICAS_AUTOR_URI; ?>" class="critica_tipo"><?php echo __('Autor');?></a>
	<a href="<?php echo ADMIN_CRITICAS_OBRA_URI; ?>" class="critica_tipo"><?php echo __('Obra');?></a>
</div>
