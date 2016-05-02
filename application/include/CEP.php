<?php
/*  
*  Função de busca de Endereço pelo CEP  
*  -   Desenvolvido Felipe Olivaes para ajaxbox.com.br  
*  -   Utilizando WebService de CEP da republicavirtual.com.br  
*/

//header("Content-Type: text/html; charset=ISO-8859-1", true);

$cep = isset($_GET['cep']) ? $_GET['cep'] : '';

function buscar_cep($cep){  
	@ini_set('allow_url_fopen', 1);
	$resultado = @file_get_contents('http://republicavirtual.com.br/web_cep.php?cep='.$cep.'&formato=json');
	@ini_set('allow_url_fopen', 0);
	if (!$resultado) {
		$resultado['resultado'] = 0;
		$resultado['resultado_txt'] = 'Erro ao buscar CEP';
		return json_encode($resultado);
    }
	return $resultado;  
}

exit(buscar_cep($cep));  
?>