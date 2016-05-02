<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . "/../include/mysqli.php");
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleUsuarios extends DataTables {

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
	 * @param integer $id ID da usuário (opcional)
	 * @param array $fields Campos (colunas) da tabela a serem retornados
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @return array Resultado da pesquisa
	 */
	public function get($id = '', $fields = array(), $start = 0, $limit = 0) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(',', $fields);
		}
		//Prepara a consulta SQL
		$query = "SELECT $columns FROM Usuario";
		//Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
		if ($id) {
			$query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
		}
		//Verifica se os parâmetros para limite/paginação foram passados
		elseif (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
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
	 * Obtém os papéis de usuários
	 *
	 * @param integer $id ID do Papel
	 * @return array Resultado da pesquisa
	 */
	public function get_papeis($id = '', $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(',', $fields);
		}
		//Prepara a consulta SQL
		$query = "SELECT $columns
                  FROM Papel";

		//Adiciona a clausula WHERE para obter um registro específico
		if ($id) {
			$query.= sprintf("WHERE id='%s'
                              LIMIT 1",
			mysqlx_real_escape_string($id));
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
	 * Atualiza o Usuário no banco de dados
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	public function update($id, $nome, $email, $login, $senha, $repete_senha, $papel, $profissao = '', $url = '',
						   $anotacao = '', $personalizacao = '', $tipo_cores = '', $tipo_ordenacao = '') {
		
		if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
			return json_encode(array('error' => 'Acesso negado'));
		}
		
		//Validação
		if (!$id) {
			return json_encode(array('error' => 'ID não especificado'));
		}
		$invalid_fields = $this->validate_parameters($id, $nome, $email, $login, $senha, $repete_senha, $papel,
		$anotacao, $personalizacao, $tipo_cores, $tipo_ordenacao, true);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}
		
		//Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['nome'] = $nome;
		$fields['email'] = $email;
		$fields['login'] = $login;

		if ($senha != ''){
			$fields['senha'] = md5($senha);
		}

		$fields['Papel_id'] = $papel;
		$fields['profissao'] = $profissao;
		$fields['url'] = $url;
		$fields['anotacao'] = $anotacao;
		$fields['personalizacao'] = $personalizacao;
		$fields['tipo_cores'] = $tipo_cores;
		$fields['tipo_ordenacao'] = $tipo_ordenacao;

		$where = "id = '" . mysqlx_real_escape_string($id) . "'";

		try {
			$this->DB->update('Usuario', $fields, $where, false); //Deixar o último parâmetro como FALSE (permitir updates de somente alguns campos)
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
		}

		return json_encode(array('error' => null));
	}

	/**
	 * Adiciona o Usuário leitor pela interface pública
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	public function add_leitor($nome, $email, $login, $senha, $repete_senha, $profissao = '', $url = '',
							   $anotacao = '', $personalizacao = true, $tipo_cores = '', $tipo_ordenacao = '') {


		if (Auth::check()) {
			exit(json_encode(array('error' => 'Não é possível cadastro com usuário logado')));
		}

		$papel = PAPEL_LEITOR_ID;

		$invalid_fields = $this->validate_parameters(null, $nome, $email, $login, $senha, $repete_senha, $papel,
		$anotacao, $personalizacao, $tipo_cores, $tipo_ordenacao);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}

		$codigo_confirmacao = generate_random_string(10);
		
		//Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['nome'] = $nome;
		$fields['email'] = $email;
		$fields['login'] = $login;
		$fields['senha'] = md5($senha);
		$fields['Papel_id'] = $papel;
		$fields['profissao'] = $profissao;
		$fields['url'] = $url;
		$fields['anotacao'] = $anotacao;
		$fields['personalizacao'] = $personalizacao;
		$fields['tipo_cores'] = $tipo_cores;
		$fields['tipo_ordenacao'] = $tipo_ordenacao;
		$fields['confirmado'] = false;
		$fields['codigo_confirmacao'] = $codigo_confirmacao;

		try {
			$this->DB->insert('Usuario', $fields, false);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
		}
		
		$this->send_codigo_confirmacao($nome, $email, $codigo_confirmacao, $login, $senha);
		
		//Nenhum erro ocorreu. Retorna nulo.
		return json_encode(array('error' => null));
	}
	
	/**
	 * Envia o código de confirmação para o email o usuário
	 *
	 * @return boolean Sucesso/Falha
	 */
	private function send_codigo_confirmacao($nome, $email, $codigo_confirmacao, $login, $senha) {
		require_once(APPLICATION_PATH . "/include/PHPMailer/PHPMailerAutoload.php");
		global $config;
		
		$confirm_url = CONTAS_URI . 'confirmar/?codigo=' . $codigo_confirmacao;
		
		$subject = "Cadastro na LiteraturaDigital";
		$message = "Olá $nome!<br /><br />" .
				   'Seu cadastro na LiteraturaDigital está quase completo!<br />' .
				   'Clique no link abaixo para confirmar e acessar sua conta:<br />' .
				   '<a href="' . $confirm_url . '">' . $confirm_url . '</a><br /><br />' .
				   "Seus dados de acesso são:<br />Login: $login<br />Senha: $senha<br />URL: " . ROOT_URI;
		
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
		$mail->AddAddress($email);
		
		if (!$mail->Send()) {
			if (!mail($email, $subject, $message)) {
				Logger::log('Erro ao enviar email de cadastro de usuário para ' . $email, __FILE__);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Re-envia o código de confirmação para o email o usuário
	 *
	 * @return boolean Sucesso/Falha
	 */
	public function resend_codigo_confirmacao($id) {
		require_once(APPLICATION_PATH . "/include/PHPMailer/PHPMailerAutoload.php");
		global $config;
		
		$usuario = $this->get($id, array('nome', 'email', 'codigo_confirmacao', 'login'));
		$usuario = isset($usuario[0]) ? $usuario[0] : null;
		if (!$usuario) {
			return false;
		}

		$nome = $usuario['nome'];
		$email = $usuario['email'];
		$codigo_confirmacao = $usuario['codigo_confirmacao'];
		$login = $usuario['login'];
		
		$confirm_url = CONTAS_URI . 'confirmar/?codigo=' . $codigo_confirmacao;
		
		$subject = "Cadastro na LiteraturaDigital";
		$message = "Olá $nome!<br /><br />" .
				   'Para poder acessar sua conta você deve confirmar seu cadastro acessando o endereço abaixo:<br />' .
				   '<a href="' . $confirm_url . '">' . $confirm_url . '</a><br /><br />' .
				   "Seus dados de acesso são:<br />Login: $login<br />URL: " . ROOT_URI;
		
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
		$mail->AddAddress($email);
		
		if (!$mail->Send()) {
			if (!mail($email, $subject, $message)) {
				Logger::log('Erro ao enviar email de cadastro de usuário para ' . $email, __FILE__);
				return false;
			}
		}
		return true;
	}
	

	/**
	 * Atualiza o Usuário leitor pela interface pública
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	public function update_leitor($nome, $email, $senha, $repete_senha, $profissao = '', $url = '', $anotacao = '',
								  $personalizacao = true, $tipo_cores = '', $tipo_ordenacao = '') {

		if (!Auth::check()) {
			return json_encode(array('error' => 'Acesso negado'));
		}

		$id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
		$login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
		$papel = isset($_SESSION['papel']) ? $_SESSION['papel'] : '';

		//Validação
		if (!$id) {
			return json_encode(array('error' => 'ID não especificado'));
		}
		$invalid_fields = $this->validate_parameters($id, $nome, $email, $login, $senha, $repete_senha, $papel,
													 $anotacao, $personalizacao, $tipo_cores, $tipo_ordenacao, true);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}

		//Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['nome'] = $nome;
		$fields['email'] = $email;
		$fields['login'] = $login;

		if ($senha != '') {
			$fields['senha'] = md5($senha);
		}

		$fields['Papel_id'] = $papel;
		$fields['profissao'] = $profissao;
		$fields['url'] = $url;
		$fields['anotacao'] = $anotacao;
		$fields['personalizacao'] = $personalizacao;
		$fields['tipo_cores'] = $tipo_cores;
		$fields['tipo_ordenacao'] = $tipo_ordenacao;

		$where = "id = '" . mysqlx_real_escape_string($id) . "'";

		try {
			$this->DB->update('Usuario', $fields, $where, false); //Deixar o último parâmetro como FALSE (permitir updates de somente alguns campos)
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
		}

		return json_encode(array('error' => null));
	}
	
	/**
	 * Confirma o cadastro do usuário
	 *
	 * @return boolean Resultado da redefinição
	 */
	public function confirm_account($code) {
		if (!$code) {
			return false;
		}
		
		$DB = DB::getInstance();
		$DB->connect();
		
		$query = sprintf("SELECT COUNT(*) AS total
						FROM Usuario
						WHERE codigo_confirmacao='%s'",
						mysqlx_real_escape_string($code));
		try {
			$result_sql = $DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		
		if (mysqlx_result($result_sql, 0) == 0) {
			return false;
		}
		
		$query = sprintf("UPDATE Usuario SET
						confirmado = 1,
						codigo_confirmacao = NULL
						WHERE codigo_confirmacao = '%s'",
						mysqlx_real_escape_string($code));
		try {
			$result_sql = $DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		return true;
	}
	
	/**
	 * Primeiro passo para redefinição de senha
	 *
	 * @return string Resultado da redefinição no formato JSON
	 */
	public function password_redefinition_step_1($login) {
		global $config;
		
		$login = isset($_POST['login']) ? $_POST['login'] : '';
		if (!$login) {
			return json_encode(array('error' => 'Informe o usuário/e-mail'));
		}
		
		$DB = DB::getInstance();
		$DB->connect();
		
		$query = sprintf("SELECT email
						FROM Usuario
						WHERE login='%s'
						OR email='%s'
						LIMIT 1",
						mysqlx_real_escape_string($login),
						mysqlx_real_escape_string($login));
		try {
			$result_sql = $DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro no banco de dados'));
		}
		
		$result = $DB->parse_result($result_sql);
		$email = isset($result[0]['email']) ? $result[0]['email'] : '';
		
		if (!$email) {
			return json_encode(array('error' => 'Usuário/email não existe'));
		}
		
		$code = generate_random_string(10);
		
		require_once(APPLICATION_PATH . "/include/PHPMailer/PHPMailerAutoload.php");
		
		$subject = "Redefinição de senha";
		$message = "Para redefinir sua senha, utilize o código abaixo:<br />$code";
		
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
		$mail->AddAddress($email);
		
		if (!$mail->Send()) {
			if (!mail($email, $subject, $message)) {
				return json_encode(array('error' => 'Erro ao enviar o email'));
			}
		}
		
		$query = sprintf("UPDATE Usuario SET
						codigo_redefinicao = '%s',
						data_codigo_redefinicao = NOW()
						WHERE email = '%s'",
						mysqlx_real_escape_string($code),
						mysqlx_real_escape_string($email));
		
		try {
			$result_sql = $DB->query($query);
			
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro no banco de dados'));
		}
		return json_encode(array('error' => null));
	
	}
	
	/**
	 * Segundo passo para redefinição de senha
	 *
	 * @return string Resultado da redefinição no formato JSON
	 */
	public function password_redefinition_step_2($code, $password, $password_check) {
		
		if ($password && strlen($password) < 5) {
			exit(json_encode(array('error' => 'A senha deve conter ao menos 5 caracteres')));
		}
		
		if ($password != $password_check) {
			exit(json_encode(array('error' => 'Senhas não conferem')));
		}
		
		$DB = DB::getInstance();
		$DB->connect();
		
		$query = sprintf("SELECT COUNT(*) as total
						FROM Usuario
						WHERE codigo_redefinicao='%s'
						AND DATE_ADD(data_codigo_redefinicao, INTERVAL 1 DAY) >= NOW()",
						mysqlx_real_escape_string($code));
		try {
			$result_sql = $DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro no banco de dados'));
		}
		
		if (mysqlx_result($result_sql, 0) == 0) {
			return json_encode(array('error' => 'Código inválido ou expirou'));
		}
		
		$query = sprintf("UPDATE Usuario SET
						senha = MD5('%s'),
						confirmado = 1,
						codigo_redefinicao = NULL,
						data_codigo_redefinicao = NULL
						WHERE codigo_redefinicao = '%s'",
						mysqlx_real_escape_string($password),
				mysqlx_real_escape_string($code));
		try {
			$result_sql = $DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro no banco de dados'));
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
		$query = sprintf("DELETE FROM Usuario
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
	 * Verifica a disponibilidade de um endereço de email
	 *
	 * @param string $email Endereço de email
	 * @return boolean JSON availabile=TRUE caso não existam registros com o mesmo email, senão FALSE
	 *
	 */
	public function email_available($email) {
		$query = sprintf("SELECT COUNT(*) as total
    	                  FROM Usuario
    	                  WHERE email='%s'",
						  mysqlx_real_escape_string($email));
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('available' => false));
		}

		if (mysqlx_result($result_sql, 0, 'total') > 0) {
			return json_encode(array('available' => false));
		}
		return json_encode(array('available' => true));
	}
	 
	/**
	 * Verifica a disponibilidade de um login (nome de usuário)
	 *
	 * @param string $login Username
	 * @return string JSON availabile=TRUE caso não existam registros com o mesmo login, senão FALSE
	 *
	 */
	public function login_available($login) {
		$query = sprintf("SELECT COUNT(*) as total
                          FROM Usuario
                          WHERE login='%s'",
						  mysqlx_real_escape_string($login));
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('available' => false));
		}

		if (mysqlx_result($result_sql, 0, 'total') > 0) {
			return json_encode(array('available' => false));
		}
		return json_encode(array('available' => true));
	}

	/**
	 * Cria a consulta SQL para pesquisa dos dados da tabela
	 *
	 * @override (DataTables):
	 * @param array $aParams Parâmetros enviados na requisição pelos dados da tabela
	 * @return string Consulta SQL
	 */
	protected function fnBuildQuery($aParams) {
		/* Query statements */
		$aClauses = $this->fnBuildQueryClauses($aParams);
		/* Parse columns to JOIN */
		$aColumns = explode(',', $aParams['sColumns']);
		/* Query */
		$sQuery = "SELECT SQL_CALC_FOUND_ROWS
                     u.id,
                     u.nome,
                     u.email,
                     u.login,
                     p.nome as papel
                   FROM Usuario u
                     JOIN Papel p
                       ON u.Papel_id = p.id
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
					$aColumn = 'u.nome';
				}
				elseif ($aColumn == 'papel') {
					$aColumn = 'p.nome';
				}
				$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
			}
			$sWhere = substr($sWhere, 0, -4);
		}
		return $sWhere;
	}

	/**
	 * Valida os campos (parâmetros) do Autor
	 *
	 * @return array Campos que não passaram no teste de validação
	 */
	private function validate_parameters($id = '', $nome, $email, $login, $senha, $repete_senha, $papel, $anotacao = '',
										 $personalizacao = true, $tipo_cores = '', $tipo_ordenacao = '', $update = false) {
		$invalid_fields = array();

		//        $data = array();
		//        if ($id) {
		//            $data = $this->get($id, array('login', 'email'));
		//            if (!$data) {
		//            	$data = $data[0];
		//            }
		//        }

		if (!$nome) {
			$invalid_fields['nome'] = 'O nome não pode ser vazio';
		}
		if (!$email) {
			$invalid_fields['email'] = 'O email não pode ser vazio';
		}
		elseif (!validate_email($email)) {
			$invalid_fields['email'] = 'Email inválido';
		}
		else {
			$result = json_decode($this->email_available($email), true);
			if (!$result['available'] && $update == 0){
				$invalid_fields['email'] = 'Email já cadastrado';
			}
		}
		if (!$login) {
			$invalid_fields['login_cadastro'] = 'O login não pode ser vazio';
		}
		else {
			$result = json_decode($this->login_available($login), true);
			if (!$result['available'] && !$update) {
				$invalid_fields['login_cadastro'] = 'Login já cadastrado';
			}
		}
		if ($papel != PAPEL_ADMINISTRADOR_ID && $papel != PAPEL_CADASTRADOR_ID && $papel != PAPEL_LEITOR_ID) {
			$invalid_fields['papel'] = 'Especifique um papel';
		}
		if (!$senha && !$update) {
			$invalid_fields['senha_cadastro'] = 'Senha não definida';
		}
		elseif ($senha && strlen($senha) < 5) {
			$invalid_fields['senha_cadastro'] = 'A senha deve conter ao menos 5 caracteres';
		}
		elseif ($senha && !$repete_senha) {
			$invalid_fields['repete_senha'] = 'Confirme a senha';
		}
		elseif ($senha && $senha != $repete_senha) {
			$invalid_fields['repete_senha'] = 'Senha não confere';
		}

	if ($anotacao && !is_bool($anotacao)) {
			$invalid_fields['anotacao'] = 'Anotação deve ser um booleano';
		}
		if ($personalizacao && !is_bool($personalizacao)) {
			$invalid_fields['personalizacao'] = 'Personalizacao deve ser um booleano';
		}
		if ($tipo_cores && !is_integer($tipo_cores)) {
			$invalid_fields['tipo_cores'] = 'O tipo de cores deve ser um número inteiro (chave)';
		}
		if ($tipo_ordenacao && !is_integer($tipo_ordenacao)) {
			$invalid_fields['tipo_ordenacao'] = 'O tipo de ordenação deve ser um número inteiro (chave)';
		}
		return $invalid_fields;
	}

	public function atualiza_adaptabilidade($id, $id_autores, $id_genero, $id_documento) {
		
		if (! $id || ! is_numeric ( $id )) {
			return json_encode ( array ('error' => 'ID não especificado ou inválido' ) );
		}
		
		try {
			
			// Verifica se é Obra Literária, senão para adaptabilidade
			$query = sprintf("SELECT id FROM ObraLiteraria WHERE Documento_id = '%s'",mysqlx_real_escape_string($id_documento));
			$result = $this->DB->query ( $query );
			$row = mysqli_fetch_assoc($result);

			if (!$row) {	
				return false;
			}
			
			// Atualiza adaptação apenas para obra literária
			
			$query = sprintf ( " SELECT * FROM UsuarioObraLiterariaAcessadas
                                     WHERE Usuario_id = '%s'
                                      AND ObraLiteraria_id = '%s'", mysqlx_real_escape_string ( $id ), mysqlx_real_escape_string ( $row['id'] ) );
			
			$result = $this->DB->query ( $query );
			$acessouObra = mysqli_num_rows ( $result );
				
			if ($acessouObra == 0) {
		        
				// Primeiro acesso, atualiza perfil do usuário
				$fields = array();
				$fields['Usuario_id'] =  mysqlx_real_escape_string ( $id );
				$fields['ObraLiteraria_id'] = mysqlx_real_escape_string ( $row['id'] );

				$this->DB->insert('UsuarioObraLiterariaAcessadas', $fields, false);
				
				// identifica primeiro autor da obra
				$query = sprintf ( "SELECT Autor_id FROM AutorDocumento WHERE Documento_id = %s", mysqlx_real_escape_string ( $id_documento )); 
				$result = $this->DB->query ( $query );
				
				$AutorsId = mysqli_fetch_assoc($result);
				$autorId = $AutorsId['Autor_id'];
				
				// Verifica se o autor já foi acessado
				$query = sprintf ( "SELECT Escore FROM AdUsuarioAutor WHERE Autor_id = %s AND Usuario_id = %s", 
						mysqlx_real_escape_string ( $autorId ), mysqlx_real_escape_string($id) );
				$result = $this->DB->query ( $query );
				
				if (!mysqli_fetch_assoc($result)) {
					// Não existe Autor, deve ser incluído
					$fields1 = array();
					$fields1['Usuario_id'] =  mysqlx_real_escape_string ( $id );
					$fields1['Autor_id'] = mysqlx_real_escape_string ( $autorId );
					$fields1['Escore'] = 1;
					$this->DB->insert('AdUsuarioAutor', $fields1, false);
				}
				else {
					// Existe autor, deve ser adicionado o escore.
					$result = $this->DB->query ( $query );
					$escore = (int) mysqli_fetch_assoc($result);
					$fields1 = array();
					$fields1['Escore'] = $escore+1;
					//Clausula WHERE para atualizar apenas o registro especificado
					$where = sprintf ( "Usuario_id = %s AND Autor_id = %s", mysqlx_real_escape_string($id), mysqlx_real_escape_string($autorId));
					$this->DB->update('AdUsuarioAutor', $fields1, $where);
				}
				
				// identifica primeiro gênero da obra
				$query = sprintf ( "SELECT Genero_id FROM DocumentoGenero WHERE Documento_id = %s", mysqlx_real_escape_string ( $id_documento ));
				$result = $this->DB->query ( $query );
				
				$generosId = mysqli_fetch_assoc($result);
				$generoId = $generosId['Genero_id'];
				
				// Verifica se o gênero já foi acessado
				$query = sprintf ( "SELECT Escore FROM AdUsuarioGenero WHERE Genero_id = %s AND Usuario_id = %s",
						mysqlx_real_escape_string ( $generoId ), mysqlx_real_escape_string($id) );
				$result = $this->DB->query ( $query );
				
				
				if (!mysqli_fetch_assoc($result)) {
					// Não existe Gênero, deve ser incluído
					$fields1 = array();
					$fields1['Usuario_id'] =  mysqlx_real_escape_string ( $id );
					$fields1['Genero_id'] = mysqlx_real_escape_string ( $generoId );
					$fields1['Escore'] = 1;
					$this->DB->insert('AdUsuarioGenero', $fields1, false);
				}
				else {
					// Existe gênero, deve ser adicionado o escore.
					$fields1 = array();
					$row = mysqli_fetch_assoc($result);
					$escore = $row['Escore'];
					$fields1['Escore'] = $escore+1;
						
					//Clausula WHERE para atualizar apenas o registro especificado
					$where = sprintf ( "Usuario_id = %s AND Genero_id = %s", mysqlx_real_escape_string($id), mysqlx_real_escape_string($generoId));
					$this->DB->update('AdUsuarioGenero', $fields1, $where);
				}
				$this->normaliza(mysqlx_real_escape_string($id));
				return true;
			}	
		} catch ( Exception $e ) {
			// Logger::log ( $e->getMessage ());
			echo 'Erro na atualização do perfil';
			return false;
		}
	
	}

	public function get_autores_preferidos($id) {
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}
		$columns = '*';
		//Prepara a consulta SQL
		$query = sprintf("SELECT $columns
		                  FROM Autor a
		                  INNER JOIN
		                   (SELECT * FROM AdUsuarioAutor WHERE Usuario_id = '%s') AS ad
		                   ON (a.id = ad.Autor_id)",
		mysqlx_real_escape_string($id)) ;


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

	public function get_generos_preferidos($id) {
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}
		$columns = '*';
		//Prepara a consulta SQL
		$query = sprintf("SELECT g.id, g.nome as nome, Escore, td.nome as tipo_documento
                          FROM Genero g
                          INNER JOIN
                           (SELECT * FROM AdUsuarioGenero WHERE Usuario_id = '%s') AS ad 
                           ON (g.id = ad.Genero_id)
                          INNER JOIN TipoDocumento td
                           ON (g.TipoDocumento_id = td.id)",
		mysqlx_real_escape_string($id)) ;


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
	
	public function normaliza($idUsuario) {
		
		
		$query = "SELECT SUM(Escore) as numTotalAcessos FROM AdUsuarioGenero WHERE Usuario_id = ".mysqlx_real_escape_string ( $idUsuario );
		try {
			// Normaliza Gênero
			$result = $this->DB->query ( $query );
			$row = mysqli_fetch_assoc ( $result );
			$totalGenero = (int) $row['numTotalAcessos'];

			$query = sprintf('UPDATE AdUsuarioGenero SET normalizado = (Escore/%u) WHERE Usuario_id =%s', 
					$totalGenero , mysqlx_real_escape_string ($idUsuario));		
			$result = $this->DB->query ( $query );
		    
			// Normaliza Autor
			$query = "SELECT SUM(Escore) as numTotalAcessos FROM AdUsuarioAutor WHERE Usuario_id = ".mysqlx_real_escape_string ( $idUsuario );
			$result = $this->DB->query ( $query );
			$row = mysqli_fetch_assoc ( $result );
			$totalAutor = (int) $row['numTotalAcessos'];
			
			$query = sprintf('UPDATE AdUsuarioAutor SET normalizado = (Escore/%u) WHERE Usuario_id =%s', 
					$totalAutor , mysqlx_real_escape_string ($idUsuario));		
			$result = $this->DB->query ( $query );
			return true;
				
		} catch (Exception $e) {
			// Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			echo 'Erro na normalização';
			return false;
		}
	}

	public function remove_autor_preferido($id, $autor) {
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}
		//Prepara a consulta SQL
		$query = sprintf("DELETE FROM AdUsuarioAutor
				         WHERE Usuario_id = '%s'
				          AND Autor_id = '%s'",
		mysqlx_real_escape_string($id),
		mysqlx_real_escape_string($autor));
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return json_encode(array('error' => null));
	}

	public function remove_genero_preferido($id, $genero) {
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}
		//Prepara a consulta SQL
		$query = sprintf("DELETE FROM AdUsuarioGenero
					      WHERE Usuario_id = '%s'
					       AND Genero_id = '%s'",
		mysqlx_real_escape_string($id),
		mysqlx_real_escape_string($genero));
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return false;
	}
	
	public function remove_obra_visualizada($id, $obra) {

		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}
		
		$query = sprintf("DELETE FROM  UsuarioObraLiterariaAcessadas
					      WHERE Usuario_id = '%s'
					       AND ObraLiteraria_id = '%s'",mysqlx_real_escape_string($id),mysqlx_real_escape_string($obra));
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		
		$query = sprintf("SELECT Documento_id FROM ObraLiteraria
								WHERE id = '%s'", mysqlx_real_escape_string($obra));
		$idDocumento = mysqli_fetch_assoc($this->DB->query($query));
		$idDocumento = $idDocumento['Documento_id'];
		
		if ($idDocumento) {
				$query = sprintf("SELECT Genero_id FROM DocumentoGenero
									WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($idDocumento));
				$idGenero = mysqli_fetch_assoc($this->DB->query($query));
				
				$idGenero = $idGenero['Genero_id'];
				$query = sprintf("SELECT Autor_id FROM AutorDocumento
								WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($idDocumento));
				$idAutor = mysqli_fetch_assoc($this->DB->query($query));
				$idAutor = $idAutor['Autor_id'];
				
				if($idAutor && $idGenero){
					
					$query_total_acessos = sprintf("SELECT SUM(AdUsuarioGenero.Escore) as numTotalAcessos
					FROM Usuario INNER JOIN AdUsuarioGenero ON Usuario.id = AdUsuarioGenero.Usuario_id
					WHERE Usuario.id = %s AND AdUsuarioGenero.Genero_id = %s", mysqlx_real_escape_string($id), mysqlx_real_escape_string ( $idGenero )) ;

					$result = $this->DB->query ( $query_total_acessos );
					
					if($result){
						$totalacessosgenero = mysqli_fetch_assoc($result);
						if($totalacessosgenero['numTotalAcessos'] == 1){
							//Prepara a consulta SQL
							$query = "DELETE FROM AdUsuarioGenero
											  WHERE Usuario_id = ".mysqlx_real_escape_string($id)."
											   AND Genero_id = ".mysqlx_real_escape_string($idGenero);
							try {
								$result_sql = $this->DB->query($query);
							}
							catch (Exception $e) {
								Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
								return false;
							}
						}
						else{
							$query = "UPDATE AdUsuarioGenero SET Escore =
							Escore-1, normalizado = (Escore) / ".mysqlx_real_escape_string ( $totalacessosgenero['numTotalAcessos']-1 )."
							WHERE Usuario_id = ".mysqlx_real_escape_string ( $id )." AND Genero_id = ".mysqlx_real_escape_string ( $idGenero );
							try {
								$result_sql = $this->DB->query($query);
							}
							catch (Exception $e) {
								Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
								return false;
							}
						}	
					}
					$query_total_acessos= "SELECT SUM(AdUsuarioAutor.Escore) as numTotalAcessos  
													FROM Usuario INNER JOIN AdUsuarioAutor ON Usuario.id = AdUsuarioAutor.Usuario_id 
													WHERE Usuario.id = ".mysqlx_real_escape_string ( $id )." AND AdUsuarioAutor.Autor_id =".mysqlx_real_escape_string ( $idAutor );
					$result = $this->DB->query ( $query_total_acessos );
					if($result){
						$totalacessosautor = mysqli_fetch_assoc($result);
						if($totalacessosautor['numTotalAcessos'] == 1){
							$query = "DELETE FROM AdUsuarioAutor
											 WHERE Usuario_id = ".mysqlx_real_escape_string($id)."
											  AND Autor_id = ".mysqlx_real_escape_string($idAutor);
							try {
								$result_sql = $this->DB->query($query);
							}
							catch (Exception $e) {
								Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
								return false;
							}
						}
						else{
							$query = "UPDATE AdUsuarioAutor SET Escore =
									Escore-1, normalizado = (Escore) / ".mysqlx_real_escape_string ( $totalacessosautor['numTotalAcessos']-1 )."
									WHERE Usuario_id = ".mysqlx_real_escape_string ( $id )."
									AND Autor_id =".mysqlx_real_escape_string ( $idAutor );
							try {
								$result_sql = $this->DB->query($query);
							}
							catch (Exception $e) {
								Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
								return false;
							}
						}	
					}
				}
				return false;
			}
		
		return false;
	}
	
	public function tem_perfil($id) {
		if (! $id || ! is_numeric ( $id )) {
			return json_encode ( array ('error' => 'ID não especificado ou inválido' ) );
		}
		$retorno = false;
		$query = sprintf ( "SELECT * FROM UsuarioObraLiterariaAcessadas
				WHERE Usuario_id = '%s'", mysqlx_real_escape_string ( $id ) );
		try {
			$result_sql = $this->DB->query ( $query );
			// Usuário tem que ter pelo menos 3 obras
			if (mysqli_num_rows ( $result_sql ) > 3) {
				$retorno = true;
			}
		
		} catch ( Exception $e ) {
			Logger::log ( $e->getMessage () . " (Query: $query)", __FILE__ );
			return false;
		}
		return $retorno;
	}
	
	public function get_obras_visualizadas($id) {
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}
		$columns = '*';
		//Prepara a consulta SQL
	/*	$query = sprintf("SELECT o.id as idObra, d.titulo, a.nome_completo, g.nome as nomeGenero
                          FROM Documento d INNER JOIN AutorDocumento ad ON (d.id = ad.Documento_id),Genero g,Autor a, ObraLiteraria o
                          INNER JOIN
                            UsuarioObraLiterariaAcessadas uo
                           ON (o.id = uo.ObraLiteraria_id)
							WHERE uo.Usuario_id = '%s' AND d.id = o.Documento_id AND g.id = d.Genero_id AND a.id = ad.Autor_id",
		mysqlx_real_escape_string($id)) ; */
		
		$query = sprintf("SELECT o.id AS idObra, dc.titulo, dc.autores_nome_usual as nome_completo, dc.nome_genero as nomeGenero FROM 
DocumentoConsulta dc INNER JOIN ObraLiteraria o ON (o.Documento_id = dc.id) 
INNER JOIN UsuarioObraLiterariaAcessadas uo ON (o.id = uo.ObraLiteraria_id) 
WHERE uo.Usuario_id = %s ", 
				mysqlx_real_escape_string($id)) ;


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

}
