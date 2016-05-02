<?php


require('../../controllers/ControleBusca.php');

$controllerdoc = ControleBusca::getInstance();

$palavras="Flor anônima";
$tipo=1;

$hits = $controllerdoc->busca_conteudo($palavras, $tipo);


        $numArray = count($hits);
	if($numArray!=0){
		foreach ($hits as $hit) {

                    echo $hit->id_midia;
                    echo "\n";
                    echo $hit->document_id;
                    echo "\n";
                    echo $hit->url;
                    echo "\n";
                    echo $hit->title;
                    echo "\n";
                    echo $hit->score;
                    echo "\n";
                    echo "\n";
		}

        }

/*
require 'Zend/Search/Lucene.php';


Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('UTF-8');

//$stopWords = array('a', 'o', 'da','do', 'em', 'ou', 'e');
//$stopWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_StopWords($stopWords);
//$shortWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_ShortWords();

$analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive;

Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);

$textBusca="da";

$textBusca = ltrim($textBusca);

$busca=$textBusca;
$textBusca = strtolower($textBusca);
$textBusca = utf8_encode($textBusca);
$words1 = explode(" ",$textBusca);

$words=$words1;
$i=count($words);

$query1 = new Zend_Search_Lucene_Search_Query_Phrase($words1);

/*
	for($w=0;$w<$i;$w++){
		if($words[$w]!=''){
			$query1->addTerm(new Zend_Search_Lucene_Index_Term($words[$w]));
			//echo "=".$words[$w]."=<br>";
		}
	}


        echo "Busca por ".$busca;

        echo "\n";

 	$index = Zend_Search_Lucene::open('indexes');
	$hits1 = $index->find($query1);
	$numArray = count($hits1);
	if($numArray!=0){
		foreach ($hits1 as $hit) {
                    
                    echo $hit->id_midia;
                    echo "\n";
                    echo $hit->document_id;
                    echo "\n";
                    echo $hit->url;
                    echo "\n";
                    echo $hit->title;
                    echo "\n";
                    echo $hit->score;
                    echo "\n";
                    echo "\n";
		}
	}else{
            echo "aaa";
        }
	unset($index);

        */


?>
