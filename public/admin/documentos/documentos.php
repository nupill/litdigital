<?php 
if (!Auth::check()) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
	<h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; Documentos</h2>
	<ul>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_AUDIOVISUAIS_URI; ?>"><?php echo __('Audiovisuais');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_BIBLIOTECA_URI; ?>"><?php echo __('Biblioteca');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_ADAPTACOES_URI; ?>"><?php echo __('Comprovantes de Adaptações');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_CRITICA_URI; ?>"><?php echo __('Comprovantes de Crítica');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_COMPROVANTES_EDICOES_URI; ?>"><?php echo __('Comprovantes de Edições');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_CORRESPONDENCIAS_URI; ?>"><?php echo __('Correspondência');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_ESBOCOS_NOTAS_URI; ?>"><?php echo __('Esboços e Notas');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_HISTORIA_EDITORIAL_URI; ?>"><?php echo __('História Editorial');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_ILUSTRACOES_URI; ?>"><?php echo __('Ilustrações');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_MEMORABILIA_URI; ?>"><?php echo __('Memorabilia');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_ORIGINAIS_URI; ?>"><?php echo __('Originais');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_OBJETOS_ARTE_URI; ?>"><?php echo __('Objetos de Arte');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_OBRA_URI; ?>"><?php echo __('Obra');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>"><?php echo __('Obra Literária');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_PUBLICACOES_IMPRENSA_URI; ?>"><?php echo __('Publicações na Imprensa');?></a></li>
		<li><a href="<?php echo ADMIN_DOCUMENTOS_VIDA_URI; ?>"><?php echo __('Vida');?></a></li>
    </ul>
</div>
