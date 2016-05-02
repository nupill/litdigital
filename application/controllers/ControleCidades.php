<?php

require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/ControleEditoras.php');


class ControleCidades extends DataTables {

    protected $DB;
    private static $instance;
    private $intervalo_busca = 100;

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
    private function __clone() {
        
    }

    /**
     * Pesquisa e retorna os registros de Acervos do banco de dados
     * 
     * @param integer $id ID do autor (opcional)
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
public function get($id = '', $fields = array(), $start = 0, $limit = 0, $descricao = '') {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }
        //Prepara a consulta SQL
        $query = "SELECT $columns FROM cidades";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        }
        //Senão, caso a descrição tenha sido especificada, aplica o filtro
 //       elseif ($nome) {
 //           $query.= sprintf(" WHERE nome LIKE '%%%s%%'", mysqlx_real_escape_string($descricao));
 //           $query.= " ORDER BY nome";
 //       }
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

    public function getPais($id, $fields = array()) {
    	$columns = '*';
    	if ($fields) {
    		//Junta os campos/colunas para a consulta SQL
    		$columns = implode(', t1.', $fields);
    	}
    	//Prepara a consulta SQL
    	$query = sprintf("SELECT t1.$columns
    			FROM Paises t1
    			JOIN cidades t2
    			ON (t1.id = t2.pais)
    			WHERE t2.id='%s'",
    			mysqlx_real_escape_string($id));
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
    
    /**
     * Adiciona o Acervo no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($nome, $estado, $pais, $lat=NULL, $lng=NULL) {
    	
          
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

        //Validação
        $invalid_fields = $this->validate_parameters($nome,$estado,$pais,$lat,$lng);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
        

        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['nome'] = $nome;
        
        if (($pais!=1) AND (!$estado)) {
        	$fields['estado_id'] = 0;
        }
        else {
        	$fields['estado_id'] = $estado;
        }
        $fields['pais'] = $pais;
        $fields['lat'] = $lat;
        $fields['lng'] = $lng;
        
   //     if ($sameThan)
   //     	$fields['antigo_nome_de'] = $sameThan;
   //     else 
   //     	$fields['antigo_nome_de'] = NULL;
             
        	 
        try {
            $this->DB->insert('cidades', $fields);
        } catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }

        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));
    }

    /**
     * Atualiza o Documento no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update($id,$nome,$estado, $pais, $lat=NULL, $lng=NULL) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

        if (!$id) {
        	return json_encode(array('error' => 'ID não especificado ou inválido'));
        }
        //Validação
        $invalid_fields = $this->validate_parameters($nome,$estado,$pais,$lat,$lng);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['nome'] = $nome;
        $fields['pais'] = $pais;
        $fields['lat'] = $lat;
        $fields['lng'] = $lng;
        
        if (($pais!=1) AND (!$estado)) {
        	$fields['estado_id'] = 0;
        }
        else {
        	$fields['estado_id'] = $estado;
        }
        
 //       if ($sameThan)
 //       	$fields['antigo_nome_de'] = $sameThan;
 //       else 
  //      	$fields['antigo_nome_de'] = NULL;
        //Clausula WHERE para atualizar apenas o registro especificado
        $where = "id = '" . $id . "'";

        try {
            $this->DB->update('cidades', $fields, $where);
            $controle_editoras = ControleEditoras::getInstance();
            $controle_editoras->atualizaLocais();
        } catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
        }

        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));
    }

    /**
     * Remove os registros do banco de dados
     * 
     * @param array $ids IDs dos registros a serem excluídos
     * @return string Resultado no formato JSON
     */
    public function del($ids, $force=NULL) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        if (!is_array($ids) || !$ids) {
            return json_encode(array('error' => 'IDs inválidos'));
        }
        
        	try {
        		$ids_str = implode("','", $ids);
        		
        		$query = sprintf("DELETE FROM cidades
                          WHERE id IN ('%s')", $ids_str);
        		$this->DB->query($query);
            
        	} catch (Exception $e) {
            	Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            	return json_encode(array('error' => 'Erro ao excluir no banco de dados'));
        	}

        	//Nenhum erro ocorreu. Retorna nulo.
        	return json_encode(array('error' => null));
    }

    /**
     * Valida os campos (parâmetros) do Acervo
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($nome,$estado,$pais,$lat,$lng) {
        $invalid_fields = array();
        if (!$pais) {
        	$invalid_fields['pais'] = 'Nome pais deve ser especificado';
        }
        if (!$nome) {
            $invalid_fields['cidade'] = 'Nome da cidade deve ser especificado';
        }
        if (!$estado) {
        	if ($pais==1)
        		$invalid_fields['estado'] = 'Nome do estado deve ser especificado';
        }
        if ($lat or $lng) {
        	if (!is_numeric($lat)) 
        		$invalid_fields['latitude'] = 'Latitude tem que ser um número';
        	if (!is_numeric($lng)) 
        		$invalid_fields['longitude'] = 'Longitude tem que ser um número';
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
    	c.id as id,
    	c.nome as nome,
    	e.nome as estado,
    	p.nome as pais
    	FROM cidades c
    	JOIN estados e
    	ON c.estado_id = e.id
    	JOIN Paises p
    	ON p.id = c.pais
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
    				$aColumn = 'c.nome';
    			}
    			elseif ($aColumn == 'estado') {
    				$aColumn = 'e.nome';
    			} elseif ($aColumn == 'pais') {
    				$aColumn = 'p.nome';
    			} 
    			$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
    		}
    		$sWhere = substr($sWhere, 0, -4);
    	}
    	return $sWhere;
    }
    
    
}
