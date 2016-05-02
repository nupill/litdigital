<?php
require_once('Zend/Search/Lucene.php');
require_once('Zend/Search/Lucene/Analysis/TokenFilter/StopWords.php');
require_once('Zend/Search/Lucene/Analysis/TokenFilter/ShortWords.php');
require_once('removeAcentos.php');

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
    //$analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num;
    //Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
    
    
    /*
     * Busca o nome do arquivo no disco
     */
    
    $title = $document['titulo_midia'];
    //$title = utf8_encode($title);
    
    if ($title=='') {
      $title = 'Sem Titulo';
    }
  
    $fileContent =  $document['arquivo'];
    
    $char_corretos = array("", "e","e","E","E","u","u","U","U","i","i","I","I","o","o","O","O","a","a","A","A","ç","Ç","a","A","o","O","a","A","e","E","i","I","o","O","u","u");
    $char_incorretos = array("&nbsp;","&eacute;","&egrave;","&Eacute;","&Eacute;","&uacute;","&ugrave;","&Uacute;","&Ugrave;","&iacute;","&icirc;","&Iacute;","&Igrave;","&oacute;","&ograve;","&Oacute;","&Ograve;","&aacute;","&agrave;","&Aacute;","&Agrave;","&ccedil;","&Ccedil;","&atilde;","&Atilde;","&otilde;","&Otilde;","&acirc;","&Acirc;","&ecirc;","&Ecirc;","&iexcl;","&Icirc;","&ocirc;","&Ocirc;","&ucirc;","&Ucirc;");
    
    $fileContent = str_replace($char_incorretos, $char_corretos, $fileContent);
    
    /*
    $fileContent = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $fileContent);
    $fileContent = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $fileContent);
    */

    $fileContent = preg_replace_callback('~&#x([0-9a-f]+);~i',
    		create_function ('$matches', 'return chr(hexdec($matches[1]));'), $fileContent);
    
    $fileContent = preg_replace_callback('~&#([0-9]+);~',
    		create_function ('$matches', 'return chr($matches[1]);'), $fileContent);
        
    
    if(!mb_check_encoding($fileContent, 'UTF-8')
    		OR !($fileContent === mb_convert_encoding(mb_convert_encoding($fileContent, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
    			$fileContent = mb_convert_encoding($fileContent, 'UTF-8'); }
    
    $contents = strip_tags($fileContent);
    			
    $contents=remove_accents($contents);
    

  /*  $fileContent =  $document['arquivo'];

    $char_incorretos = array("\"", "\'");
    $char_corretos = array("","");
    
    $er = "/charset=(\".*?\"|[A-Za-z0-9_]*?).*?\">/";
    preg_match($er,$fileContent,$charset);
    
    if ($charset != null && ($charset[0]=="charset=us-ascii\">" || $charset[0]=="charset=iso-8859-1\" />")) {
		echo 'entrou charset';
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
    
 //   $index->commit();
 //   $index->optimize();

    unset($fileContent);
    unset($contents);    
  }
}
?>
