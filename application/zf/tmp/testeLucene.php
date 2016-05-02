<?php
require_once('../Zend/Search/Lucene.php');
require_once('../removeAcentos.php');


if (@preg_match('/\pL/u', 'a') == 1) {
	echo "PCRE unicode support is turned on.\n";
} else {
	echo "PCRE unicode support is turned off.\n";
}

//define('ZEND_PATH', 'C://Users/infoway/git/pronex/application/zf');
//set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());

$docUrl = 'http://www.inf.ufsc.br/';



setlocale(LC_CTYPE, 'pt_BR.utf-8');
Zend_Search_Lucene_Analysis_Analyzer::setDefault(
 new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());
// Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
// Zend_Search_Lucene_Analysis_Analyzer::setDefault('utf-8');
//Zend_Search_Lucene_Analysis_Analyzer::setDefault(new
// Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());

// Create index
$index = Zend_Search_Lucene::create('./teste/', true);
$fileContent = file_get_contents('obra.html');
 
$char_corretos = array("", "e","e","E","E","u","u","U","U","i","i","I","I","o","o","O","O","a","a","A","A","ç","Ç","a","A","o","O","a","A","e","E","i","I","o","O","u","u");
$char_incorretos = array("&nbsp;","&eacute;","&egrave;","&Eacute;","&Eacute;","&uacute;","&ugrave;","&Uacute;","&Ugrave;","&iacute;","&icirc;","&Iacute;","&Igrave;","&oacute;","&ograve;","&Oacute;","&Ograve;","&aacute;","&agrave;","&Aacute;","&Agrave;","&ccedil;","&Ccedil;","&atilde;","&Atilde;","&otilde;","&Otilde;","&acirc;","&Acirc;","&ecirc;","&Ecirc;","&iexcl;","&Icirc;","&ocirc;","&Ocirc;","&ucirc;","&Ucirc;");

$fileContent = str_replace($char_incorretos, $char_corretos, $fileContent);
$fileContent = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $fileContent);
$fileContent = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $fileContent);
 

 
 if(!mb_check_encoding($fileContent, 'UTF-8')
 		OR !($fileContent === mb_convert_encoding(mb_convert_encoding($fileContent, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
 			$fileContent = mb_convert_encoding($fileContent, 'UTF-8');
 			echo 'converteu utf8';
 		} else echo 'não converteu'; 
		
 		$contents = strip_tags($fileContent);
 		
 		$contents=remove_accents($contents);
 		
 		
 		echo $contents;

// $contents=toASCII($contents);

// $contents = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $contents);
 
 
 
// $contents = utf8_strtr($contents, $from, $to);
 


$doc = new Zend_Search_Lucene_Document();
$doc->addField(Zend_Search_Lucene_Field::Text('url', $docUrl));
$doc->addField(Zend_Search_Lucene_Field::UnStored('content',strtolower($contents),'utf-8'));;
$index->addDocument($doc);
$index->commit();
$index->optimize();


$hits = $index->terms();

if (!$hits) {
	echo 'sem resposta';
}
else {
	foreach ($hits as $result) {
        var_dump($result);
	}
}

function wd_remove_accents($str, $charset='utf-8')
{
	$str = htmlentities($str, ENT_NOQUOTES, $charset);

	$str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caract�res

	return $str;
}

?>