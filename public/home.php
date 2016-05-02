<?php
require_once(dirname(__FILE__) . '/../application/controllers/ControleConfig.php');
require_once (APPLICATION_PATH . '/controllers/ControleDocumentos.php');
require_once (APPLICATION_PATH . '/controllers/ControleUsuarios.php');
require_once (APPLICATION_PATH . '/controllers/ControleConfig.php');
$controle_documentos = ControleDocumentos::getInstance ();
$controle_config = ControleConfig::getInstance();
$controle_usuarios = ControleUsuarios::getInstance ();
$mais_acessados = $controle_documentos->get_midias_mais_acessadas ( 0, 5 );
$ultimos_documentos = $controle_documentos->get_ultimos_documentos ( 0, 5 );


if (Auth::check ()) {
	
	$id_usuario = isset ( $_SESSION ['id'] ) ? $_SESSION ['id'] : '';
	

	if ($controle_usuarios->tem_perfil ( $id_usuario )) {
		$obras_recomendadas = $controle_documentos->get_obras_recomendadas ( $id_usuario, 0, 10 );
	}
}

$count_documentos = $controle_documentos->count_documentos (); // depois trocar por logado
$count_autores = $controle_documentos->count_autores ();
$count_midias = $controle_documentos->count_midias ();


?>

<div id="bar">
	<div  id="bar_content">
		<img class="home-image" src="<?php echo IMAGES_URI; ?>/home_img.png" />
		<div id="bar_right">
		
			<h2><?php echo __('Acervo de obras literárias'); ?></h2>
			<p >
				<?php echo sprintf(__('Atualmente temos <strong> %s </strong> obras, <strong> %s </strong> autores cadastrados e <strong> %s </strong> arquivos digitalizados'),
				 number_format($count_documentos, 0, ',', '.'), number_format($count_autores, 0, ',', '.'), number_format($count_midias, 0, ',', '.') ); ?>
			</p>
		</div>
	</div>
</div>
<?php $this->show_navigation(); ?>
<div id="mais_acessados">
	<h3><?php echo __('Mais acessados') ?></h3>
	<ul>
		<?php
		if ($mais_acessados) {
			foreach ( $mais_acessados as $mais_acessado ) {
				?>
		<li><a
			href="<?php echo DOCUMENTOS_URI . '?action=midias&id=' . $mais_acessado['id'] ; ?>"><?php echo $mais_acessado['titulo']; ?><em><?php echo $mais_acessado['autores']; ?></em></a></li>		
		<?php
			}
		}
		?>
	</ul>
</div>
<?php if (!Auth::check () || (Auth::check () && empty ( $obras_recomendadas ))) { ?>
<div id="ultimas_obras">
	<h3><?php echo __('Últimas obras cadastradas') ?></h3>
	<ul>
	    <?php
	if ($ultimos_documentos) {
		foreach ( $ultimos_documentos as $ultimo_documento ) {
			?>
        <li><a
			href="<?php echo DOCUMENTOS_URI . '?id=' . $ultimo_documento['id'] ; ?>"><?php echo $ultimo_documento['titulo']; ?><em><?php echo $ultimo_documento['autores']; ?></em></a></li>     
        <?php
		}
	}
	?>
	</ul>
</div>
<?php }?>

<?php

if (Auth::check ()) {
	if (! empty ( $obras_recomendadas )) {
		$cont=0;
		$count = count ( $obras_recomendadas );
		?>
<div id="obras_recomendadas">
	<h3><?php echo __('Recomendamos para sua leitura:') ?></h3>
	<ul> 
	    <?php
		if ($obras_recomendadas) {
			foreach ( $obras_recomendadas as $obra_recomendada ) {
				?>
        <li><a
			href="<?php echo DOCUMENTOS_URI . '?id=' . $obra_recomendada['id'] ; ?>"><?php echo $obra_recomendada['titulo']; ?><em><?php echo $obra_recomendada['nome_completo']; ?></em></a></li>     
        <?php
			$cont++;
			if($cont>4){
				break;
			}
			}
		}
		?>
		<li><a href="<?php echo RECOMENDACOES_URI; ?>"><?php echo __('Veja mais'); ?></a></li>
	</ul>
</div>
<?php } }?>


<div id="noticias">
	<?php 
		echo $controle_config->get("div_noticias");
	?>
</div>