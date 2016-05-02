<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../zf/removeAcentos.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

// set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());

class ControleBusca extends DataTables {

	protected $DB;
	private static $instance;

	/**
	 * Obtém a única instância da classe, restringindo-a somente a um objeto (Singleton)
	 *
	 * @see http://en.wikipedia.org/wiki/Singleton_pattern
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Construtor privado, chamado a partir da função getInstance()
	 */
	private function __construct() {
		$this->DB = DB::getInstance();
		$this->DB->connect();
	}

	/**
	 * Previne cópia do objeto
	 */
	private function __clone() { }

	/**
	 * Pesquisa e retorna os registros de autores do banco de dados
	 *
	 * @param string $term Termo de busca
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @return array Resultado da pesquisa
	 */
	/* NAO ESTA MAIS SENDO UTILIZADA. AGORA É FEITO VIA DATATABLES
	public function busca_simples($term, $start = 0, $limit = 0) {
		$columns = '*';

		//Verifica a forma de busca
		if ($term && $term[0] == '"' && $term[strlen($term)-1] == '"') { //Frase exata
			$term = str_replace('"', '', $term);
			if (!remove_stop_words($term)) {
				return false;
			}
		}
		else { //Qualquer palavra
			$term = remove_stop_words($term);
			if (!$term) {
				return false;
			}
			$term = explode(' ', $term);
		}

		//Prepara a consulta SQL
		$query = "SELECT SQL_CALC_FOUND_ROWS
                    dw.*, t.nome AS tipo,
                    d.ano_producao,
                    g.nome AS genero,
                    o.seculo_producao,
                    o.seculo_publicacao
		  		  FROM DocumentoView dw
		  		  JOIN TipoDocumento t
				    ON t.id = dw.TipoDocumento_id
				  JOIN Documento d
				    ON d.id = dw.id
				  JOIN Genero g
				    ON g.id = d.Genero_id
				  LEFT JOIN ObraLiteraria o
				    ON o.Documento_id = dw.id";

		//Adiciona a cláusula WHERE
		if ($term) {
			if (is_array($term)) { //Qualquer palavra
				$query.= " WHERE";
				foreach ($term as $search_term) {
					$query .= sprintf(" dw.titulo LIKE '%%%s%%' OR autores LIKE '%%%s%%' OR",
					mysqlx_real_escape_string($search_term),
					mysqlx_real_escape_string($search_term));
				}
				$query = substr($query, 0, -3); //Remove o último OR
			}
			else { //Frase exata
				$query.= sprintf(" WHERE dw.titulo LIKE '%%%s%%'
                		  		  	OR autores LIKE '%%%s%%'",
				mysqlx_real_escape_string($term),
				mysqlx_real_escape_string($term));
			}
		}
		 
		//Verifica se os parâmetros para limite/paginação foram passados
		if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			$query.= sprintf(" LIMIT %u,%u",
			mysqlx_real_escape_string($start),
			mysqlx_real_escape_string($limit));
		}
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $this->DB->parse_result($result_sql);
	}
    */

	/**
	 * Pesquisa e retorna os registros de autores do banco de dados
	 *
	 * @param string $... Parâmetros de busca
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @return array Resultado da pesquisa
	 */
	/* NAO ESTA MAIS SENDO UTILIZADA. AGORA É FEITO VIA DATATABLES
	public function busca_autor($nome = '', $ano_nascimento_inicio = '', $ano_nascimento_inicio_era = 'd.C.',
								$ano_nascimento_fim = '', $ano_nascimento_fim_era = 'd.C.',
								$local_nascimento = '', $catarinense = false, $ano_morte_inicio = '',
								$ano_morte_inicio_era = 'd.C.', $ano_morte_fim = '',
								$ano_morte_fim_era = 'd.C.', $local_morte = '', $descricao = '',
								$start = 0, $limit = 0) {
		$columns = '*';

		//Verifica a forma de busca
		if ($nome && $nome[0] == '"' && $nome[strlen($nome)-1] == '"') { //Frase exata
			$nome = str_replace('"', '', $nome);
			if (!remove_stop_words($nome)) {
				return false;
			}
		}
		else { //Qualquer palavra
			$nome = remove_stop_words($nome);
			if ($nome) {
				$nome = explode(' ', $nome);
			}
		}

		//Prepara a consulta SQL
		$query = "SELECT id, nome_completo, pseudonimo, local_nascimento, ano_nascimento, seculo_nascimento, catarinense
        	      FROM Autor";

		//Adiciona os filtros
		$where = array();
		if ($nome) {
			if (is_array($nome)) { //Qualquer palavra
				$where_nome = "(";
				foreach ($nome as $search_term) {
					$where_nome .= sprintf("nome_completo LIKE '%%%s%%' OR
                    					    nome_usual LIKE '%%%s%%' OR
                    					    pseudonimo LIKE '%%%s%%' OR",
					mysqlx_real_escape_string($search_term),
					mysqlx_real_escape_string($search_term),
					mysqlx_real_escape_string($search_term));
				}
				$where_nome = substr($where_nome, 0, -3); //Remove o último OR
				$where_nome.= ")";
				$where[] = $where_nome;
			}
			else { //Frase exata
				$where[] = sprintf("(nome_completo LIKE '%%%s%%' OR
                    		 	    nome_usual LIKE '%%%s%%' OR
                    			    pseudonimo LIKE '%%%s%%') ",
				mysqlx_real_escape_string($nome),
				mysqlx_real_escape_string($nome),
				mysqlx_real_escape_string($nome));
			}
		}

		if ($ano_nascimento_inicio && $ano_nascimento_fim) {
			$where[] = sprintf("(ano_nascimento BETWEEN '%s' AND '%s')",
			mysqlx_real_escape_string($ano_nascimento_inicio),
			mysqlx_real_escape_string($ano_nascimento_fim));
		}
		elseif ($ano_nascimento_inicio) {
			$where[] = sprintf("ano_nascimento = '%s'",
			mysqlx_real_escape_string($ano_nascimento_inicio));
		}
		elseif ($ano_nascimento_fim) {
			$where[] = sprintf("ano_nascimento = '%s'",
			mysqlx_real_escape_string($ano_nascimento_fim));
		}

		//TODO: FAZER BUSCAS PELOS SECULOS

		if ($local_nascimento) {
			$where[] = sprintf("local_nascimento LIKE '%%%s%%'",
			mysqlx_real_escape_string($local_nascimento));
		}

		if ($catarinense) {
			$where[] = "catarinense = 1";
		}

		if ($ano_morte_inicio && $ano_morte_fim) {
			$where[] = sprintf("(ano_morte BETWEEN '%s' AND '%s')",
			mysqlx_real_escape_string($ano_morte_inicio),
			mysqlx_real_escape_string($ano_morte_fim));
		}
		elseif ($ano_morte_inicio) {
			$where[] = sprintf("ano_morte = '%s'",
			mysqlx_real_escape_string($ano_morte_inicio));
		}
		elseif ($ano_morte_fim) {
			$where[] = sprintf("ano_morte = '%s'",
			mysqlx_real_escape_string($ano_morte_fim));
		}
		 
		//TODO: FAZER BUSCAS PELOS SECULOS

		if ($local_morte) {
			$where[] = sprintf("local_morte LIKE '%%%s%%'",
			mysqlx_real_escape_string($local_morte));
		}

		if ($descricao) {
			$where[] = sprintf("descricao LIKE '%%%s%%'",
			mysqlx_real_escape_string($descricao));
		}

		if ($where) {
			$query.= ' WHERE ' . implode(' AND ', $where);
		}

		//Verifica se os parâmetros para limite/paginação foram passados
		if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			$query.= sprintf(" LIMIT %u,%u",
			mysqlx_real_escape_string($start),
			mysqlx_real_escape_string($limit));
		}
		try {
			//FB::log($query);
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $this->DB->parse_result($result_sql);
	}
	*/

	/**
	 * Pesquisa e retorna os registros de documentos do banco de dados
	 *
	 * @param string $... Parâmetros de busca
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @return array Resultado da pesquisa
	 */
	/* NAO ESTA MAIS SENDO UTILIZADA. AGORA É FEITO VIA DATATABLES
	public function busca_documento($titulo = '', $autores = '', $tipo = '', $tipo_acervo = '', $genero = '', $categoria = '',
									$ano_inicio = '', $ano_fim = '', $seculo_inicio = '', $seculo_fim = '',
									$descricao = '', $idioma = '', $editora_id = '', $start = 0, $limit = 0) {
		$columns = '*';
		
		//Verifica a forma de busca
		if ($titulo && $titulo[0] == '"' && $titulo[strlen($titulo)-1] == '"') { //Frase exata
			$titulo = str_replace('"', '', $titulo);

			if (!remove_stop_words($titulo)) {
				return false;
			}
		}
		else { //Qualquer palavra
			$titulo = remove_stop_words($titulo);

			if ($titulo) {
				$titulo = explode(' ', $titulo);
			}
		}

		//Prepara a consulta SQL
		$query = "SELECT dw.*, t.nome AS tipo, d.ano_producao, g.nome AS genero, o.seculo_producao, o.seculo_publicacao
                  FROM DocumentoView dw
                  JOIN TipoDocumento t
                    ON t.id = dw.TipoDocumento_id
                  JOIN Documento d
                    ON d.id = dw.id
                  JOIN AutorDocumento ad
                    ON ad.Documento_id = d.id
                  JOIN Autor a
                    ON a.id = ad.Autor_id
                  JOIN Genero g
                    ON g.id = d.Genero_id
                  LEFT JOIN ObraLiteraria o
                    ON o.Documento_id = dw.id";

		//Adiciona os filtros
		$where = array();

		if ($titulo) {
			// Se o filtro do título for por 'qualquer palavra'
			if (is_array($titulo)) {
				$where_titulo = "(";
				foreach ($titulo as $search_term) {
					$where_titulo .= sprintf("d.titulo LIKE '%%%s%%' OR
                                              o.subtitulo LIKE '%%%s%%' OR
                                              o.titulo_alternativo LIKE '%%%s%%' OR ",
					mysqlx_real_escape_string($search_term),
					mysqlx_real_escape_string($search_term),
					mysqlx_real_escape_string($search_term));
				}
				$where_titulo = substr($where_titulo, 0, -3); //Remove o último OR
				$where_titulo.= ")";
				$where[] = $where_titulo;
				 
			}
			// Se o filtro do título for por 'frase exata'
			else {
				$where[] = sprintf("(d.titulo LIKE '%%%s%%' OR
                                    o.subtitulo LIKE '%%%s%%' OR
                                    o.titulo_alternativo LIKE '%%%s%%') ",
									mysqlx_real_escape_string($titulo),
									mysqlx_real_escape_string($titulo),
									mysqlx_real_escape_string($titulo));

			}
		}

		if ($autores) {
			$autor = explode(',', $autores);
			$where_autor = "(";
			for ($i=0; $i<sizeof($autor)-1; $i++) {
				$where_autor .= sprintf("a.nome_completo LIKE '%%%s%%' OR
                                         a.nome_usual LIKE '%%%s%%' OR
                                         a.pseudonimo LIKE '%%%s%%' OR ",
										mysqlx_real_escape_string(trim($autor[$i])),
										mysqlx_real_escape_string(trim($autor[$i])),
										mysqlx_real_escape_string(trim($autor[$i])));
				 
			}
			$where_autor = substr($where_autor, 0, -3); //Remove o último OR
			$where_autor.= ")";
			$where[] = $where_autor;
		}
		 
		if ($tipo && $tipo != 'acervo') {
			$where[] = sprintf("(dw.TipoDocumento_id = '%d')",
			mysqlx_real_escape_string($tipo));
		}
		elseif ($tipo && $tipo == 'acervo') {
			$where[] = sprintf("(dw.TipoDocumento_id = '%d')",
			mysqlx_real_escape_string($tipo_acervo));
		}
		if ($genero) {
			$where[] = sprintf("(d.Genero_id = '%d')",
			mysqlx_real_escape_string($genero));
		}

		if ($categoria) {
			$where[] = sprintf("(d.Categoria_id = '%d')",
			mysqlx_real_escape_string($categoria));
		}
		 
		if ($ano_inicio && $ano_fim && $ano_inicio <= $ano_fim) {
			$where[] = sprintf("(d.ano_producao >= '%s' AND o.ano_producao_fim <= '%s') OR (o.ano_publicacao_inicio >= '%s' AND o.ano_publicacao_fim <= '%s')",
								mysqlx_real_escape_string($ano_inicio),
								mysqlx_real_escape_string($ano_fim),
								mysqlx_real_escape_string($ano_inicio),
								mysqlx_real_escape_string($ano_fim));
		}
		elseif ($ano_inicio) {
			$where[] = sprintf("d.ano_producao = '%s'",
			mysqlx_real_escape_string($ano_inicio));
		}
		elseif ($ano_fim) {
			$where[] = sprintf("o.ano_producao_fim = '%s'",
			mysqlx_real_escape_string($ano_fim));
		}

		if ($seculo_inicio && $seculo_fim) {
			$intervalo_seculos = get_roman_centuries($seculo_inicio, $seculo_fim);
			$ano_inicio = get_first_century_year($seculo_inicio);
			$ano_fim = get_first_century_year($seculo_fim);
			if ($ano_inicio <= $ano_fim) {
				$where[] = sprintf("( (o.seculo_producao IN ('%s')) OR (o.seculo_publicacao IN ('%s')) OR " .
		            			   "(d.ano_producao >= '%s' AND o.ano_producao_fim <= '%s') OR (o.ano_publicacao_inicio >= '%s' AND o.ano_publicacao_fim <= '%s') )",
								   implode("', '", $intervalo_seculos),
								   implode("', '", $intervalo_seculos),
								   $ano_inicio,
								   $ano_fim+99,
								   $ano_inicio,
								   $ano_fim+99);
			}
			 
		}
		elseif ($seculo_inicio) {
			$ano_inicio = get_first_century_year($seculo_inicio);
			$where[] = sprintf("(o.seculo_producao = '%s') OR (o.seculo_publicacao = '%s') OR " .
            		           "(d.ano_producao = '%s') OR (o.ano_publicacao_inicio = '%s') ",
			mysqlx_real_escape_string($seculo_inicio),
			mysqlx_real_escape_string($seculo_inicio),
			$ano_inicio,
			$ano_inicio);
		}
		elseif ($seculo_fim) {
			$ano_inicio = get_first_century_year($seculo_fim);
			$where[] = sprintf("(o.seculo_producao = '%s') OR (o.seculo_publicacao = '%s') OR " .
            		           "(o.ano_producao_fim = '%s') OR (o.ano_publicacao_fim = '%s') ",
							   mysqlx_real_escape_string($seculo_fim),
							   mysqlx_real_escape_string($seculo_fim),
			$ano_inicio+99,
			$ano_inicio+99);
		}
		if ($descricao) {
			$where[] = sprintf("d.descricao LIKE '%%%s%%'",
			mysqlx_real_escape_string($descricao));
		}
		if ($idioma) {
			$where[] = sprintf("(d.Idioma_id = '%d')",
			mysqlx_real_escape_string($idioma));
		}
		if ($editora) {
			$where[] = sprintf("(d.nome_editora like '%%%s%%')",
			mysqlx_real_escape_string($editora));
		}
		if ($local_editora) {
			$where[] = sprintf("(d.local_editora like '%%%s%%')",
			mysqlx_real_escape_string($local_editora));
		}

		// Adiciona a cláusula WHERE à consulta
		if ($where) {
			$query.= ' WHERE ' . implode(' AND ', $where);
		}

		//Verifica se os parâmetros para limite/paginação foram passados
		if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			$query.= sprintf(" LIMIT %u,%u",
			mysqlx_real_escape_string($start),
			mysqlx_real_escape_string($limit));
		}

		//exit($query);

		try {
			//FB::log($query);
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $this->DB->parse_result($result_sql);
	}
	*/

    /*
	public function busca_conteudo($palavras, $tipo) {

		//require_once(dirname(__FILE__) . '/../zf/Zend/Search/Lucene.php');
		require_once('Search/Lucene.php');
		Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('UTF-8');
		$analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive;
		Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
		//como vai estar na pasta public/busca/conteudo, deve procurar a pasta indexes
		$index = Zend_Search_Lucene::open('../../../application/zf/indexes');
		//$index = Zend_Search_Lucene::open('../zf/indexes');

		//tratamento inicial da busca
		$busca = ltrim($palavras);
		$busca = strtolower($busca);

		//retirar caracteres especiais
		$char_incorretos = array(',', '.','?','!','-',';');
		$char_corretos = array('','','','',' ','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','');
		$busca = str_replace($char_incorretos, $char_corretos, $busca);

		//$busca = utf8_encode($busca);
		$words = explode(' ', $busca);

		if ($tipo == 1){
			//se for frase nao precisa fazer nada
			$query = new Zend_Search_Lucene_Search_Query_Phrase($words);
		}
		else {
			//se for todas ou qualquer palavra, tem que adicionar termo por termo
			$query = new Zend_Search_Lucene_Search_Query_MultiTerm();
			$busca = explode(' ', $busca);
			$i = count($busca);
			for ($w=0; $w<$i; $w++){
				if ($words[$w] != '') {
					if ($tipo == 2){
						$query->addTerm(new Zend_Search_Lucene_Index_Term($words[$w], 'content'), true);
					}
					else {
						$query->addTerm(new Zend_Search_Lucene_Index_Term($words[$w], 'content'));
					}
				}
			}
		}
		$hits = $index->find($query);
		return $hits;
	}
	*/

    public function busca_conteudo($palavras, $tipo) {
    	$palavras = remove_accents($palavras);
    	 
       // require_once(dirname(__FILE__) . '/../zf/Zend/Search/Lucene.php');
       
    	// require_once('/../zf/Zend/Search/Lucene.php');
    	// set_include_path(get_include_path() . PATH_SEPARATOR . '../zf/Zend');
    	// ini_set('include_path', '../zf/Zend');
    	// set_include_path(".:" .dirname(__FILE__) . '/../../../application/zf:'.dirname(__FILE__).'/../../../application/zf/Zend');
    	
    	set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());
    	require_once ('Zend/Search/Lucene.php');

        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('UTF-8');
        // $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive;
        $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num;
        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
        
        //como vai estar na pasta public/busca/conteudo, deve procurar a pasta indexes
        $index = Zend_Search_Lucene::open('../../../application/zf/indexes');
        
       // $index = Zend_Search_Lucene::open('../zf/indexes');
        
        //tratamento inicial da busca
        $busca = ltrim($palavras);
        $busca = strtolower($busca);
        

           //retirar caracteres especiais
        $char_incorretos = array("’",',','.','?','!','-',';',"\"");
        $char_corretos =   array(' ',' ',' ',' ',' ',' ',' ','');
        
        $busca = str_replace($char_incorretos, $char_corretos, $busca);
       //$busca = str_replace("’", " ", $busca);

        //$busca = utf8_encode($busca);
        $busca = mb_convert_encoding($busca, 'UTF-8');
        $words = explode(' ', $busca);

        if ($tipo == 3){
            //se for frase nao precisa fazer nada
            $query = new Zend_Search_Lucene_Search_Query_Phrase($words);
           
        }
        else {
            //se for todas ou qualquer palavra, tem que adicionar termo por termo
            $query = new Zend_Search_Lucene_Search_Query_MultiTerm();
            
            $busca = explode(' ', $busca);
            
            $i = count($busca);
            for ($w=0; $w<$i; $w++){
                if ($words[$w] != '') {
                    if ($tipo == 2){
                        $query->addTerm(new Zend_Search_Lucene_Index_Term($words[$w], 'content'), true);
                    }
                    else {
                        $query->addTerm(new Zend_Search_Lucene_Index_Term($words[$w], 'content'));
                    }
                }
            }
        }
      
	    $hits = $index->find($query);	 
        return $hits;
    }
}
