<?php

require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleFatosHistoricos extends DataTables {

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
     * Pesquisa e retorna os registros de fatos históricos do banco de dados
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
        $query = "SELECT $columns FROM FatoHistorico";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        }
        elseif ($descricao) {
        	$query.= sprintf(" WHERE descricao LIKE '%%%s%%'", mysqlx_real_escape_string($descricao));
        	$query.= " ORDER BY descricao";
        }
        //Verifica se os parâmetros para limite/paginação foram passados
        elseif (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
            $query.= sprintf(" LIMIT %u,%u", mysqlx_real_escape_string($start), mysqlx_real_escape_string($limit));
        }
        try {
            $result_sql = $this->DB->query($query);
        } catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return false;
        }
        //Retorna os resultados em forma de matriz
        return $this->DB->parse_result($result_sql);
    }

    public function get_by_data($ano_nascimento_autor = '',$ano_morte_autor = '', $fields = array(), $start = 0, $limit = 0) {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }

        $query = "";

        if ($ano_nascimento_autor) {

            //Prepara a consulta SQL
            $query.= "SELECT * FROM FatoHistorico";
            if($ano_morte_autor){
                $query.= sprintf(" WHERE (ano_inicio BETWEEN '%s' AND '%s') AND ano_fim <= '%s'", mysqlx_real_escape_string($ano_nascimento_autor), mysqlx_real_escape_string($ano_nascimento_autor + $this->intervalo_busca), mysqlx_real_escape_string($ano_morte_autor));
            }else{
                $query.= sprintf(" WHERE (ano_inicio BETWEEN '%s' AND '%s') AND ano_fim <= '%s'", mysqlx_real_escape_string($ano_nascimento_autor), mysqlx_real_escape_string($ano_nascimento_autor + $this->intervalo_busca), mysqlx_real_escape_string($ano_nascimento_autor + $this->intervalo_busca));
            }
     
        }elseif($ano_morte_autor){
            $query .= $query.= sprintf(" WHERE (ano_inicio >= '%s') AND ano_fim <= '%s'", mysqlx_real_escape_string($ano_morte_autor-$this->intervalo_busca), mysqlx_real_escape_string($ano_morte_autor));
        }
        //Verifica se os parâmetros para limite/paginação foram passados
        elseif (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
            $query.= sprintf(" LIMIT %u,%u", mysqlx_real_escape_string($start), mysqlx_real_escape_string($limit));
        }

        if (!$query)
        	return false;
        
        try {
            $result_sql = $this->DB->query($query);
        } catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return false;
        }
        //Retorna os resultados em forma de matriz
        return $this->DB->parse_result($result_sql);
    }

    /**
     * Adiciona o Fato Histórico no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($ano_inicio, $ano_fim, $fato) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

        //Validação
        $invalid_fields = $this->validate_parameters($ano_inicio, $ano_fim, $fato);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['ano_inicio'] = $ano_inicio;
        $fields['ano_fim'] = $ano_fim;
        $fields['descricao'] = $fato;

        try {
            $this->DB->insert('FatoHistorico', $fields, false);
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
    public function update($id, $ano_inicio, $ano_fim, $fato) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

        //Validação
        if (!$id || !is_numeric($id)) {
            return json_encode(array('error' => 'ID não especificado ou inválido'));
        }
        $invalid_fields = $this->validate_parameters($ano_inicio, $ano_fim, $fato);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['ano_inicio'] = $ano_inicio;
        $fields['ano_fim'] = $ano_fim;
        $fields['descricao'] = $fato;

        //Clausula WHERE para atualizar apenas o registro especificado
        $where = "id = '" . mysqlx_real_escape_string($id) . "'";

        try {
            $this->DB->update('FatoHistorico', $fields, $where);
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
    public function del($ids) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        if (!is_array($ids) || !$ids) {
            return json_encode(array('error' => 'IDs inválidos'));
        }

        //Exclui do banco de dados
        $ids_str = implode("','", $ids);
        $query = sprintf("DELETE FROM FatoHistorico
                          WHERE id IN ('%s')", $ids_str);
        try {
            $this->DB->query($query);
        } catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return json_encode(array('error' => 'Erro ao excluir no banco de dados'));
        }

        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));
    }

    /**
     * Valida os campos (parâmetros) do Fato Histórico
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($ano_inicio, $ano_fim, $fato) {
        $invalid_fields = array();
        if (!is_numeric($ano_inicio)) {
            $invalid_fields['ano_inicio'] = 'Ano deve ser numérico';
        }
        if (!is_numeric($ano_fim)) {
            $invalid_fields['ano_fim'] = 'Ano deve ser numérico';
        }
        if (intval($ano_inicio) > intval($ano_fim)) {
            $invalid_fields['ano_inicio'] = 'Deve ser menor que o ano de fim';
            $invalid_fields['ano_fim'] = 'Deve ser maior que o ano de início';
        }
        if (!$fato) {
            $invalid_fields['fato'] = 'Fato não especificado';
        }
        return $invalid_fields;
    }

}
