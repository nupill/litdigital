<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

/* Classe copiada do ControleGenero.php. Todos os métodos e variáveis mantiveram-se os mesmos.*/

class ControleIdiomas extends DataTables {
	
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
     * Pesquisa e retorna os registros de idioma do banco de dados
     * 
     * @param integer $id ID do idioma (opcional)
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @param integer $descricao Parte da descrição do idioma. Utilizado nos campos de auto completar (opcional)
     * @return array Resultado da pesquisa
     */
    public function get($id = '', $fields = array(), $start = 0, $limit = 0, $descricao = '') {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }
        //Prepara a consulta SQL
        $query = "SELECT $columns FROM Idioma";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        }
        //Senão, caso a descrição tenha sido especificada, aplica o filtro.
        //"AND TipoDocumento_id = 12" = Pega somente os documentos para a obra literária
        //Resolver caso para outros tipos de obra.
        elseif ($descricao) {
            $query.= sprintf(" WHERE descricao LIKE '%%%s%%'", mysqlx_real_escape_string($descricao));
            $query.= " ORDER BY descricao";
        }
        else {
        	$query.= " ORDER BY descricao";
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
	
	/**
     * Adiciona o idioma no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($descricao,$iso) {
                            
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        $invalid_fields = $this->validate_parameters($descricao,$iso);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['descricao'] = $descricao;
        $fields['iso'] = $iso;
        
        try {
            $this->DB->insert('Idioma', $fields, false);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }
        
        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));
    }

    /**
     * Atualiza o idioma no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update($id, $descricao, $iso) {
        
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        if (!$id) {
            return json_encode(array('error' => 'ID não especificado'));
        }
        $invalid_fields = $this->validate_parameters($descricao, $iso);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['descricao'] = $descricao;
        $fields['iso'] = $iso;
        
        $where = "id = '" . mysqlx_real_escape_string($id) . "'";
        
        try {         
            $this->DB->update('Idioma', $fields, $where);
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
        $query = sprintf("DELETE FROM Idioma
                          WHERE id IN ('%s')",
                          $ids_str);
        try {
            $this->DB->query($query);
        }
        catch (Exception $e) {
 //           Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return json_encode(array('error' => 'Não pode excluir idioma pois ele está sendo utilizado'));
        }
        
        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));        
    }
   
    /**
     * Valida os campos (parâmetros) do idioma
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($descricao,$iso) {
        $invalid_fields = array();
        if (!$descricao) {
            $invalid_fields['descricao'] = 'O nome não pode ser vazio';
        }
        if (!$descricao) {
        	$invalid_fields['iso'] = 'O ISO não pode ser vazio';
        }
        return $invalid_fields;
    }
}