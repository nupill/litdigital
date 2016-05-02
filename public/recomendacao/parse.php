<?php

function array_orderby()
{
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
			$args[$n] = $tmp;
		}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}

function removeNumericIndexes($recomendacoes) {
	
	$recomendacoesDepoisDoParse = array ();
	
	foreach ( $recomendacoes as $recomendacao ) {
		foreach ( $recomendacao as $chave => $valor ) {
			if (intval ( $chave ) || $chave == '0') {
				unset ( $recomendacao [$chave] );
			}
		}
		array_push ( $recomendacoesDepoisDoParse, $recomendacao );
	}
	
	return $recomendacoesDepoisDoParse;
}

$recomendacoes = removeNumericIndexes ( $recomendacoes );

for($i = 0; $i < sizeof($recomendacoes); $i++) {
	$recomendacoes [$i] ['escorePerfil'] = round($recomendacoes [$i] ['escorePerfil'], 2);  
}

$recomendacoesOrdenadasPorEscore = array_orderby($recomendacoes, 'escorePerfil', SORT_DESC);

$aColumns = array ('titulo', 'nome_completo', 'nome', 'escorePerfil' );

$output = array ("sEcho" => intval ( $_GET ['sEcho'] ), "iTotalRecords" => count ( $recomendacoesOrdenadasPorEscore ), "iTotalDisplayRecords" => 10, "aaData" => array () );

$recomendacoesFinal = array ();


foreach ( $recomendacoesOrdenadasPorEscore as $recomendacao ) {
	foreach ( $recomendacao as $chave => $valor ) {
		
		if ($chave == 'titulo') {
			$recomendacao [$chave] = '<a href="' . DOCUMENTOS_URI . '?id=' . $recomendacao ['id'] . '" alt="Detalhes sobre a obra" title="Detalhes sobre a obra">' . $recomendacao ['titulo'] . '</a>';
		}
	}
	
	array_push ( $recomendacoesFinal, $recomendacao );
	
	$row = array ();
	for($i = 0; $i < count ( $aColumns ); $i ++) {
		if ($recomendacao)
			$row [] = $recomendacao [$aColumns [$i]];
	
	}
	$output ['aaData'] [] = $row;
}


echo json_encode ( $output );
?>