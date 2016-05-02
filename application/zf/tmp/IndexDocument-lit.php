<?php
require_once('Zend/Search/Lucene.php');
require_once('Zend/Search/Lucene/Analysis/TokenFilter/StopWords.php');
require_once('Zend/Search/Lucene/Analysis/TokenFilter/ShortWords.php');

class IndexDocument extends Zend_Search_Lucene_Document {
  /**
   * Constructor. Creates our indexable document and adds all
   * necessary fields to it using the passed in document
   */
  public function __construct($document) {
    /*
     * Configura as palavras que a busca nao permite (preposicoes por exemplo, que dao resultados demais)
     */
    
    //$analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive;
    $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num;
    Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
    
    
    /*
     * Busca o nome do arquivo no disco
     */
    
    $title = $document['titulo_midia'];
    //$title = utf8_encode($title);
    
    if ($title=='') {
      $title = 'Sem Titulo';
    }
  

    $fileContent =  $document['arquivo'];

    $char_incorretos = array("\"", "\'");
    $char_corretos = array("","");
    
    /*
     * Expressao regular para buscar o charset do arquivo html.
     * Dependo do charset, substitui entidades html (numeros designados
     * para caracteres especiais) por caracteres especiais 'normais'.
     * 
     * Pergunta no ar: sera que não eh mais simples substituir os caracteres
     * especiais na busca por endidades html, e garantir que todos os arquivos
     * html tenham somente entidades?
     */
    $er = "/charset=(\".*?\"|[A-Za-z0-9_]*?).*?\">/";
    preg_match($er,$fileContent,$charset);
    
    if ($charset != null && ($charset[0]=="charset=us-ascii\">" || $charset[0]=="charset=iso-8859-1\" />")) {
      $char_incorretos = array(
        "\"",
        "\'",
        "&atilde;",
        "&otilde;",
        "&ccedil;",
        "&aacute;",
        "&eacute;",
        "&iacute;",
        "&oacute;",
        "&uacute;",
        "&agrave;",
        "&egrave;",
        "&igrave;",
        "&ograve;",
        "&ugrave;",
        "&acirc;",
        "&ecirc;",
        "&icirc;",
        "&ocirc;",
        "&ucirc;",
        "&nbsp;"
      );
      
      $char_corretos = array(
        "",
        "",
        "ã",
        "õ",
        "ç",
        "á",
        "é",
        "í",
        "ó",
        "ú",
        "à",
        "è",
        "ì",
        "ò",
        "ù",
        "â",
        "ê",
        "î",
        "ô",
        "û",
        ""
      );
      
      $contents = strip_tags($fileContent);
      $contents = str_replace($char_incorretos, $char_corretos, $contents);
    }
    else {
      $contents = strip_tags($fileContent);
      $contents = str_replace($char_incorretos, $char_corretos, $contents);
      $contents = utf8_encode($contents);
    }
    /*
     * Outra versao do codigo acima.
     * 
		 * $er = "/charset=(\".*?\"|[A-Za-z0-9_]*?).*?\">/";
	   * preg_match($er,$fileContent,$charset);
	   * if ($charset[0]=="charset=us-ascii\">") {
	   *   echo "UTF8!!";
	   *   $contents = strip_tags($fileContent);
	   *   //$contents= preg_replace ('/([\x80-\xff])/se', "pack (\"C*\", (ord ($1) >> 6) | 0xc0, (ord ($1) & 0x3f) | 0x80)", $contents);
	   *   $contents = str_replace($char_incorretos, $char_corretos, $contents);
	   *   //$contents = utf8_encode($contents);
	   * }
	   * else {
	   *   print_r($charset);
	   *   $contents = strip_tags($fileContent);
	   *   $contents = str_replace($char_incorretos, $char_corretos, $contents);
	   *   $contents = utf8_encode($contents);
	   * }
     */
    
    //$contents = utf8_encode($contents);
    //$contents = $memoryManager->create(utf8_encode($contents));
    //echo $contents;
    //echo utf8_decode($contents);
    //echo "TITULO= ".$title."<br><br>";
    //echo "CONTEUDO= ".$contents."<br><br>";
    
    /*
     * Adiciona mais campos na entrada do indice do Zend.
     * Esses campos sao usados novamente no codigo de busca,
     * logo eh preciso ponderar se a classe PhpRiot eh realmente
     * desnecessaria.
     */
    $this->addField(Zend_Search_Lucene_Field::Keyword('id_midia', $document['id_midia'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('nome_arquivo', $document['nome_arquivo'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('document_id', $document['Documento_id'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('obra', $document['obra'][0]['titulo'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('autor_nome', $document['autores'][0]['nome_completo'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('autor_id', $document['autores'][0]['id'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('tipo', $document['tipo'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('genero', $document['genero'][0]['nome'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::UnIndexed('url',$document['link'],'UTF-8'));
    $this->addField(Zend_Search_Lucene_Field::Text('title',$title,'UTF-8'));//Titulo da midia
    $this->addField(Zend_Search_Lucene_Field::UnStored('content',strtolower($contents),'UTF-8'));
    
//    echo "TITULO: ".$title."\n";
//    echo "OBRA: ".$document['obra'][0]['titulo']."\n";
//    echo "AUTOR: ".$this->autores[0]['nome_completo']."\n";

    unset($fileContent);
    unset($contents);
  }
}
?>
