<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; <?php echo __('Comentários');?></h2>
    <a href="<?php echo ADMIN_COMENTARIOS_AUTOR_URI; ?>" class="comentario_tipo"><?php echo __('Autor');?></a>
	<a href="<?php echo ADMIN_COMENTARIOS_DOCUMENTO_URI; ?>" class="comentario_tipo"><?php echo __('Documento');?></a>
	<a href="<?php echo ADMIN_COMENTARIOS_DENUNCIAS_URI; ?>" class="comentario_tipo"><?php echo __('Denúncias');?></a>
</div>
