<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleConfig extends DataTables {
	
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
     * Pesquisa e retorna variáveis de configuração do banco de dados
     * 
     * @param string $name campo (coluna) da tabela a ser retornado
     * @return $value valor da variável de configuração
     */
    public function get($name) {
    	 
        $columns = '*';
        //Prepara a consulta SQL
        $query= sprintf("SELECT value FROM Config WHERE name='%s' LIMIT 1", mysqlx_real_escape_string($name));
        try {
            $result_sql = $this->DB->query($query);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return false;
        }
        //Retorna os resultados em forma de matriz
        return $this->DB->parse_result($result_sql)[0]['value'];
    }
	
	/**
     * Adiciona uma variável de configuração no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($name, $value) {
                            
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['name'] = $name;
        $fields['value'] = $value;
        
        try {         
            $this->DB->insert('Config', $fields, false);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }
        return json_encode(array('error' => null));
    }

    /**
     * Atualiza a Config no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update($name, $value) {
        
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        if (!$name) {
            return json_encode(array('error' => 'Nome não especificado'));
        }
        
        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['value'] = $value;
        
        $where = "name = '" . mysqlx_real_escape_string($name) . "'";
        
        try {         
            $this->DB->update('Config', $fields, $where);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
        }
        
        return json_encode(array('error' => null));
    }
}