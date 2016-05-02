<?php

define('ZEND_PATH', 'C://Users/infoway/git/pronex/application/zf');
set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());

$docUrl = 'http://www.inf.ufsc.br/';
$docContent = 'isso e um teste de indexacao asb asdf fffffffffffffffffff ffffffffffffffffffffff fffffffffffffffffffffff fffffffffff ffsafd adsfsdfasfasdf asdasdf asdfasd asdf asfd  asd asdf asd fasd fasdf asdf asdf asdf sad dfs ';

require_once('../Zend/Search/Lucene.php');

// Create index
$index = Zend_Search_Lucene::open('../zf/teste');
  $hits = $index->terms();

if (!$hits) {
	echo 'sem resposta';
}
else {
	foreach ($hits as $result) {
//		echo 'Resposta: '.$result->document_id.'\n';
        var_dump($result);
	}
}

?>
