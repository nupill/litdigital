<?php 
/* Verifica se o usuário está logado e se possui acesso a este módulo */
require_once(dirname(__FILE__) . '/../include/Auth.php');
if (!Auth::check()) {
?>
<br />
<div class="center">
	<h1><?php echo __('Você precisa estar logado para acessar esta página') ?></h1>
	<em><?php echo __('Sua sessão pode ter expirado. Faça o login novamente.') ?></em>
	<br /><br />
	<a href="<?php echo HOME_URI?>"><?php echo __('Ir para a página inicial') ?></a>
</div>
<?php 
}
else {
?>
<br />
<div class="center">
    <h1><?php echo __('Você não possui autorização para acessar esta página') ?></h1>
    <br /><br />
	<a href="<?php echo HOME_URI?>"><?php echo __('Ir para a página inicial') ?></a>
</div>
<?php 
}
?>