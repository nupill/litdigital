<?php
require_once(dirname(__FILE__) . '/../config/general.php');
require_once(dirname(__FILE__) . '/DB.php');
require_once(dirname(__FILE__) . '/../controllers/ControleUsuarios.php');

class Auth {

	public static function login($username, $password) {
		global $config;

		$DB = DB::getInstance();
		$DB->connect();
		$query = sprintf("SELECT id, nome, Papel_id AS papel, personalizacao, confirmado, email 
        				  FROM Usuario
        				  WHERE login='%s'
        				   AND senha='%s'",
						  mysqlx_real_escape_string($username),
						  mysqlx_real_escape_string($password));
                
		try {
			$result = $DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
		}

		if (!$result || mysqli_num_rows($result) == 0) {
			return json_encode(array('error' => 'Dados não conferem'));
		}
		elseif (!mysqlx_result($result, 0, 'confirmado')) {
			$controller = ControleUsuarios::getInstance();
			$controller->resend_codigo_confirmacao(mysqlx_result($result, 0, 'id'));
			return json_encode(array('error' => 'Cadastro não confirmado. Verifique seu email.'));
		}
		else {
			session_start();
			$_SESSION['logged_in'] = true;
			$_SESSION['id'] = mysqlx_result($result, 0, 'id');
			$_SESSION['nome'] = mysqlx_result($result, 0, 'nome');
			$_SESSION['primeiro_nome'] = explode(' ', $_SESSION['nome']);
			$_SESSION['primeiro_nome'] = $_SESSION['primeiro_nome'][0];
			$_SESSION['papel'] = mysqlx_result($result, 0, 'papel');
			$_SESSION['login'] = mysqlx_real_escape_string($username);
			$_SESSION['personalizacao'] = mysqlx_result($result, 0, 'personalizacao');
			$_SESSION['email'] = mysqlx_result($result, 0, 'email');
			return json_encode(array('error' => null, 'name' => $_SESSION['nome'], 'papel' => $_SESSION['papel']));
		}
	}

	public static function logout() {
		if (self::check()) {
			session_unset();
			session_destroy();
		}
		return json_encode(array('error' => null));
	}

	public static function check($papel = array()) {
		if (!isset($_SESSION)) {
			session_start();
		}
		if (isset($_SESSION['logged_in']) && !$papel) {
			return true;
		}
		elseif (isset($_SESSION['logged_in'])) {
			if (in_array($_SESSION['papel'], $papel)) {
				return true;
			}
		}
		return false;
	}

	public static function checa_personalizacao() {
		$DB = DB::getInstance();
		$DB->connect();
		$query = sprintf("SELECT personalizacao
        				  FROM Usuario
        				  WHERE id='%s'",
						  mysqlx_real_escape_string($_SESSION['id']));

		try {
			$result = $DB->query($query);
			return (mysqlx_result($result, 0, 'personalizacao'));
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
		}
	}

}
