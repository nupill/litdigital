<?php

require_once(dirname(__FILE__) . '/../../../application/controllers/ControleDocumentos.php');

$controle_documentos = ControleDocumentos::getInstance();
$tipos = $controle_documentos->get_tipos(null, false);

$alfabeto = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X','Y','Z');
$target = NAVEGACAO_DOCUMENTO_URI;

?>
<em class="howto">
<?php echo __('Como pesquisar: Selecione uma letra para fazer a busca por documentos cuja primeira letra do nome seja igual à selecionada.'); ?>
</em>
<?php 

echo '<div align="center" class="naveg_div">';
for ($i=0; $i<sizeof($alfabeto); $i++) {
	echo "<a href=$target?letra=$alfabeto[$i] style='padding:10px; font-size:14px;'>$alfabeto[$i]</a>";
}
echo '</div>';

?>


