<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
require_once(APPLICATION_PATH . '/controllers/ControleDocumentos.php');
$controle_documentos = ControleDocumentos::getInstance();
$tipos = $controle_documentos->get_tipos();
?>
<ul id="navigation_menu">
	<li<?php if ($this->get_active_nav_item() == ADMIN_ESTATISTICAS_ID) { echo ' class="active"'; } ?>>
		<a href="<?php echo ADMIN_ESTATISTICAS_URI; ?>"><?php echo __('Estatísticas') ?></a>
	</li>
	<li<?php if ($this->get_active_nav_item() == ADMIN_AUTORES_ID) { echo ' class="active"'; } ?>>
		<a href="<?php echo ADMIN_AUTORES_URI; ?>"><?php echo __('Autores') ?></a>
	</li>
	<li<?php if ($this->get_active_nav_item() == ADMIN_DOCUMENTOS_ID) { echo ' class="active"'; } ?>>
		<a href="#documentos"><?php echo __('Documentos') ?></a>
		<ul>
		    <?php 
		    foreach ($tipos as $id_tipo=>$tipo) {
		    	echo '<li><a href="'.$controle_documentos->get_uri_tipos($id_tipo).'">'.__($tipo).'</a></li>';
		    }
		    ?>
        </ul>
	</li>
	
	<li<?php if ($this->get_active_nav_item() == ADMIN_DOCUMENTOS_ID) { echo ' class="active"'; } ?>>
		<a href="#outros"><?php echo __('Outros') ?></a>
		<ul>
			<li><a href="<?php echo ADMIN_EDITORAS_URI; ?>"><?php echo __('Editoras') ?></a></li>
			<li><a href="<?php echo ADMIN_FONTES_URI; ?>"><?php echo __('Fontes') ?></a></li>
			<li><a href="<?php echo ADMIN_ACERVOS_URI; ?>"><?php echo __('Acervos') ?></a></li>
			<a href="<?php echo ADMIN_FATOS_HISTORICOS_URI; ?>"><?php echo __('Fatos Históricos') ?></a>
			
		</ul>
	</li>
	
	<li<?php if ($this->get_active_nav_item() == ADMIN_CRITICAS_ID) { echo ' class="active"'; } ?>>
		<a href="#criticas"><?php echo __('Críticas') ?></a>
		<ul>
			<li><a href="<?php echo ADMIN_CRITICAS_AUTOR_URI; ?>"><?php echo __('Autor') ?></a></li>
			<li><a href="<?php echo ADMIN_CRITICAS_OBRA_URI; ?>"><?php echo __('Obra') ?></a></li>
		</ul>
	</li>
	

	<?php 
	if ($_SESSION['papel'] == PAPEL_ADMINISTRADOR_ID) {
	?>
	<li<?php if ($this->get_active_nav_item() == ADMIN_COMENTARIOS_ID) { echo ' class="active"'; } ?>>
		<a href="#comentarios"><?php echo __('Comentários') ?></a>
		<ul>
			<li><a href="<?php echo ADMIN_COMENTARIOS_AUTOR_URI; ?>"><?php echo __('Autor') ?></a></li>
			<li><a href="<?php echo ADMIN_COMENTARIOS_DOCUMENTO_URI; ?>"><?php echo __('Documento') ?></a></li>
			<li><a href="<?php echo ADMIN_COMENTARIOS_DENUNCIAS_URI; ?>"><?php echo __('Denúncias') ?></a></li>
		</ul>
	</li>
	
	<li<?php if ($this->get_active_nav_item() == ADMIN_USUARIOS_ID) { echo ' class="active"'; } ?>>
		<a href="#outros"><?php echo __('Administração') ?></a>
		<ul>
			<li<?php if ($this->get_active_nav_item() == ADMIN_USUARIOS_ID) { echo ' class="active"'; } ?>>
				<a href="<?php echo ADMIN_USUARIOS_URI; ?>"><?php echo __('Usuários') ?></a>
				
				<li><a href="<?php echo ADMIN_IDIOMAS_URI; ?>"><?php echo __('Idiomas') ?></a></li>
				<li><a href="<?php echo ADMIN_GENEROS_URI; ?>"><?php echo __('Gêneros') ?></a></li>
				<li><a href="<?php echo ADMIN_CIDADES_URI; ?>"><?php echo __('Cidades') ?></a></li>
				<li><a href="<?php echo ADMIN_ESTADOS_URI; ?>"><?php echo __('Estados') ?></a></li>
				<li><a href="<?php echo ADMIN_PAISES_URI; ?>"><?php echo __('Paises') ?></a></li>
				<li><a href="<?php echo ADMIN_MANUTENCAO_URI."?id=1"; ?>"><?php echo __('Manutenção Consulta') ?></a></li>							
				<li><a href="<?php echo ADMIN_MANUTENCAO_URI."?id=2"; ?>"><?php echo __('Reindexação') ?></a></li>
				<li><a href="<?php echo ADMIN_CUSTOM_URI?>"><?php echo __('Customização')?></a></li>							
					
			</li>
		</ul>
	</li>
	<?php 
	}
	?>
</ul>
<div id="sub_navigation_menu">
	<span>Olá <?php echo $_SESSION['primeiro_nome']; ?>.</span>
	<a href="<?php echo CONTAS_URI; ?>?action=logout" class="logout"><?php echo __('Logout') ?></a>
	<a href="#" class="settings"><?php echo __('Configurações') ?></a>
	<a href=<?php echo TUTORIAL_URI; ?> target="_blank"><?php echo __('Tutorial') ?></a></li>
	<a href="<?php echo HOME_URI; ?>" class="home"><?php echo __('Página inicial') ?></a>
</div>
