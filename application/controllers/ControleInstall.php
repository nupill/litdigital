<?php
require_once(dirname(__FILE__) . '/../config/constants.php');
require_once(dirname(__FILE__) . '/../include/DB.php');

class ControleInstall {
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
	
	public function query($query) {
		$this->DB->query($query);
	}

	public function set_db($filename) {
		$this->DB = DB::getInstance();
		$this->DB->import_db($filename);
		// fazer um import do dump em BDDUMP
	}
	
}