<?php
require_once(APPLICATION_PATH . "/controllers/ControleUsuarios.php");
$controller = ControleUsuarios::getInstance();
?>
<div id="breadcrumbs">
    <a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
    <?php echo __('Confirmação de cadastro');?>
</div>

<div id="content">
<?php
$code = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if ($controller->confirm_account($code)) {	
?>
	<h3 class="success"><?php echo __('Seu cadastro foi concluído com sucesso');?>!</h3>
	<script type="text/javascript">
	$(function() {
		$.post('<?php echo CONTAS_URI; ?>?action=login', { usuario: $('#login_cadastro').val(), senha: $.md5($('#senha_cadastro').val()) });
	    window.setTimeout("window.location = '<?php echo ROOT_URI; ?>';", 3000);       
	});
	</script>
<?php 
}
else {
?>
<h3 class="warning"><?php echo __('Não foi possível confirmar seu cadastro');?>!</h3>
<?php
}
?>
</div>