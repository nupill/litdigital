<?php

require_once(APPLICATION_PATH . "/controllers/ControleDocumentos.php");

$controle_documentos = ControleDocumentos::getInstance();
$acervo = $controle_documentos->get_distinct_acervo();


?>
<p>Filtrar por <b>acervo</b>:</p>

<div id="div_acervo">
	<form id="form_acervo" action="<?php echo NAVEGACAO_URI; ?>acervo/"" method="post" class="inline">
		<select id="acervo" name="acervo">
			<option value="todos">Todos</option>
		    <?php 
		    foreach ($acervo as $array => $nome) {
		    ?>
				<option value="<?php echo $nome['acervo'] ?>"><?php echo $nome['acervo']; ?></option>
		    <?php            
		    }
		    ?>  
		</select>
		<input type="submit" id="submit_acervo" name="submit_acervo" value="Pesquisar" />
	</form>
</div>
