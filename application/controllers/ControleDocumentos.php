<?php
require_once(dirname(__FILE__) . '/../config/constants.php');
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/UploadFile.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/../include/mysqli.php');

//set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());

//require_once(dirname(__FILE__) . '/ControleFontes.php');

class ControleDocumentos extends DataTables {

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
	 * Pesquisa e retorna os registros de documentos do banco de dados. Quando o ID é especificado,
	 * as informações específicas da obra também são retornadas, caso contrário, apenas os dados dos
	 * documentos são obtidos.
	 *
	 * @param integer $id ID do documento (opcional)
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
		$query = "SELECT $columns FROM Documento";
		//Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
		if ($id) {
			//Consulta o nome da tabela filha
			$query_tipo = sprintf("SELECT
                                     tabela
                                   FROM TipoDocumento
                                     JOIN Documento
                                       ON (TipoDocumento.id = Documento.TipoDocumento_id)
                                   WHERE Documento.id='%s'
                                   LIMIT 1",
								  mysqlx_real_escape_string($id));
			try {
				$result_sql = $this->DB->query($query_tipo);
				if (mysqli_num_rows($result_sql) > 0) {
					$table = mysqlx_result($result_sql, 0, 'tabela');
					//Adiciona o comando JOIN para obter também os dados específicos da obra (tabela filha)
					$query.= " LEFT JOIN $table ON (Documento.id = $table.Documento_id)";
				}
				//Adiciona a clausula WHERE para obter um registro específico
				$query.= sprintf(" WHERE Documento.id='%s' LIMIT 1", mysqlx_real_escape_string($id));

			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query_tipo)", __FILE__);
				return false;
			}
		}
		//Verifica se os parâmetros para limite/paginação foram passados
		elseif (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			//Limita a quantidade de resultados a partir dos valores especificados
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
	 * Obtém os autores de um documento
	 *
	 * @param integer $id ID do Documento
	 * @param array $fields Campos/colunas a serem obtidos
	 * @return array Resultado da pesquisa
	 */
	public function get_autores($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', t1.', $fields);
		}
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
                          FROM Autor t1
                            JOIN AutorDocumento t2
                              ON (t1.id = t2.Autor_id)
                          WHERE t2.Documento_id='%s'", 
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
	 * Obtém as editoras de um documento
	 *
	 * @param integer $id ID do Documento
	 * @param array $fields Campos/colunas a serem obtidos
	 * @return array Resultado da pesquisa
	 */
	public function get_editoras($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', t1.', $fields);
		}
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
                          FROM Editora t1
                            JOIN DocumentoEditora t2
                              ON (t1.id = t2.Editora_id)
                          WHERE t2.Documento_id='%s'", 
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
	
	
	public function get_acervo($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', t1.', $fields);
		}
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
				FROM Acervo t1
				JOIN Documento t2
				ON (t1.id = t2.Acervo_id)
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
		$acervo = $this->DB->parse_result($result_sql);
		if ($acervo)
			$acervo = $acervo[0];
		return $acervo;
	}
	

	/**
	 * Obtém as fontes literárias de um documento
	 *
	 * @param integer $id ID do Documento
	 * @param array $fields Campos/colunas a serem obtidos
	 * @return array Resultado da pesquisa
	 */
	public function get_fontes($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', t1.', $fields);
		}
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
                          FROM Fonte t1
                            JOIN DocumentoFonte t2
                              ON (t1.id = t2.Fonte_id)
                          WHERE t2.Documento_id='%s'", 
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
	 * Obtém os arquivos (mídias) de um documento
	 *
	 * @param integer $id ID do Documento
	 * @param array $fields Campos/colunas a serem obtidos
	 * @param integer $id ID da mídia
	 * @return array Resultado da pesquisa
	 */
	public function get_midias($id = '', $fields = array(), $id_midia = '') {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', ', $fields);
		}

		//Prepara a consulta SQL
		if ($id) {
			$query = sprintf("SELECT $columns
                              FROM Midia
                              WHERE Documento_id='%s'
                              ORDER BY titulo", 
							 mysqlx_real_escape_string($id));
		}
		elseif ($id_midia) {
			$query = sprintf("SELECT $columns
                              FROM Midia
                              WHERE id='%s'
                              ORDER BY titulo", 
							 mysqlx_real_escape_string($id_midia));
		}
		else {
			$query = sprintf("SELECT $columns
                              FROM Midia
                              ORDER BY titulo");
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
	 * Obtém os arquivos (mídias) mais acessados
	 *
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @return array Resultado da pesquisa
	 */
	public function get_midias_mais_acessadas($start = 0, $limit = 0) {
		$columns = '*';

		//Prepara a consulta SQL
		//        $query = "SELECT dw.id, dw.titulo, dw.autores, SUM(m.visitas) as total_visitas
		//                  FROM Midia m
		//                  JOIN DocumentoView dw
		//                   ON (m.Documento_id = dw.id)
		//                  GROUP BY m.Documento_id
		//                  ORDER BY total_visitas DESC";

		$query = "SELECT d.id,
        				 d.titulo,
        				 d.autores_nome_completo AS autores,
        				 SUM(m.visitas) AS total_visitas
                  FROM Midia m
                  JOIN DocumentoConsulta d
                   ON (m.Documento_id = d.id)
                  GROUP BY m.Documento_id
                  ORDER BY total_visitas DESC";

		//Verifica se os parâmetros para limite/paginação foram passados
		if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			//Limita a quantidade de resultados a partir dos valores especificados
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
	 * Obtém os últimos documentos cadastrados
	 *
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @return array Resultado da pesquisa
	 */
	public function get_ultimos_documentos($start = 0, $limit = 0) {
		$columns = '*';
			
		//Prepara a consulta SQL
		$query = "SELECT
                    d.id,
                    d.titulo,
                    d.data_inclusao,
                    GROUP_CONCAT(`a`.`nome_completo` SEPARATOR ', ') AS autores
                  FROM Documento d
                  LEFT JOIN AutorDocumento ad
                     ON (ad.Documento_id = d.id)
                  LEFT JOIN Autor a
                     ON (ad.Autor_id = a.`id`)
                  GROUP BY d.id
                  ORDER BY data_inclusao DESC";

		//Verifica se os parâmetros para limite/paginação foram passados
		if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			//Limita a quantidade de resultados a partir dos valores especificados
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
	 * Retorna os gêneros de um determinado tipo de documento
	 *
	 * @param integer $id_tipo ID do tipo de documento (opcional)
	 * @param integer $id ID do gênero (opcional)
	 * @return array Resultados da pesquisa
	 */
	public function get_generos($id_tipo = '', $id = '') {
		//Prepara a consulta SQL
		$query = 'SELECT *
                  FROM Genero ';
		//Adiciona a clausula WHERE para obter os generos de um determinado tipo de documento
		if ($id_tipo) {
			$query.= sprintf("WHERE TipoDocumento_id='%s' ORDER BY nome",
							 mysqlx_real_escape_string($id_tipo));
		}

		//Adiciona a clausula WHERE para obter um registro específico
		elseif ($id) {
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

	//Adaptação *vários gêneros* do get_genero
	public function get_generos_new($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', t1.', $fields);
		}		
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
                          FROM Genero t1
                            JOIN DocumentoGenero t2
                              ON (t1.id = t2.Genero_id)
                          WHERE t2.Documento_id='%s'", 
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
	 * Retorna as categorias de um determinado tipo de documento
	 *
	 * @param integer $id_tipo ID do tipo de documento (opcional)
	 * @param integer $id ID do gênero (opcional)
	 * @return array Resultados da pesquisa
	 */
	public function get_categorias($id_tipo = '', $id = '') {
		//Prepara a consulta SQL
		$query = 'SELECT *
                  FROM Categoria ';
		//Adiciona a clausula WHERE para obter os generos de um determinado tipo de documento
		if ($id_tipo) {
			$query.= sprintf("WHERE TipoDocumento_id='%s' ORDER BY nome",
							 mysqlx_real_escape_string($id_tipo));
		}
		//Adiciona a clausula WHERE para obter um registro específico
		else if ($id) {
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
	 * Obtém a relação de idiomas disponíveis
	 *
	 * @param integer $id ID do idioma (opcional)
	 * @return array Resultados
	 */

	
	public function get_idiomas($id = '') {
		//Prepara a consulta SQL
		$query = 'SELECT *
                  FROM Idioma ';
		//Adiciona a clausula WHERE para obter um registro específico
		if ($id) {
			$query.= sprintf("WHERE id='%s'
                              LIMIT 1 ORDER BY descricao",
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
	

	public function get_idiomas_new($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(', t1.', $fields);
		}		
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
                          FROM Idioma t1
                            JOIN DocumentoIdioma t2
                              ON (t1.id = t2.Idioma_id)
                          WHERE t2.Documento_id='%s'", 
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
	 * Obtém a relação de tipos de documentos
	 *
	 * @param integer $id ID do tipo (opcional)
	 * @return array Resultados
	 *
	 */
	/*
	 public function get_tipos($id = '') {
	 //Prepara a consulta SQL
	 $query = 'SELECT *
	 FROM TipoDocumento ';
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
	 */

	/**
	 * Obtém a relação de tipos de documentos
	 *
	 * @param integer $id ID do tipo (opcional)
	 * @return array Resultados
	 */
	public function get_tipos($id = '', $somente_com_documentos = false) {

		$tipos = array(
		DOCUMENTOS_AUDIOVISUAIS_ID => 'Audiovisuais',
		DOCUMENTOS_BIBLIOTECA_ID => 'Biblioteca',
		DOCUMENTOS_COMPROVANTES_ADAPTACOES_ID => 'Comprovantes de Adaptações',
		DOCUMENTOS_COMPROVANTES_EDICOES_ID => 'Comprovantes de Edições',
		DOCUMENTOS_CORRESPONDENCIAS_ID => 'Correspondências',
		DOCUMENTOS_COMPROVANTES_CRITICA_ID => 'Comprovantes de Crítica',
		DOCUMENTOS_HISTORIA_EDITORIAL_ID => 'História Editorial',
		DOCUMENTOS_ILUSTRACOES_ID => 'Ilustrações',
		DOCUMENTOS_MEMORABILIA_ID => 'Memorabilia',
		DOCUMENTOS_ESBOCOS_NOTAS_ID => 'Esboços e Notas',
		DOCUMENTOS_OBJETOS_ARTE_ID => 'Objetos de Arte',
		DOCUMENTOS_OBRA_LITERARIA_ID => 'Obra Literária',
		DOCUMENTOS_ORIGINAIS_ID => 'Originais',
		DOCUMENTOS_PUBLICACOES_IMPRENSA_ID => 'Publicações na Imprensa',
		DOCUMENTOS_VIDA_ID => 'Vida',
		DOCUMENTOS_OBRA_ID => 'Obra'
		);
			
		if ($id) {
			return $tipos[$id];
		}

		if ($somente_com_documentos) {
			$query = 'SELECT TipoDocumento_id AS tipo
                      FROM Documento
                      GROUP BY TipoDocumento_id';
			try {
				$result_sql = $this->DB->query($query);
				$tipos_com_documentos = array();
				while ($row = mysqli_fetch_array($result_sql)) {
					$tipos_com_documentos[$row['tipo']] = $row['tipo'];
				}
				foreach ($tipos as $id=>$tipo) {
					if (!isset($tipos_com_documentos[$id])) {
						unset($tipos[$id]);
					}
				}
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return false;
			}

		}

		return $tipos;
	}

	function get_tipoDocs($id='') {
		//Prepara a consulta SQL
		$query = 'SELECT id, nome
                  FROM TipoDocumento ';
		//Adiciona a clausula WHERE para obter um registro específico
		if ($id) {
			$query.= sprintf("WHERE id='%s'
                              LIMIT 1 ORDER BY nome",
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
	 * Obtém a relação de URIs da parte administrativa de documentos
	 *
	 * @param integer $id ID do tipo (opcional)
	 * @return array Resultados
	 */
	function get_uri_tipos($id) {
		$uris = array(
		DOCUMENTOS_AUDIOVISUAIS_ID => ADMIN_DOCUMENTOS_AUDIOVISUAIS_URI,
		DOCUMENTOS_BIBLIOTECA_ID => ADMIN_DOCUMENTOS_BIBLIOTECA_URI,
		DOCUMENTOS_COMPROVANTES_ADAPTACOES_ID => ADMIN_DOCUMENTOS_COMPROVANTES_ADAPTACOES_URI,
		DOCUMENTOS_COMPROVANTES_EDICOES_ID => ADMIN_DOCUMENTOS_COMPROVANTES_EDICOES_URI,
		DOCUMENTOS_CORRESPONDENCIAS_ID => ADMIN_DOCUMENTOS_CORRESPONDENCIAS_URI,
		DOCUMENTOS_COMPROVANTES_CRITICA_ID => ADMIN_DOCUMENTOS_COMPROVANTES_CRITICA_URI,
		DOCUMENTOS_HISTORIA_EDITORIAL_ID => ADMIN_DOCUMENTOS_HISTORIA_EDITORIAL_URI,
		DOCUMENTOS_ILUSTRACOES_ID => ADMIN_DOCUMENTOS_ILUSTRACOES_URI,
		DOCUMENTOS_MEMORABILIA_ID => ADMIN_DOCUMENTOS_MEMORABILIA_URI,
		DOCUMENTOS_ESBOCOS_NOTAS_ID => ADMIN_DOCUMENTOS_ESBOCOS_NOTAS_URI,
		DOCUMENTOS_OBJETOS_ARTE_ID => ADMIN_DOCUMENTOS_OBJETOS_ARTE_URI,
		DOCUMENTOS_OBRA_LITERARIA_ID => ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI,
		DOCUMENTOS_ORIGINAIS_ID => ADMIN_DOCUMENTOS_ORIGINAIS_URI,
		DOCUMENTOS_PUBLICACOES_IMPRENSA_ID => ADMIN_DOCUMENTOS_PUBLICACOES_IMPRENSA_URI,
		DOCUMENTOS_VIDA_ID => ADMIN_DOCUMENTOS_VIDA_URI,
		DOCUMENTOS_OBRA_ID => ADMIN_DOCUMENTOS_OBRA_URI
		);
		if ($id) {
			return $uris[$id];
		}
		return $uris;
	}

	/**
	 * Adiciona o Documento no banco de dados
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	protected function add_documento($tipo, $titulo, $autores, $generos, $categoria = array(), $fontes = array(),
	$id_material = '', $editoras = array(), $abrangencia = '',
	$direitos = '', $acervo_id = '', $localizacao = '', $estado = '', $descricao = '',
	$ano_producao = '', $dimensao = '', $idiomas) {
		
	//	var_dump($idiomas);

		if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
			return json_encode(array('error' => 'Acesso negado'));
		}

		//Validação (Tipo do Documento)
		if (!$tipo) {
			return json_encode(array('error' => 'Tipo de documento não especificado'));
		}
		elseif (!$this->get_tipos($tipo)) {
			return json_encode(array('error' => "Tipo de documento é inválido ($tipo)"));
		}

		//Validação dos campos
		#$invalid_fields = $this->validate_parameters_documentos($titulo, $autores, $genero, $categoria, $fontes,
		#$id_material, $estado, $ano_producao, $idioma);
		$invalid_fields = $this->validate_parameters_documentos($titulo, $autores, $generos, $categoria, $fontes,
		$id_material, $estado, $ano_producao, $idiomas);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}

		//Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['titulo'] = $titulo;
		$fields['id_material'] = $id_material;
		// $fields['Editora_id'] = $editora_id;
		$fields['abrangencia'] = $abrangencia;
		$fields['direitos'] = $direitos;
		$fields['Acervo_id'] = $acervo_id;
		$fields['localizacao'] = $localizacao;
		$fields['estado'] = $estado;
		$fields['descricao'] = $descricao;
		$fields['ano_producao'] = $ano_producao;
		$fields['dimensao'] = $dimensao;
		//Coluna Idioma_id não será mais usada.
		//$fields['Idioma_id'] = $idioma;
		$fields['TipoDocumento_id'] = $tipo;
		//Coluna Genero_id não será mais usada.
		//$fields['Genero_id'] = $generos;
		$fields['Categoria_id'] = $categoria;
		//Log:
		$fields['Usuario_id'] = $_SESSION['id'];
		$fields['data_inclusao'] = date('Y-m-d h:i:s');

		try {
			$this->DB->insert('Documento', $fields, false);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);	
			return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
		}

		$id = $this->DB->get_last_id();

		foreach ($autores as $autor) {
			//Inserção dos relacionamentos no Banco de Dados
			$query = sprintf("INSERT INTO AutorDocumento (Documento_id, Autor_id)
                              VALUES('%s', '%s')",
			mysqlx_real_escape_string($id),
			mysqlx_real_escape_string($autor));
			try {
				$this->DB->query($query);
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return json_encode(array('error' => 'Ocorreu um erro ao armazenar os autores'));
			}
		}

		foreach ($fontes as $fonte) {
			//Inserção dos relacionamentos no Banco de Dados
			$query = sprintf("INSERT INTO DocumentoFonte (Documento_id, Fonte_id)
                              VALUES('%s', '%s')",
			mysqlx_real_escape_string($id),
			mysqlx_real_escape_string($fonte));
			try {
				$this->DB->query($query);
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return json_encode(array('error' => 'Ocorreu um erro ao armazenar as fontes'));
			}
		}		
		
		foreach ($editoras as $editora) {
			//Inserção dos relacionamentos no Banco de Dados
			$query = sprintf("INSERT INTO DocumentoEditora (Documento_id, Editora_id)
                              VALUES('%s', '%s')",
					mysqlx_real_escape_string($id),
					mysqlx_real_escape_string($editora));
			try {
				$this->DB->query($query);
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return json_encode(array('error' => 'Ocorreu um erro ao armazenar as editoras'));
			}
		}

		foreach ($generos as $genero) {
			//Inserção dos relacionamentos no Banco de Dados
			$query = sprintf("INSERT INTO DocumentoGenero (Documento_id, Genero_id)
                              VALUES('%s', '%s')",
			mysqlx_real_escape_string($id),
			mysqlx_real_escape_string($genero));
			try {
				$this->DB->query($query);
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return json_encode(array('error' => 'Ocorreu um erro ao armazenar os gêneros'));
			}
		}

		foreach ($idiomas as $idioma) {
			//Inserção dos relacionamentos no Banco de Dados
			
			$query = sprintf("INSERT INTO DocumentoIdioma (Documento_id, Idioma_id)
                              VALUES('%s', '%s')",
			mysqlx_real_escape_string($id),
			mysqlx_real_escape_string($idioma));
			try {
				$this->DB->query($query);
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return json_encode(array('error' => 'Ocorreu um erro ao armazenar os idiomas'));
			}
		}

		//Nenhum erro ocorreu. Retorna nulo e o ID do registro inserido.
		return json_encode(array('error' => null, 'id' => $id));
	}

	/**
	 * Atualiza o Documento no banco de dados
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	protected function update_documento($id, $titulo, $autores, $generos, $categoria = array(), $fontes = array(),
	$id_material = '', $editoras = array(), $abrangencia = '',
	$direitos = '', $acervo_id = '', $localizacao = '', $estado = '', $descricao = '',
	$ano_producao = '', $dimensao = '', $idiomas) {

		if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
			return json_encode(array('error' => 'Acesso negado'));
		}

		//Validação (Tipo do Documento)
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}

		//Validação dos campos
		$invalid_fields = $this->validate_parameters_documentos($titulo, $autores, $generos, $categoria, $fontes,
		$id_material, $estado, $ano_producao, $idiomas, $id);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}

		//Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['titulo'] = $titulo;
		$fields['id_material'] = $id_material;
		// $fields['Editora_id'] = $editora_id;
		$fields['abrangencia'] = $abrangencia;
		$fields['direitos'] = $direitos;
		$fields['Acervo_id'] = $acervo_id;
		$fields['localizacao'] = $localizacao;
		$fields['estado'] = $estado;
		$fields['descricao'] = $descricao;
		$fields['ano_producao'] = $ano_producao;
		$fields['dimensao'] = $dimensao;
		//Coluna Idioma_id não será mais usada.
		//$fields['Idioma_id'] = $idioma;
		//$fields['TipoDocumento_id'] = $tipo;
		//Coluna Genero_id não será mais usada.
		//$fields['Genero_id'] = $genero;
		$fields['Categoria_id'] = $categoria;
		//Log:
		$fields['Usuario_id'] = $_SESSION['id'];
		$fields['data_atualizacao'] = date('Y-m-d h:i:s');

		//Clausula WHERE para atualizar apenas o registro especificado
		$where = "id = '" . mysqlx_real_escape_string($id) . "'";

		try {
			$this->DB->update('Documento', $fields, $where);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
		}

		//TODO: Utilizar INSERT IGNORE ou REPLACE

		//Exclui os antigos relacionamentos entre Autor e Documento
		$query = sprintf("DELETE FROM AutorDocumento
                          WHERE Documento_id = '%s'",
		mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
			foreach ($autores as $autor) {
				//Inserção dos relacionamentos no Banco de Dados
				$query = sprintf("INSERT INTO AutorDocumento (Documento_id, Autor_id)
                                  VALUES('%s', '%s')",
				mysqlx_real_escape_string($id),
				mysqlx_real_escape_string($autor));
				$this->DB->query($query);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar os autores'));
		}

		//Exclui os antigos relacionamentos entre Fonte e Documento
		$query = sprintf("DELETE FROM DocumentoFonte
                          WHERE Documento_id = '%s'",
		mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
			foreach ($fontes as $fonte) {
				//Inserção dos relacionamentos no Banco de Dados
				$query = sprintf("INSERT INTO DocumentoFonte (Documento_id, Fonte_id)
                                  VALUES('%s', '%s')",
				mysqlx_real_escape_string($id),
				mysqlx_real_escape_string($fonte));
				$this->DB->query($query);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar as fontes'));
		}
		
		//Exclui os antigos relacionamentos entre Editora e Documento
		$query = sprintf("DELETE FROM DocumentoEditora
                          WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
			foreach ($editoras as $editora) {
				//Inserção dos relacionamentos no Banco de Dados
				$query = sprintf("INSERT INTO DocumentoEditora (Documento_id, Editora_id)
                                  VALUES('%s', '%s')",
						mysqlx_real_escape_string($id),
						mysqlx_real_escape_string($editora));
				$this->DB->query($query);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar as editoras'));
		}

		//Exclui os antigos relacionamentos entre Gênero e Documento
		$query = sprintf("DELETE FROM DocumentoGenero
                          WHERE Documento_id = '%s'",
		mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
			foreach ($generos as $genero) {
				//Inserção dos relacionamentos no Banco de Dados
				$query = sprintf("INSERT INTO DocumentoGenero (Documento_id, Genero_id)
	                              VALUES('%s', '%s')",
				mysqlx_real_escape_string($id),
				mysqlx_real_escape_string($genero));
				$this->DB->query($query);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar os gêneros'));
		}

		
		//Exclui os antigos relacionamentos entre Idioma e Documento
		$query = sprintf("DELETE FROM DocumentoIdioma
                          WHERE Documento_id = '%s'",
		mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
			foreach ($idiomas as $idioma) {
				//Inserção dos relacionamentos no Banco de Dados
				$query = sprintf("INSERT INTO DocumentoIdioma (Documento_id, Idioma_id)
	                              VALUES('%s', '%s')",
						mysqlx_real_escape_string($id),
						mysqlx_real_escape_string($idioma));
				$this->DB->query($query);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar os idiomas'));
		}

		//Nenhum erro ocorreu. Retorna nulo.
		return json_encode(array('error' => null));
	}
	
	private function removeRelacoes($id) {
		//Exclui os antigos relacionamentos entre Autor e Documento
		$query = sprintf("DELETE FROM AutorDocumento
                          WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar os autores'));
		}
		
		//Exclui os antigos relacionamentos entre Fonte e Documento
		$query = sprintf("DELETE FROM DocumentoFonte
                          WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar as fontes'));
		}
		
		//Exclui os antigos relacionamentos entre Editoras e Documento
		$query = sprintf("DELETE FROM DocumentoEditora
                          WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar as editoras'));
		}
		
		//Exclui os antigos relacionamentos entre Gênero e Documento
		$query = sprintf("DELETE FROM DocumentoGenero
                          WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar os gêneros'));
		}
		
		//Exclui os antigos relacionamentos entre Idioma e Documento
		$query = sprintf("DELETE FROM DocumentoIdioma
                          WHERE Documento_id = '%s'",
				mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao atualizar os idiomas'));
		}
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
		
		try {
	    	foreach ($ids as $id) {
	    		$this->removeRelacoes($id);
	    	}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() );
			return json_encode(array('error' => 'Erro ao excluir índices do Lucene'));
		}
	// continua
		$arquivos = array();
		
		//Atualiza os índices do Lucene
		$ids_str = implode("','", $ids);
		$query = sprintf("SELECT id, nome_arquivo FROM Midia
                          WHERE Documento_id IN ('%s')", $ids_str);		
		try {
			$result_sql = $this->DB->query($query);
			
			while ($row = mysqli_fetch_array($result_sql)) {
				
				$this->remove_index_one_document($row['id']);				
				$arquivos[] = $row['nome_arquivo'];
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro ao excluir índices do Lucene'));
		}
		
		//Move o arquivo para a lixeira
		$trash_path = DOCUMENTS_PATH . '_trash';
		
		if (!is_dir($trash_path)) {
			if (!@mkdir($trash_path, 0755)) {
				return json_encode(array('error'=>'Erro ao criar o diretório de arquivos excluídos'));
			}
		}
		if (!is_writable($trash_path)) {
			if (!@chmod($trash_path, 0755)) {
				return json_encode(array('error'=>'Erro ao alterar permissões do diretório de arquivos excluídos'));
			}
		}
		
		foreach ($arquivos as $arquivo) {
			
			//Verifica se outro documento utiliza este mesmo arquivo
			$query = sprintf("SELECT COUNT(*) AS num
		                  	  FROM Midia
		                  	  WHERE nome_arquivo = '%s'
		                  	   AND Documento_id NOT IN ('%s')",
							 $arquivo,
							 $ids_str);
			try {
				$result = $this->DB->query($query);
				$total_rows = mysqlx_result($result, 0, 'num');
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return json_encode(array('error'=>'Erro ao pesquisar no banco de dados'));
			}
			
			if ($total_rows == 0) {
				//Move o arquivo para o diretório de arquivos excluídos
				if (!@rename(DOCUMENTS_PATH . $arquivo, $trash_path . '/' . $arquivo)) {
					return json_encode(array('error'=>'Erro ao mover o arquivo excluído'));
				}
			}
		}
		
		//Exclui do banco de dados
		$query = sprintf("DELETE FROM Documento
                          WHERE id IN ('%s')", $ids_str);
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

	public function visita_midia($id){
		//Validação (Tipo do Documento)
		if (!$id || !is_numeric($id)) {
			return json_encode(array('error' => 'ID não especificado ou inválido'));
		}

		$query = sprintf("UPDATE Midia SET visitas = visitas+1 WHERE id = '%s' ", $id);

		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro ao excluir atualizar contador de mídias'));
		}

		return json_encode(array('error' => null));
	}

	/**
	 * Manipula o envio de arquivos (documentos)
	 *
	 * @param boolean $xhr Caso o arquivo tenha sido enviado via XHR (php://input)
	 * @param array $form_file Informações sobre o arquivo enviado via formulário ($_FILES)
	 *
	 * @return string Resultado do envio no formato JSON
	 */
	public function upload($xhr, $form_file = null) {
		try {
			$maxFileSize = MAX_FILE_SIZE * 1024 * 1024; //150MB
			if ($xhr) {
				$file = new qqUploadedFileXhr();
			}
			elseif ($form_file) {
				if ($form_file['error'] !== UPLOAD_ERR_OK) {
					$error_msg = file_upload_error_message($foto['error']);
					return htmlspecialchars(json_encode(array('error'=>$error_msg)), ENT_NOQUOTES);
				}
				$file = new qqUploadedFileForm();
			}
			else {
				return htmlspecialchars(json_encode(array('error'=>'Nenhum arquivo foi enviado')), ENT_NOQUOTES);
			}
			$size = $file->getSize();
			if ($size == 0) {
				return htmlspecialchars(json_encode(array('error'=>'Arquivo vazio')), ENT_NOQUOTES);
			}
			if ($size > $maxFileSize) {
				return htmlspecialchars(json_encode(array('error'=>'Excedeu o tamanho máximo permitido (50MB)')), ENT_NOQUOTES);
			}

			$file_info = pathinfo($file->getName());

			if (!in_array($file_info['extension'], array('html', 'htm', 'pdf', 'doc', 'txt', 'ppt', 'pps', 'zip', 'rar', 'gz', 'xls', 'png', 'jpg', 'jpeg', 'gif'))) {
				return htmlspecialchars(json_encode(array('error'=>'Formato não permitido')), ENT_NOQUOTES);
			}

			//$file_crc = $file->getCRC($form_file);
			//$file_basename = $file_crc . '.' . $file_info['extension'];
			$file_basename = clean_filename($file->getName());
			$target_file = DOCUMENTS_PATH . $file_basename;
			
			if (!is_dir(DOCUMENTS_PATH)) {
				if (!@mkdir(DOCUMENTS_PATH, 0755)) {
					return htmlspecialchars(json_encode(array('error'=>'Erro ao criar o diretório de uploads')), ENT_NOQUOTES);
				}
			}
			if (!is_writable(DOCUMENTS_PATH)) {
				if (!@chmod(DOCUMENTS_PATH, 0755)) {
					return htmlspecialchars(json_encode(array('error'=>'Erro ao alterar permissões do diretório de uploads')), ENT_NOQUOTES);
				}
			}
			$i = 1;
			while (file_exists($target_file)) {
				$query = sprintf("SELECT COUNT(*) AS num
								  FROM Midia
								  WHERE nome_arquivo = '%s'",
								  $file_basename);
				try {
					$result = $this->DB->query($query);
					$total_rows = mysqlx_result($result, 0, 'num');
					if ($total_rows == 0) {
						unlink($target_file);
						break;
					}
				}
				catch (Exception $e) {
					Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				}
				
				$file_basename = clean_filename($file_info['filename'] . '-' . $i . '.' . $file_info['extension']);
				$target_file = DOCUMENTS_PATH . $file_basename;
				if ($i >= 100) {
					return htmlspecialchars(json_encode(array('error'=>'Já existe um arquivo com este nome')), ENT_NOQUOTES);
				}
				$i++;
			}
			if (!$file->save($target_file, $form_file)) {
				return htmlspecialchars(json_encode(array('error'=>'Não foi possível salvar o arquivo no servidor')), ENT_NOQUOTES);
			}
			return htmlspecialchars(json_encode(array('success'=>true, 'filename'=>$file_basename)), ENT_NOQUOTES);
		}
		catch (Exception $e) {
			return htmlspecialchars(json_encode(array('error'=>$e->getMessage())), ENT_NOQUOTES);
		}
	}

	/**
	 * Adiciona referências para as mídias (arquivos) de um documento no banco de dados
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	public function add_midias($id_documento, $arquivos, $titulos_arquivos, $descricoes_arquivos, $fontes_arquivos) {
		
		
		if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
			return json_encode(array('error' => 'Acesso negado'));
		}
		

		//$controle_fontes = ControleFontes::getInstance();
		$errors = array();
		
		$index = -1;
		foreach ($arquivos as $arquivo) {
		
			if ($arquivo) {
				$index++;
				$path_arquivo = DOCUMENTS_PATH . $arquivo;
				
				if (is_file($path_arquivo)) {
					
					if (sizeof($arquivo) > 255) {
						$ext = pathinfo($path_arquivo, PATHINFO_EXTENSION);
						$arquivo = substr($arquivo, 0, 250);
						$arquivo.= '.' . $ext;
						$old_path_arquivo = $path_arquivo;
						$path_arquivo = DOCUMENTS_PATH . $arquivo;
						rename($old_path_arquivo, $path_arquivo);
					}
					$fields = array();
					$fields['nome_arquivo'] = $arquivo;
					
					if (isset($titulos_arquivos[$index])) {
						$fields['titulo'] = $titulos_arquivos[$index];
					}
					if (isset($descricoes_arquivos[$index])) {
						$fields['descricao'] = $descricoes_arquivos[$index];
					}
					$fields['mime'] = get_mime_type($path_arquivo, MIME_PATH);
					//                  $fields['tamanho'] = round(filesize($path_arquivo)/1024, 2); //KBytes
					$fields['tamanho'] = filesize($path_arquivo);
					if (isset($fontes_arquivos[$index]) && $fontes_arquivos[$index]) {
						//$fonte = $controle_fontes->get(null, array('id', 'descricao'), 0, 1, $fontes_arquivos[$index]);
						//if ($fonte) {
						//  if ($fonte[0]['descricao'] == $fontes_arquivos[$index]) {
						//        $fields['Fonte_id'] = $fonte[0]['id'];
						//  }
						//}
						$fields['fonte'] = $fontes_arquivos[$index];
					}
						
					$fields['Documento_id'] = $id_documento;
					$fields['data_insercao'] = date('Y-m-d');
					try {
						$this->DB->insert('Midia', $fields, false);
						
						if (preg_match('/\.htm/',$arquivo)){
							$this->updateLuceneIndex($id_documento,$arquivo);
						}
					}
					catch (Exception $e) {
						Logger::log($e->getMessage(), __FILE__);
					//	$errors[] = $arquivo;
					    $errors[] = $e->getMessage();
					}
				}
			}
		}
		if ($errors) {
			return json_encode(array('error' => 'Erro ao inserir os seguintes arquivos: ' . implode(',', $errors)));
		}
		return json_encode(array('error' => null));
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
                     id,
                     titulo,
                     autores,
                     midias
                   FROM DocumentoView
                   WHERE TipoDocumento_id = {$aParams['iTypeId']}";
		if ($aClauses['sWhere']) {
			$sQuery.= substr_replace($aClauses['sWhere'], ' AND (', 0, 6) . ')';  //Replace WHERE with AND (...)
		}
		$sQuery.= " {$aClauses['sOrder']}";
		$sQuery.= " {$aClauses['sLimit']}";
		return $sQuery;
	}

	//    /**
	//     * Cria a cláusula WHERE da consulta para obter os dados da tabela
	//     *
	//     * @override (DataTables):
	//     * @param array $aColumns Colunas da tabela
	//     * @param string $sSearch Termo de busca
	//     * @return string Cláusula WHERE
	//     */
	//    protected function fnBuildWhereClause($aColumns, $sSearch) {
	//        $sWhere = '';
	//        if ($sSearch != '') {
	//          $sWhere = "WHERE d.titulo LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR
	//                           nome_completo LIKE '%" . mysqlx_real_escape_string($sSearch) . "%'";
	//        }
	//        return $sWhere;
	//    }

	/**
	 * Valida os campos (parâmetros) do Documento
	 *
	 * @return array Campos que não passaram no teste de validação
	 */
	protected function validate_parameters_documentos($titulo, $autores, $generos, $categoria = '', $fontes = array(),
													  $id_material = '', $estado = '', $ano_producao = '', $idiomas, $id = '') {
		$invalid_fields = array();
		if (!$titulo) {
			$invalid_fields['titulo'] = 'O título não pode ser vazio';
		}
		if (!$autores) {
			$invalid_fields['autores'] = 'É necessário haver ao menos um autor';
		}
		elseif (!is_array($autores)) {
			$invalid_fields['autores'] = 'Autore(s) inválido(s)';
		}
		#if(!$generos){
		#	$invalid_fields['generos'] = 'Escolha um gênero';
		#}
		if (!$generos) {
			$invalid_fields['generos'] = 'É necessário haver ao menos um gênero';
		}
		elseif (!is_array($generos)) {
			$invalid_fields['generos'] = 'Gênero(s) inválido(s)';
		}
		if ($categoria && !$this->get_categorias('', $categoria)) {
			$invalid_fields['categoria'] = 'Categoria inválida';
		}
		if ($id_material) {
			$query = sprintf("SELECT COUNT(*) as total
                              FROM Documento
                              WHERE id_material = '%s'",
							 mysqlx_real_escape_string($id_material));

			if ($id) {
				$query.= sprintf(" AND id != '%s'", mysqlx_real_escape_string($id));
			}

			$result = $this->DB->query($query);
			if (mysqlx_result($result, 0, 'total') > 0) {
				$invalid_fields['id_material'] = 'Já existe um documento com este código';
			}
		}
		if (!$fontes){
			$invalid_fields['fontes'] = 'É necessário haver ao menos uma fonte';
		}
		elseif ($fontes && !is_array($fontes)) {
			$invalid_fields['fontes'] = 'Fonte(s) inválida(s)';
		}
		if ($estado && $estado != 'Bom' && $estado != 'Muito bom' && $estado != 'Péssimo' && $estado != 'Regular') {
			$invalid_fields['estado'] = 'Estado de conservação é inválido';
		}
		if ($ano_producao && !is_numeric($ano_producao)) {
			$invalid_fields['ano_producao'] = 'Ano de produção inválido';
		}
		/*
		if(!$idioma){
			$invalid_fields['idioma'] = 'Escolha um idioma';
		}
		elseif ($idioma && !$this->get_idiomas($idioma)) {
			$invalid_fields['idioma'] = 'Idioma inválido';
		}
		*/
		if (!$idiomas) {
			$invalid_fields['idiomas'] = 'É necessário haver ao menos um idioma';
		}
		elseif (!is_array($idiomas)) {
			$invalid_fields['idiomas'] = 'Idioma(s) inválido(s)';
		}

		return $invalid_fields;
	}


	public function count_documentos() {
		return parent::count('Documento');
	}

	public function count_autores() {
		return parent::count('Autor');
	}

	public function count_midias() {
		$query = "SELECT COUNT(DISTINCT Documento_id) AS num
                  FROM Midia";
		try {
			$result = $this->DB->query($query);
			$total_rows = mysqlx_result($result, 0, 'num');
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return 0;
		}
		return $total_rows;
	}

	/*
	 public function getAnoSeculo($seculo) {
	 //essa funcao retorna o ano de inicio de um seculo. ex: getAnoSeculo(XV) = 1401
	 //para usar o perido basta adicionar 99 no resultado e vc tem o periodo de um seculo= 1401-1500
	 $array_sec = array(0 => "I", 1 => "II", 2 => "III", 3 => "IV", 4 => "V", 5 => "VI",
	 6 => "VII", 7 => "VIII", 8 => "IX", 9 => "X", 10 => "XI",
	 11 => "XII", 12 => "XIII", 13 => "XIV", 14 => "XV", 15 => "XVI",
	 16 => "XVII", 17 => "XVIII", 18 => "XIX", 19 => "XX", 20 => "XXI",
	 21 => "XXII", 22 => "XXIII");

	 while(strcmp($seculo,current($array_sec)) != 0) {
	 next($array_sec);
	 }

	 $ano = key($array_sec);
	 $ano = ($ano*100)+1;

	 return $ano;

	 }

	 public function getSeculoAno($ano) {
	 //essa funcao retorna seculo referente ao ano passado ex: 1940 = XX
	 $array_sec = array(0 => "I", 1 => "II", 2 => "III", 3 => "IV", 4 => "V", 5 => "VI",
	 6 => "VII", 7 => "VIII", 8 => "IX", 9 => "X", 10 => "XI",
	 11 => "XII", 12 => "XIII", 13 => "XIV", 14 => "XV", 15 => "XVI",
	 16 => "XVII", 17 => "XVIII", 18 => "XIX", 19 => "XX", 20 => "XXI",
	 21 => "XXII", 22 => "XXIII");

	 //pois se for 1400 é SEC XIV, entao volta um ano para facilitar calculo
	 if ($ano > 1) {
	 $ano = $ano-1;
	 }

	 $ano = intval($ano/100);

	 while(key($array_sec) != $ano) {
	 next($array_sec);
	 }

	 $seculo = current($array_sec);

	 return $seculo;
	 }
	 */

	/**
	 * Indexa todos os documentos
	 *
	 */
	public function index_alldocuments(){
		
		set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());
		require_once ('Zend/Search/Lucene.php');
		require_once ('IndexDocument.php');
		
		setlocale(LC_CTYPE, 'pt_BR.utf-8');
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new
		Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
		
		$index = Zend_Search_Lucene::create('../zf/indexes', true);

		
		$diretorio = "../../public/_documents/";
		$query = "SELECT *, m.id as id_midia, m.titulo as titulo_midia
				  FROM Midia m
				  JOIN Documento d
				   ON m.Documento_id=d.id
				  WHERE nome_arquivo LIKE '%.htm%'
				  ORDER BY nome_arquivo";

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		$results = $this->DB->parse_result($result_sql);

		$j=0;

		foreach ($results as $document) {

			$document['link']=dirname(__FILE__)."/../../public/_documents/".$document['nome_arquivo'];
			$fileContent = null;

			echo "TENTANDO INDEXAR DOCUMENTO ".$document['titulo']." - ".$document['id_midia']." - ".$document['titulo_midia']." - ".$document['nome_arquivo']." - ".$document['link']."\n";

			try {
					
				$fileContent = file_get_contents($document['link']);
				if(!is_null($fileContent)){

					$document['arquivo']=$fileContent;
					$obra = $this->get($document['Documento_id']);
					$autores = $this->get_autores($document['Documento_id']);
					$generos = $this->get_generos_new($document['Documento_id']);
					$document['tipo'] = $this->get_tipos($document['TipoDocumento_id']);
					#$document['genero'] = $this->get_generos(null,$document['Genero_id']);
					$document['genero'] = $generos;
					$document['obra'] = $obra;
					$document['autores'] = $autores;
					$docindex=new IndexDocument($document);
					
				//	var_dump($docindex);
					$index->addDocument($docindex);
					
					echo "ADICIONADO DOCUMENTO ".$j."\n";
					$resultado = $index->commit();
					
					
					echo "COMITADO DOCUMENTO ".$j."\n\n";
					flush();

				}
			} catch (Exception $e) {
				echo $e->getMessage()." \n";
				//Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				echo "ARQUIVO NAO ENCONTRADO:".$document['id']." \n";
			}
			$j++;
			echo "PROXIMO  \n \n ";

		}
		$index->optimize();
		echo "OTIMIZOU \n";
		// var_dump($index);
		unset($index);
	}

	public function remove_index_one_document($id_midia){		
		/*
		set_include_path(".:" .dirname(__FILE__) . '/../zf:'.dirname(__FILE__).'/../zf/Zend');

		require_once dirname(__FILE__).('/../zf/Zend/Search/Lucene.php');
		require_once dirname(__FILE__).('/../zf/IndexDocument.php');
		
		*/
		set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());
		require_once ('Zend/Search/Lucene.php');
		require_once ('IndexDocument.php');		

		$index = Zend_Search_Lucene::open(dirname(__FILE__).'/../zf/indexes');
		#$index = Zend_Search_Lucene::open('/var/www/pronex/pronex/application/zf/indexes');		
		$query = new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($id_midia, 'id_midia'));		
		$hits = $index->find($query);
		foreach ($hits as $hit) {
			$index->delete($hit->id);
		}
	}

	public function index_one_document($id_document, $arquivo) {
		
		$document = array();
		$document['nome_arquivo'] = $arquivo;
		
		set_include_path(ZEND_PATH.PATH_SEPARATOR.get_include_path());
		require_once('Zend/Search/Lucene.php');
		require_once('IndexDocument.php');

		$diretorio = dirname(__FILE__) . '/../zf/indexes';
		
		$index = Zend_Search_Lucene::open($diretorio);
		

		$query = "SELECT *, m.id as id_midia, m.titulo AS titulo_midia
				  FROM Midia m
				  JOIN Documento d
				   ON m.Documento_id=d.id
				  WHERE nome_arquivo LIKE '$arquivo'
				  LIMIT 1";
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		
		//Retorna os resultados em forma de matriz
		$results = $this->DB->parse_result($result_sql);

		foreach ($results as $document) {
			$document['link'] = realpath(dirname(__FILE__) . "/../../public/_documents/" . $document['nome_arquivo']);

	//log		echo date('Y-m-d H:i:s') . " TENTANDO INDEXAR DOCUMENTO {$document['titulo']} - {$document['id_midia']} - {$document['nome_arquivo']} - {$document['link']}\n";

			try {
				$fileContent = file_get_contents($document['link']);
				
				if (!is_null($fileContent)) {
					$document['arquivo'] = $fileContent;
					$obra = $this->get($document['Documento_id']);
					$autores = $this->get_autores($document['Documento_id']);
					$generos = $this->get_generos_new($document['Documento_id']);
					$document['tipo'] = $this->get_tipos($document['TipoDocumento_id']);
					#$document['genero'] = $this->get_generos(null,$document['Genero_id']);
					$documento['genero'] = $generos;
					$document['obra'] = $obra;
					$document['autores'] = $autores;
					$docindex = new IndexDocument($document);
					
					$index->addDocument($docindex);
					$index->commit();
					//flush();
				}
			}
			catch (Exception $e) {
				echo date('Y-m-d H:i:s') . " ERROR: " . $e->getMessage() . "\n";
				Logger::log($e->getMessage(), __FILE__);
			}

			$index->optimize();
	//log		echo date('Y-m-d H:i:s') . " OTIMIZOU\n";
			unset($index);
		}
	}

	public function updateLuceneIndex($id_document, $arquivo) {

		global $config;
		
		if ($config['local_test']) {
				$this->index_one_document($id_document,$arquivo);
		} else {
						
			$cmd = "php ".dirname(__FILE__)."/../zf/indexa.php '$id_document' '$arquivo'";
		
		    
			$outputfile = realpath(dirname(__FILE__)."/../zf/index.log");
			$pidfile = '/dev/null';
			sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile); 
			exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
			
		}
		
		//$dump = shell_exec("php /home/biblio/public_html/literaturadigital/paulo/application/zf/teste.php");
		//$dump = shell_exec("php ".dirname(__FILE__)."/../zf/teste.php");
		//$dump = shell_exec("php ".dirname(__FILE__)."/../zf/indexa.php '$id_document' '$arquivo'");
		//$dump = shell_exec("ls -la ".dirname(__FILE__).'/../zf/');
	}

	public function calc_adaptacao($document, $id_user){

		//if(!is_int($id_user)){
		//    return json_encode(array('error' => 'Id de usuário deve ser um numero'));
		// }

		$query = sprintf("SELECT Escore
                		  FROM AdUsuarioGenero
                		  WHERE Usuario_id = '%s'
                		   AND Genero_id='%s' ",
						 $id_user,
						 $document['Genero_id']);

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		$results_gen = $this->DB->parse_result($result_sql);
		$escore_gen = 0;
		if($results_gen !=null){
			$escore_gen = current($results_gen[0]);
		}

		/* APENAS PARA TESTES
		 $document['ids_autores'] = $this->get_autores($document['id'],  array('id'));
		 $ids_autores='';
		 foreach( $document['ids_autores'] as $a){
		 $ids_autores = current($a);
		 }*/

		//$ids_autores = explode(',', $document['ids_autores']);

		$ids_autores=str_replace(';', ',', $document['Autor_ids']);

		$query = sprintf("SELECT Escore
                		  FROM `AdUsuarioAutor`
                		  WHERE Usuario_id = '%s'
                		   AND Autor_id IN ('%s') ",
						 $id_user,
					     $ids_autores);

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		$results_autor = $this->DB->parse_result($result_sql);
		$escore_autor = 0;

		foreach ($results_autor as $result_escore ){
			$escore_autor = current($result_escore)+$escore_autor;
		}

		if ($escore_autor > 0){
			$escore_autor = $escore_autor/count($results_autor);
		}


		$document['escore_autor'] = $escore_autor;
		$document['escore_genero'] = $escore_gen;
		$document['escore_total'] = ($escore_autor+$escore_gen)/2;

		return $document;
	}


	public function retorna_escore_total_genero($id_user){

		$query = sprintf("SELECT SUM(Escore) as total_gen FROM AdUsuarioGenero WHERE Usuario_id = '%s'", $id_user);
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}

		$result = $this->DB->parse_result($result_sql);

		$total_gen = 0;
		if ($result[0]['total_gen']){
			$total_gen = $result[0]['total_gen'];
		}

		return $total_gen;

	}

	public function calc_escore_genero($genero, $id_user, $total_gen){

		//if(!is_int($id_user)){
		//    return json_encode(array('error' => 'Id de usuário deve ser um numero'));
		// }

		$query = sprintf("SELECT Escore FROM AdUsuarioGenero adg
						  JOIN Genero g
						   ON adg.`Genero_id`= g.`id`
						  WHERE Usuario_id = '%s'
				           AND g.`nome`='%s'",$id_user,$genero);

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		$results_gen = $this->DB->parse_result($result_sql);

		$escore_gen = 0;
		if($results_gen){
			if ($results_gen[0]['Escore']){
				$escore_gen = $results_gen[0]['Escore'];
			}
		}

		$escore_gen_norma = 0;
		if ($total_gen != 0){
			$escore_gen_norma = round($escore_gen/$total_gen,1);
		}

		return $escore_gen_norma;

	}

	public function retorna_escore_total_autor($id_user){

		$query = sprintf("SELECT SUM(Escore) AS total_aut
						  FROM AdUsuarioAutor
						  WHERE Usuario_id = '%s'", $id_user);
		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}

		$result = $this->DB->parse_result($result_sql);

		$total_aut = 0;
		if ($result[0]['total_aut']){
			$total_aut = $result[0]['total_aut'];
		}

		return $total_aut;

	}

	public function calc_escore_autor($document_id, $id_user, $total_autor){

		$query = sprintf("SELECT Autor_ids
                		  FROM `DocumentoConsulta`
                		  WHERE id = '%s'",
		$document_id);

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		
		if (mysqlx_result($result_sql, 0) == 0) {
			return 0;
		}
		
		//Retorna os resultados em forma de matriz
		$results_autor_ids = $this->DB->parse_result($result_sql);

		$ids_autores = "0";
		
		if ($results_autor_ids)
			$ids_autores=str_replace(';', ',', $results_autor_ids[0]);
		
		/*  Não funcionava na produção mas somente localmente, substituído com as duas acima
		if($results_autor_ids[0]['Autor_ids']){
			$ids_autores=str_replace(';', ',', $results_autor_ids[0]['Autor_ids']);
		}

		*/
		$query = sprintf("SELECT Escore
                		  FROM `AdUsuarioAutor`
                		  WHERE Usuario_id = '%s'
                		   AND Autor_id IN ('%s')",
						 $id_user,
						 $ids_autores);

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		//Retorna os resultados em forma de matriz
		$results_autor = $this->DB->parse_result($result_sql);

		$escore_autor = 0;
		foreach ($results_autor as $result_escore) {
			$escore_autor = current($result_escore)+$escore_autor;
		}

		if ($escore_autor > 0) {
			$escore_autor = $escore_autor/count($results_autor);
		}

		$escore_autor_norm = 0;
		if ($total_autor != 0) {
			$escore_autor_norm = round($escore_autor/$total_autor,1);
		}

		//$document['escore_autor'] = $escore_autor;
		//$document['escore_genero'] = $escore_gen;
		//$document['escore_total'] = ($escore_autor+$escore_gen)/2;

		return $escore_autor_norm;
	}
	
	public function get_obras_recomendadas($id_usuario, $offset, $limit) {
		$cluster = true; // <--- VARIAVEL "SWITCH" PARA DEFINIR A UTILIZACAO OU NAO DA CLUSTERIZACAO
		$consulta_inicial_autores = "SELECT SUM(aua.Escore) FROM Autor a INNER JOIN AdUsuarioAutor aua ON a.id = aua.Autor_id where aua.Usuario_id = $id_usuario";
		$consulta_inicial_generos = "SELECT SUM( aug.Escore ) FROM Genero g INNER JOIN AdUsuarioGenero aug ON g.id = aug.Genero_id WHERE aug.Usuario_id = $id_usuario";		
		$somaAutores = $this->DB->query($consulta_inicial_autores);
		$somaGeneros = $this->DB->query($consulta_inicial_generos);
		$somaAut = mysqli_fetch_array($somaAutores);
		$somaGen = mysqli_fetch_array($somaGeneros);		
		$consulta_autores_normalizados = "SELECT a.id, (adp.Escore / ".$somaAut[0].") AS autor_norm FROM Autor a, AdUsuarioAutor adp WHERE a.id = adp.Autor_id AND adp.Usuario_id = '$id_usuario'";  // print $queryAutorNormalizada;
		$consulta_generos_normalizados = "SELECT g.id, (adg.Escore / ".$somaGen[0].") AS genero_norm FROM AdUsuarioGenero adg, Genero g WHERE adg.Usuario_id = '$id_usuario' AND adg.Genero_id = g.id";	
		$autoresNormalizadosUsuario = $this->DB->query($consulta_autores_normalizados);
		$generosNormalizadosUsuario = $this->DB->query($consulta_generos_normalizados);			
		$similaridades = array ();
		$i = 0;
		
		if ($cluster) {				
			$usuariosAPesquisar = array();  //usuarios que dividem o(s) mesmo(s) cluster(s) do usuario inicial
			$clusterAutor = $this->DB->query( "SELECT idAutor FROM AdClusterAutor WHERE idUsuario='$id_usuario'" );
			$usuariosAdClusterAutor = "";
			while ($autoresDesteUsuarioCluster = mysqli_fetch_array($clusterAutor)) {
				$usuariosAdClusterAutor.= ($autoresDesteUsuarioCluster[0].",");
			}
			if (!empty($usuariosAdClusterAutor)) {
				$usuariosAdClusterAutor = substr($usuariosAdClusterAutor,0, strlen($usuariosAdClusterAutor)-1); //remover a ultima virgula
				$usuariosMesmoClusterA = $this->DB->query("select idUsuario from AdClusterAutor WHERE idAutor in (".$usuariosAdClusterAutor.") and idUsuario <> '$id_usuario'");				
				while ($cadaUsuarioClusterA = mysqli_fetch_array ($usuariosMesmoClusterA)){
					$usuariosAPesquisar[] = $cadaUsuarioClusterA[0];
				}
			}	
			$clusterGenero = $this->DB->query( "SELECT g.idGenero FROM AdClusterGenero g WHERE g.idUsuario=$id_usuario" );
			$usuariosAdClusterGenero = "";
			while ($generosDesteUsuarioCluster = mysqli_fetch_array($clusterGenero)) {
				$usuariosAdClusterGenero.= ($generosDesteUsuarioCluster[0].",");
			}
			if (!empty($usuariosAdClusterGenero)) {
				$usuariosAdClusterGenero = substr($usuariosAdClusterGenero,0, strlen($usuariosAdClusterGenero)-1); //remover a ultima virgula
				$usuariosMesmoClusterG = $this->DB->query("select idUsuario from AdClusterGenero WHERE idGenero in (".$usuariosAdClusterGenero.") and idUsuario <> '$id_usuario'");
				while ($cadaUsuarioClusterG = mysqli_fetch_array ($usuariosMesmoClusterG)){
					$usuariosAPesquisar[] = $cadaUsuarioClusterG[0];
				}
			}						
			if (count($usuariosAPesquisar) == 0){ //caso nao encontre nenhum usuario no(s) cluster(s) que o usuario está, ou o usuario nao pertence a nenhum cluster.
				$usuariosESomaAutor = $this->DB->query ( "SELECT aua.Usuario_id, SUM( aua.Escore ) FROM AdUsuarioAutor aua WHERE aua.Usuario_id <> $id_usuario GROUP BY aua.Usuario_id");
				$usuariosESomaGenero = $this->DB->query ("select aug.Usuario_id, SUM(aug.Escore) from AdUsuarioGenero aug where aug.Usuario_id <> $id_usuario group by aug.Usuario_id");				
			} else {				
				array_unique($usuariosAPesquisar); // retirar valores repetidos de usuários			
				$queryDeUsuariosDoMesmoCluster = "Usuario_id in (";		
				foreach ($usuariosAPesquisar as $cadaUm){					
					$queryDeUsuariosDoMesmoCluster.=($cadaUm.",");				
				}			
				$queryDeUsuariosDoMesmoCluster = substr($queryDeUsuariosDoMesmoCluster,0, strlen($queryDeUsuariosDoMesmoCluster)-1); //remover a ultima virgula
				$queryDeUsuariosDoMesmoCluster.=")";				
				$usuariosESomaAutor = $this->DB->query("SELECT aua.Usuario_id, SUM( aua.Escore ) FROM AdUsuarioAutor aua WHERE aua.$queryDeUsuariosDoMesmoCluster and aua.Usuario_id <> $id_usuario GROUP BY aua.Usuario_id");
				$usuariosESomaGenero = $this->DB->query ("select aug.Usuario_id, SUM(aug.Escore) from AdUsuarioGenero aug where aug.$queryDeUsuariosDoMesmoCluster and aug.Usuario_id <> $id_usuario group by aug.Usuario_id");												
			}			
		} else {
			$usuariosESomaAutor = $this->DB->query ( "SELECT aua.Usuario_id, SUM(aua.Escore) FROM AdUsuarioAutor aua WHERE aua.Usuario_id <> $id_usuario GROUP BY aua.Usuario_id");
			$usuariosESomaGenero = $this->DB->query ("select aug.usuario_id, SUM(aug.Escore) from AdUsuarioGenero aug where aug.Usuario_id <> $id_usuario group by aug.Usuario_id");
		}
		$usuarios_e_somas = array();	
		while ( $autores_deste_usuario = mysqli_fetch_array ( $usuariosESomaAutor ) ) {
			$generos_deste_usuario = mysqli_fetch_array ($usuariosESomaGenero);
			$autores_deste_usuario[2] = $generos_deste_usuario[1];
			array_push ($usuarios_e_somas, $autores_deste_usuario);
		}
		foreach ($usuarios_e_somas as $usuario_e_soma) {			
			$usuario_id = $usuario_e_soma [0];
			$autores_normalizados = "SELECT a.id, (adp.Escore / ".$usuario_e_soma[1].") AS autor_norm FROM Autor a inner join AdUsuarioAutor adp on a.id = adp.Autor_id where adp.Usuario_id = $usuario_id";
			$generos_normalizados = "SELECT g.id, (adg.Escore / ".$usuario_e_soma[2].") AS genero_norm FROM Genero g inner join AdUsuarioGenero adg on g.id=adg.Genero_id where adg.Usuario_id = $usuario_id";		
			$similaridade_autores = $this->calcula_similaridade_autores ( $autoresNormalizadosUsuario, $autores_normalizados );
			$similaridade_generos = $this->calcula_similaridade_generos ( $generosNormalizadosUsuario, $generos_normalizados );
			$similaridade_total = ($similaridade_autores + $similaridade_generos) / 2;				
			array_push ( $similaridades, $usuario_e_soma );
			$similaridades [$i] [3] = $similaridade_total;
			$i ++;
		}
		$limiar = 0.5;		
		$consulta_inicial = "SELECT distinct d.id, d.titulo, a.id as idAutor, a.nome_completo, i.descricao, g.nome, o.ano_publicacao_inicio, o.ano_publicacao_fim,
		o.seculo_producao, o.seculo_publicacao, o.seculo_encenacao, (SELECT COUNT(id) FROM Midia WHERE Documento_id = d.id) AS MID,
		o.id AS CodMaterial, (SELECT COUNT(*) FROM CriticaObraLiteraria cr WHERE cr.ObraLiteraria_id = o.id) AS critica, g.id AS idGenero
		FROM ObraLiteraria o
		INNER JOIN Documento d ON o.Documento_id = d.id
		LEFT JOIN AutorDocumento ad ON ad.Documento_id = d.id
		INNER JOIN Autor a ON ad.Autor_id = a.id
		LEFT JOIN DocumentoGenero dg ON dg.Documento_id=d.id
		INNER JOIN Genero g ON dg.Genero_id = g.id
		LEFT JOIN DocumentoIdioma di ON di.Documento_id=d.id
		INNER JOIN Idioma i ON di.Idioma_id = i.id
		INNER JOIN Midia m ON o.Documento_id = m.Documento_id
		INNER JOIN UsuarioObraLiterariaAcessadas uola ON o.id = uola.ObraLiteraria_id
		WHERE ";		
		$numero_usuarios_semelhantes = 0;
		$consulta_usuarios = "";
		for($i = 0; $i < count ( $similaridades ); $i ++) {
			if (($similaridades [$i] [3] > $limiar)) {
				$numero_usuarios_semelhantes ++;				
				$consulta_usuarios .= $similaridades[$i][0].",";				 
			}
		}	
		if ($numero_usuarios_semelhantes > 0) {
			$consulta_usuarios = substr($consulta_usuarios,0, strlen($consulta_usuarios)-1); //remover a ultima virgula
			$consulta_inicial.= "EXISTS( select Usuario.id from Usuario where Usuario.id in (".$consulta_usuarios.") and uola.Usuario_id=Usuario.id) AND ";
		} else { // se nao houver usuarios com limiar suficiente, nao havera recomendacao.
			return null;
		}
		$consulta_inicial = $consulta_inicial . "uola.ObraLiteraria_id NOT IN (SELECT ObraLiteraria_id FROM UsuarioObraLiterariaAcessadas WHERE Usuario_id='$id_usuario') ";
		$consulta_inicial = $consulta_inicial . " ORDER BY Titulo";
		$result = $this->DB->query ( $consulta_inicial );
		$numArray = mysqli_num_rows ( $result );	
		$autoresScoreBusca = "SELECT Autor_id, Escore FROM AdUsuarioAutor p WHERE p.Usuario_id = '$id_usuario'";
		$autorEscore = array ();
		$result1 = $this->DB->query ( $autoresScoreBusca );		
		while ( $row = mysqli_fetch_array ( $result1 ) ) {
			array_push ( $autorEscore, $row );
		}
		$autorTot = 0;
		for($i = 0; $i < count ( $autorEscore ); $i ++) {
			$autorTot = $autorEscore [$i] [1] + $autorTot;
		}
		for($i = 0; $i < count ( $autorEscore ); $i ++) {
			$autorEscore [$i] [1] = $autorEscore [$i] [1] / $autorTot;
		}
		$generoScoreBusca = "SELECT Genero_id, Escore FROM AdUsuarioGenero p WHERE p.Usuario_id ='$id_usuario'";
		$generoEscore = array ();
		$result1 = $this->DB->query ( $generoScoreBusca );
		while ( $row = mysqli_fetch_array ( $result1 ) ) {
			array_push ( $generoEscore, $row );
		}		
		$generoTot = 0;
		for($i = 0; $i < count ( $generoEscore ); $i ++) {
			$generoTot = $generoEscore [$i] [1] + $generoTot;
		}
		for($i = 0; $i < count ( $generoEscore ); $i ++) {
			$generoEscore [$i] [1] = $generoEscore [$i] [1] / $generoTot;
		}		
		$array = array ();
		while ( $row = mysqli_fetch_array ( $result ) ) {
			array_push ( $array, $row );
		}		
		$escorePerfil = array ();	
		for($i = 0; $i < $numArray; $i ++) {
			$escorePerfil [$i] = 0;
			for($j = 0; $j < count ( $autorEscore ); $j ++) {
				if ($array [$i] ['idAutor'] == $autorEscore [$j] [0]) {
					$escorePerfil [$i] = $autorEscore [$j] [1] / 2;
				}
			}
			for($j = 0; $j < count ( $generoEscore ); $j ++) {
				if ($array [$i] ['idGenero'] == $generoEscore [$j] [0]) {
					$escorePerfil [$i] = $escorePerfil [$i] + ($generoEscore [$j] [1] / 2);
				}
			}			
			$array [$i] ['escorePerfil'] = $escorePerfil [$i];	
		}	
/*		if ($cluster) {
			foreach($array as $desordenado){
				print "id do doc = ".$desordenado[0].", escore = ".$desordenado['escorePerfil'].". \n";
			}
			print " \n";
		}	*/
		foreach($array as $temp_list) {  // ordenando a lista de obras recomendadas por ordem de relevancia
			$sort_aux[] = ($temp_list['escorePerfil']);
		}
		array_multisort($sort_aux, SORT_DESC, $array);
/*		if ($cluster) {
			$somaDeEscores = 0;
			foreach($array as $ordenado){
				print "id do doc = ".$ordenado[0].", escore = ".$ordenado['escorePerfil'].". \n";
				$somaDeEscores+=$ordenado['escorePerfil'];
			}
			print "total de escores de ordenado = $somaDeEscores \n \n";
		} */
		$arrayTop = array();
		$escorePerfilTotal = 0;
		$countdoarray = count($array);
		for /*($i=$offset;$i<$limit;$i++)*/ ($i=0;$i<count($array);$i++){  // pegando a lista com as X obras de maior relevancia para recomendar. 
			array_push ( $arrayTop, $array[$i] );
			$escorePerfilTotal+= $array [$i] ['escorePerfil'];
			if ($i==$limit) {				
				break;
			}
		}
		for($i = 0; $i < count($arrayTop); $i++) {  // normaliza o escore perfil das X obras de maior relevancia para recomendar.
			$arrayTop[$i]['escorePerfil'] = ($arrayTop[$i]['escorePerfil'] / $escorePerfilTotal);
			round($arrayTop[$i]['escorePerfil'], 4);
		}
/*		if ($cluster){
			foreach($arrayTop as $cadaTop){
				print "id do doc = ".$cadaTop[0].", escore = ".$cadaTop['escorePerfil'].". \n";
			}
		} */
		return $arrayTop;	
	
	}
	
public function eh_catarinense($document_id){

		$query = sprintf("SELECT catarinense FROM Autor a
						 JOIN AutorDocumento ad ON ad.Autor_id=a.id
						 AND Documento_id = '%s'", $document_id);

		try {
			$result_sql = $this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		
		$results = $this->DB->parse_result($result_sql);
		
		return $results;

	}

	function calcula_similaridade_autores($result1, $lista2) {		
	//	print "calcula_similaridade_autores \n";
		$soma_a = 0;
		$soma_b = 0;
		$soma_c = 0;
		
		$i = 0;
		$j = 0;
		
	//	$result1 = $DB->query ( $lista1 );
		$result2 = $this->DB->query ( $lista2 );
		
		while ( $linha_lista1 = mysqli_fetch_array ( $result1 ) ) {
			$soma_b = $linha_lista1 [1] * $linha_lista1 [1] + $soma_b;
			$i ++;
			while ( $linha_lista2 = mysqli_fetch_array ( $result2 ) ) {
				$j ++;
				if ($linha_lista1 [0] == $linha_lista2 [0]) {
					$soma_a = $linha_lista1 [1] * $linha_lista2 [1] + $soma_a;
					break;
				}
			}
			mysqli_data_seek ( $result2, 0 );
			$j = 0;
		}
		
		if ($soma_a > 0) {
			
			while ( $linha_lista2 = mysqli_fetch_array ( $result2 ) ) {
				$soma_c = $linha_lista2 [1] * $linha_lista2 [1] + $soma_c;
			}
			$total = $soma_a / sqrt ( $soma_b * $soma_c );
		} else {
			$total = 0;
		}
		mysqli_data_seek ( $result1, 0 );
		return $total;
	}
	
	function calcula_similaridade_generos($result1, $lista2) {		
	//	print "calcula_similaridade_generos \n";
		$soma_a = 0;
		$soma_b = 0;
		$soma_c = 0;
		
	//	$result1 = $DB->query ( $lista1 );
		$result2 = $this->DB->query ( $lista2 );
		
		while ( $linha_lista1 = mysqli_fetch_array ( $result1 ) ) {
			$soma_b = $linha_lista1 [1] * $linha_lista1 [1] + $soma_b;
			while ( $linha_lista2 = mysqli_fetch_array ( $result2 ) ) {
				// Comparação entre os ID dos autores
				if ($linha_lista1 [0] == $linha_lista2 [0]) {
					$soma_a = $linha_lista1 [1] * $linha_lista2 [1] + $soma_a;
					break;
				}
			}
			mysqli_data_seek ( $result2, 0 );
		}
		
		if ($soma_a > 0) {
			while ( $linha_lista2 = mysqli_fetch_array ( $result2 ) ) {
				$soma_c = $linha_lista2 [1] * $linha_lista2 [1] + $soma_c;
			}
			$total = $soma_a / sqrt ( $soma_b * $soma_c );
		} else {
			$total = 0;
		}
		mysqli_data_seek ( $result1, 0 );
		return $total;
	}		
	

	/**
	 * Pega id do tipo da obra atual em que se está inserindo ou editanto.
	 *
	 * @param string $documentTypeName string contendo o nome do tipo da obra em questão.
	 * @return string ID do tipo da obra em questão
	 */
	function get_id_tipo_obra_atual($documentTypeName){		
		
		// Converte tipo para nome de tabela
		
		switch ($documentTypeName) {
			case 'obra-literaria':
				$documentTypeName = 'obraliteraria';
				break;
			case 'audiovisuais':
				$documentTypeName = 'audiovisual';
				break;
		}
		
		$query = sprintf("SELECT 
								id	
							FROM 
								TipoDocumento 
							WHERE 
								tabela = '%s'",
						 $documentTypeName);
			try {
				$result_sql = $this->DB->query($query);							
			}
			catch (Exception $e) {
				Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
				return false;
			}
		$results_currentIdType = $this->DB->parse_result($result_sql);
		return $results_currentIdType[0]['id'];
	}

/*
	function popula_genero_em_DocumentoConsulta($id = ''){
		$query = sprintf("SELECT 
					nome 
				FROM 
					Genero as g, DocumentoGenero as dg, DocumentoConsulta as dc 
				WHERE 
					dc.id = dg.Documento_id AND 
					dg.Genero_id = g.id AND 
					g.TipoDocumento_id = '%s'"),
			mysqlx_real_escape_string($id));

			$this->DB->query($query);
	}

	/* APENAS PARA TESTES
	 $document['ids_autores'] = $this->get_autores($document['id'],  array('id'));
	 $ids_autores='';
	 foreach( $document['ids_autores'] as $a){
	 $ids_autores = current($a);
	 }*/

	//$ids_autores = explode(',', $document['ids_autores']);

}
