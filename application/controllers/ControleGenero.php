<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleGenero extends DataTables {
	
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
     * Pesquisa e retorna os registros de gêneros do banco de dados
     * 
     * @param integer $id ID do gênero (opcional)
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @param integer $descricao Parte da descrição do gênero. Utilizado nos campos de auto completar (opcional)
     * @param string $tipoDocumento_id Usado para retornar o conjunto de gêneros pertencentes à este id (opcional)
     * @return array Resultado da pesquisa
     */
    public function get($id = '', $fields = array(), $start = 0, $limit = 0, $descricao = '', $tipoDocumento_id = '') {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }
        //Prepara a consulta SQL
        $query = "SELECT $columns FROM Genero";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        }
        //Senão, caso a descrição tenha sido especificada, aplica o filtro.        
        elseif ($descricao) {
            $query.= sprintf(" WHERE nome LIKE '%%%s%%'", mysqlx_real_escape_string($descricao));
            //Caso, o tipoDocumento_id tenha sido especificado.
            if ($tipoDocumento_id) {
                $query.= sprintf(" AND TipoDocumento_id = '%s'",                    
                    mysqlx_real_escape_string($tipoDocumento_id));                
            }
            $query.= " ORDER BY nome";
        }
        else {
        	$query.= " ORDER BY nome";
        }
        //Verifica se os parâmetros para limite/paginação foram passados
        if (!$id && is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
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
	
    public function getTipoDoc($id) {
    	$query = sprintf("SELECT td.nome AS nome FROM TipoDocumento td JOIN Genero g ON g.TipoDocumento_id=td.id WHERE g.id = %s",$id); 
    	try {
    		$result_sql = $this->DB->query($query);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return false;
    	}
    	return $this->DB->parse_result($result_sql)[0]['nome'];
    }
	/**
     * Adiciona o gênero no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($nomeGenero,$tipoDoc) {
                            
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        $invalid_fields = $this->validate_parameters($nomeGenero,$tipoDoc);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
               
        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['nome'] = $nomeGenero;
        $fields['TipoDocumento_id'] = $tipoDoc;
        
        try {
            $this->DB->insert('Genero', $fields, false);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }
        
        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));
    }

    /**
     * Atualiza o gênero no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update($id, $nomeGenero, $tipodoc) {
        
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        if (!$id) {
            return json_encode(array('error' => 'ID não especificado'));
        }
        $invalid_fields = $this->validate_parameters($nomeGenero,$tipodoc);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['nome'] = $nomeGenero;
        $fields['TipoDocumento_id'] = $tipodoc;
        
        
        $where = "id = '" . mysqlx_real_escape_string($id) . "'";
        
        try {         
            $this->DB->update('Genero', $fields, $where);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
        }

        return json_encode(array('error' => null));
    }

    /**
     * Remove os registros do banco de dados
     * 
     * @param array $ids IDs dos registros a serem excluídos
     * @return string Resultado no formato JSON
     */
    public function del($ids) {
        
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        if (!is_array($ids) || !$ids) {
            return json_encode(array('error' => 'IDs inválidos'));
        }
        
        //Exclui do banco de dados
        $ids_str = implode("','", $ids);
        $query = sprintf("DELETE FROM Genero
                          WHERE id IN ('%s')",
                          $ids_str);
        try {
            $this->DB->query($query);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return json_encode(array('error' => 'Erro ao excluir no banco de dados'));
        }
        
        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));        
    }
   
    /**
     * Valida os campos (parâmetros) do gênero
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($nomeGenero,$tipoDoc) {
        $invalid_fields = array();
        if (!$nomeGenero) {
            $invalid_fields['nome'] = 'O nome não pode ser vazio';
        }
        if (!$nomeGenero) {
        	$invalid_fields['tipoDoc'] = 'O tipo de documento não pode ser vazio';
        }
        return $invalid_fields;
    }
    
    protected function fnBuildQuery($aParams) {
    	/* Query statements */
    	$aClauses = $this->fnBuildQueryClauses($aParams);
    	/* Parse columns to JOIN */
    	$aColumns = explode(',', $aParams['sColumns']);
    	/* Query */
    	$sQuery = "SELECT SQL_CALC_FOUND_ROWS
    	g.id,
    	g.nome,
    	d.nome as tipoDoc
    	FROM Genero g
    	JOIN TipoDocumento d
    	ON g.TipoDocumento_id = d.id
    	{$aClauses['sWhere']}
    	{$aClauses['sOrder']}
    	{$aClauses['sLimit']}";
    	return $sQuery;
    }
    
    protected function fnBuildWhereClause($aColumns, $aParams) {
    	$sSearch = $aParams['sSearch'];
    	$sWhere = '';
    	if (is_array($aColumns) && $sSearch != '') {
    		// $sSearch = preg_replace_callback('/\s\s+/', ' ', $sSearch); //Remove extra spaces
    		$sSearch = preg_replace('/\s\s+/', ' ', $aParams['sSearch']); //Remove extra spaces
    		$sSearch = str_replace(' ', '%', $sSearch); //Replace space with %
    		$sWhere = 'WHERE ';
    		array_shift($aColumns);
    		foreach ($aColumns as $aColumn) {
    			if ($aColumn == 'nome') {
    				$aColumn = 'g.nome';
    			}
    			elseif ($aColumn == 'tipoDoc') {
    				$aColumn = 'd.nome';
    			}
    			$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
    		}
    		$sWhere = substr($sWhere, 0, -4);
    	}
    	return $sWhere;
    }
    
}