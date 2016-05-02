<?php

require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/ControleEditoras.php');

class ControlePaises extends DataTables {

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
        $query = "SELECT $columns FROM Paises";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        }
        //Senão, caso a descrição tenha sido especificada, aplica o filtro
  //      elseif ($nome) {
//            $query.= sprintf(" WHERE nome LIKE '%%%s%%'", mysqlx_real_escape_string($descricao));
 //           $query.= " ORDER BY nome";
//        }
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

    /**
     * Adiciona o Acervo no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($nome, $lat=NULL, $lng=NULL) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

        //Validação
        $invalid_fields = $this->validate_parameters($nome,$lat,$lng);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['nome'] = $nome;
        $fields['lat'] = $lat;
        $fields['lng'] = $lng;
        
 //       if ($sameThan)
 //       	$fields['sameThan'] = $sameThan;
        
        try {
            $this->DB->insert('Paises', $fields);
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
    public function update($id,$nome,$lat=NULL, $lng=NULL) {

        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

        if (!$id) {
        	return json_encode(array('error' => 'ID não especificado ou inválido'));
        }
        //Validação
        $invalid_fields = $this->validate_parameters($nome,$lat,$lng);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['nome'] = $nome;
        $fields['lat'] = $lat;
        $fields['lng'] = $lng;
        
	//	if ($sameThan)
     //   	$fields['sameThan'] = $sameThan;
	//	else 
	//		$fields['sameThan'] = NULL;
				
        //Clausula WHERE para atualizar apenas o registro especificado
        $where = "id = '" . $id . "'";

        try {
            $this->DB->update('Paises', $fields, $where);
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
        		
        		$query = sprintf("DELETE FROM Paises
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
    private function validate_parameters($nome,$lat,$lng) {
        $invalid_fields = array();
        if (!$nome) {
            $invalid_fields['nome'] = 'Nome do Pais deve ser especificado';
        }
        if ($lat or $lng) {
        	if (!is_numeric($lat))
        		$invalid_fields['latitude'] = 'Latitude tem que ser um número';
        	if (!is_numeric($lng))
        		$invalid_fields['longitude'] = 'Longitude tem que ser um número';
        }
        return $invalid_fields;
    }

}
