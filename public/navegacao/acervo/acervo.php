<?php

require_once(APPLICATION_PATH . "/controllers/ControleAcervos.php");

$controle_acervos = ControleAcervos::getInstance();
$acervos = $controle_acervos->get();


?>
<p><?php echo __('Filtrar por'); ?> <b><?php echo __('acervo'); ?></b>:</p>

<div id="div_acervo">
	<form id="form_acervo" action="<?php echo NAVEGACAO_URI; ?>acervo/"" method="post" class="inline">
		<select id="acervo" name="acervo">
			<option value=""><?php echo __('Selecione'); ?></option>
		    <?php 
		    foreach ($acervos as $acervo) {
		    ?>
				<option value="<?php echo $acervo['id'] ?>"><?php echo $acervo['descricao']; ?></option>
		    <?php            
		    }
		    ?>  
		</select>
		<input type="submit" id="btnPesquisar" name="btnPesquisar" value="<?php echo __('Pesquisar'); ?>" />
	</form>
</div>