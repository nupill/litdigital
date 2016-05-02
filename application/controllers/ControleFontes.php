<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleFontes extends DataTables {
	
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
	
	private function notify($operacao,$descricao) {
		require_once(APPLICATION_PATH . "/include/PHPMailer/PHPMailerAutoload.php");
		global $config;
		
		if (isset($config['follow_email'])) {

			$subject = 'Cadastramento de Fontes: '.$operacao.' de fonte';
			$message = 'A seguinte fonte foi alterada:<br /><br />' .
				'<b>Fonte:</b> ' . $descricao . '<br />' ;
		 
			$mail = new PHPMailer();
			$mail->IsSMTP(true);
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = "ssl";
			$mail->Host = $config['smtp_host'];
			$mail->Port = $config['smtp_port'];
			$mail->Username = $config['smtp_user'];
			$mail->Password = $config['smtp_password'];
			$mail->CharSet = 'utf-8';
		 
			$mail->SetFrom($config['smtp_email'], 'Literatura Digital');
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			$mail->AddAddress($config['follow_email']);
		 
			if (!$mail->Send()) {
				if (!mail($config['follow_email'], $subject, $message)) {
					Logger::log('Erro ao enviar email de notificação de fonte ' . $id, __FILE__);
					return json_encode(array('error' => 'Erro ao reportar alteração de fonte'));
				}
			}
		}
	}
	
	
	/**
     * Pesquisa e retorna os registros de autores do banco de dados
     * 
     * @param integer $id ID da fonte (opcional)
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @param integer $descricao Parte da descrição da fonte. Utilizado nos campos de auto completar (opcional)
     * @return array Resultado da pesquisa
     */
    public function get($id = '', $fields = array(), $start = 0, $limit = 0, $descricao = '') {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }
        //Prepara a consulta SQL
        $query = "SELECT $columns FROM Fonte";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        }
        //Senão, caso a descrição tenha sido especificada, aplica o filtro
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
     * Adiciona a Fonte no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($descricao) {
                            
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        $invalid_fields = $this->validate_parameters($descricao);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['descricao'] = $descricao;
        
        try {         
            $this->DB->insert('Fonte', $fields, false);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }
        
        //Nenhum erro ocorreu. Retorna nulo.
        $this->notify('criação',$descricao);
        return json_encode(array('error' => null));
    }

    /**
     * Atualiza a Fonte no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update($id, $descricao) {
        
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação
        if (!$id) {
            return json_encode(array('error' => 'ID não especificado'));
        }
        $invalid_fields = $this->validate_parameters($descricao);
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }
        
        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['descricao'] = $descricao;
        
        $where = "id = '" . mysqlx_real_escape_string($id) . "'";
        
        try {         
            $this->DB->update('Fonte', $fields, $where);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
        }
        
        $this->notify('modificação',$descricao);

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
        $queryAviso = sprintf("Select descricao FROM Fonte
                          WHERE id IN ('%s')",
                          $ids_str);
        
        $query = sprintf("DELETE FROM Fonte
                          WHERE id IN ('%s')",
                          $ids_str);
        try {
        	$result = $this->DB->query($queryAviso);
        	while ($row = mysqli_fetch_array($result)) {
        		$this->notify("remoção",$row['descricao']);
        	}
            $this->DB->query($query);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return json_encode(array('error' => 'Esta fonte não pode ser excluída pois está sendo utilizada'));
        }
        
        //Nenhum erro ocorreu. Retorna nulo.
        
        return json_encode(array('error' => null));        
    }
   
    /**
     * Valida os campos (parâmetros) do Autor
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($descricao) {
        $invalid_fields = array();
        if (!$descricao) {
            $invalid_fields['descricao'] = 'O descrição não pode ser vazia';
        }
        return $invalid_fields;
    }
}