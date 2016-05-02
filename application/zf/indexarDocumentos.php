<?php

require('../controllers/ControleDocumentos.php');
require_once('Zend/Search/Lucene.php');

function deleteDir($dirPath) {
	if (! is_dir($dirPath)) {
		throw new InvalidArgumentException("$dirPath must be a directory");
	}
	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		$dirPath .= '/';
	}
	$files = glob($dirPath . '*', GLOB_MARK);
	foreach ($files as $file) {
		if (is_dir($file)) {
			self::deleteDir($file);
		} else {
			unlink($file);
		}
	}
//	rmdir($dirPath);
}

if (@preg_match('/\pL/u', 'a') == 1) {
	echo "PCRE unicode support is turned on. OK!\n";
} else {
	echo "ERROR: PCRE unicode support is turned off.\n";
	return;
}
deleteDir('./indexes');

// Create index
setlocale(LC_CTYPE, 'pt_BR.utf-8');
Zend_Search_Lucene_Analysis_Analyzer::setDefault(new
		Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());

$index = Zend_Search_Lucene::create('./indexes', true);

// $indexPath = 'indexes';
// $diretorio = "../../public/_documents/";
$controllerdoc = ControleDocumentos::getInstance();
$controllerdoc->index_alldocuments();
?>
