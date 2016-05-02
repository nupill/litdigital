<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleLocalizacao  {

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

	public function getPaises($id=NULL){
		if ($id) 
			$query = sprintf("SELECT nome FROM Paises WHERE id=%s", $id);
		else
			$query = "SELECT * FROM Paises ORDER BY id ASC";
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
	
	public function getEstado($id){
		$resposta = array();
		$query = sprintf("SELECT * FROM estados WHERE id=%s", $id);
      		
		try {
			$result_sql = $this->DB->query($query);
			$result = $this->DB->parse_result($result_sql);
			$resposta['sigla'] = $result[0]['sigla'];
			$query = sprintf("SELECT nome FROM Paises WHERE id=%s", $result[0]['pais_id']);
			$result_sql = $this->DB->query($query);
			$result = $this->DB->parse_result($result_sql);
			$resposta['pais'] = $result[0]['nome'];
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $resposta;
	}
	
	public function getEstadosPais ($pais=1,$id=NULL){
		
		if ($id)
			$query = sprintf("SELECT * FROM estados WHERE pais_id=%s AND id=%s ORDER BY sigla ASC",$pais,$id);
		else	
			$query = sprintf("SELECT * FROM estados WHERE pais_id=%s ORDER BY sigla ASC", $pais);
	
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
	
	public function getCidades($pais, $estado=NULL){
		
		if ($estado) {
			$query = sprintf("SELECT id,nome FROM cidades WHERE estado_id=%s ORDER BY nome ASC", $estado);
		} else 
			$query = sprintf("SELECT id,nome FROM cidades WHERE pais=%s ORDER BY nome ASC", $pais);
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
	
	public function getCidadeEstado($id){
		
		$resposta = Array();
	
		$query = sprintf("SELECT nome, estado_id, pais FROM cidades WHERE id=%s", $id);
		
		try {
			$result = $this->DB->query($query);
			$result = $this->DB->parse_result($result)[0];
			$resposta['cidade'] =  $result['nome'];
				
			if ($result['pais']==1) {
				$query = sprintf("SELECT nome, sigla  FROM estados WHERE id=%s", $result['estado_id']);
				$result = $this->DB->query($query);
				$result = $this->DB->parse_result($result)[0];
				$resposta['estado_sigla'] =  $result['sigla'];
				$resposta['estado_nome'] =  $result['nome'];
				
			} else {
				$query = sprintf("SELECT nome  FROM Paises WHERE id=%s", $result['pais']);
				$result = $this->DB->query($query);
				$result = $this->DB->parse_result($result)[0];
				$resposta['pais'] =  $result['nome'];
			}
			return $resposta;
		}
		catch (Exception $e) {
	//		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $this->DB->parse_result($result_sql);
	}
	
	public function getCidadeEstadoString($id){	
		$query = sprintf("SELECT nome, estado_id, pais FROM cidades WHERE id=%s", $id);
	
		try {
			$result = $this->DB->query($query);
			$result = $this->DB->parse_result($result)[0];
			$resposta =  $result['nome'];
		    
			if ($result['pais']==1) {
				$query = sprintf("SELECT nome, sigla  FROM estados WHERE id=%s", $result['estado_id']);
				$result = $this->DB->query($query);
				$result = $this->DB->parse_result($result)[0];
				$resposta = $resposta.", ".$result['sigla'];
			} else {
				$query = sprintf("SELECT nome  FROM Paises WHERE id=%s", $result['pais']);
				$result = $this->DB->query($query);
				$result = $this->DB->parse_result($result)[0];
				$resposta = $resposta.", ".$result['nome'];
			}
			return $resposta;
		}
		catch (Exception $e) {
			//		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $this->DB->parse_result($result_sql);
	}
	public function getNomeCidade($id){
		$query = sprintf("SELECT nome FROM cidades WHERE id=%s", $id);
	
		try {
			$result = $this->DB->query($query);
			$result = $this->DB->parse_result($result)[0];
			$resposta =  $result['nome'];
		}
		catch (Exception $e) {
			//		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $resposta;
	}
	public function getSiglaEstado($id){
		$query = sprintf("SELECT sigla FROM estados WHERE id=%s", $id);
	
		try {
			$result = $this->DB->query($query);
			$result = $this->DB->parse_result($result)[0];
			$resposta =  $result['sigla'];
		}
		catch (Exception $e) {
			//		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $resposta;
	}
	public function getNomePais($id){
		$query = sprintf("SELECT nome FROM Paises WHERE id=%s", $id);
	
		try {
			$result = $this->DB->query($query);
			$result = $this->DB->parse_result($result)[0];
			$resposta =  $result['nome'];
		}
		catch (Exception $e) {
			//		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $resposta;
	}
	public function getEstadoString($id){
		$query = sprintf("SELECT e.nome as estado, p.nome as pais FROM estados e left join Paises p ON (e.pais_id = p.id) WHERE e.id=%s", $id);
	
		try {
			$result = $this->DB->query($query);
			$result = $this->DB->parse_result($result)[0];
			$resposta =  $result['estado'].", ".$result['pais'];
		}
		catch (Exception $e) {
			//		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		return $resposta;
	}
	
}
