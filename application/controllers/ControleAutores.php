<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once (dirname ( __FILE__ ) . '/ControleLocalizacao.php');


class ControleAutores extends DataTables {

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
	 * @param integer $id ID do autor (opcional)
	 * @param array $fields Campos (colunas) da tabela a serem retornados
	 * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
	 * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
	 * @param integer $nome Parte do nome do autor. Utilizado nos campos de auto completar (opcional)
	 * @return array Resultado da pesquisa
	 */
	public function get($id = '', $fields = array(), $start = 0, $limit = 0, $nome = '') {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(',', $fields);
		}
		//Prepara a consulta SQL
		$query = "SELECT $columns FROM Autor";
		//Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
		if ($id) {
			$query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
		}
		//Senão, caso o nome tenha sido especificado, retornará apenas os autores que contém o termo em seu nome
		elseif ($nome) {
			//$query.= sprintf(" WHERE nome_completo LIKE '%%%s%%'", mysqlx_real_escape_string($nome));
			$query.= sprintf(" WHERE nome_completo LIKE '%%%s%%'
            				   OR pseudonimo LIKE '%%%s%%'
                               OR nome_usual LIKE '%%%s%%'",
			mysqlx_real_escape_string($nome),
			mysqlx_real_escape_string($nome),
			mysqlx_real_escape_string($nome));
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
	 * Obtém as fontes literárias de um autor
	 *
	 * @param integer $id ID do Autor
	 * @param array $fields Campos (colunas) da tabela a serem retornados
	 * @return array Resultado da pesquisa
	 */
	public function get_fontes($id, $fields = array()) {
		$columns = '*';
		if ($fields) {
			//Junta os campos/colunas para a consulta SQL
			$columns = implode(',', $fields);
		}
		//Prepara a consulta SQL
		$query = sprintf("SELECT t1.$columns
                          FROM Fonte t1
                          JOIN AutorFonte t2
                          ON (t1.id = t2.Fonte_id)
                          WHERE t2.Autor_id='%s'", 
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
	 * Obtém os documentos associados ao autor
	 *
	 * @param integer $id ID do Autor
	 * @return array Resultado da pesquisa
	 */
	public function get_documentos($id) {
		$columns = '*';
		
		$query = sprintf("SELECT dc.id,
        						 dc.titulo,
        						 dc.nome_tipodocumento AS tipo,
        						 dc.TipoDocumento_id,
                                 dc.nome_genero AS genero,
                                 dc.nome_categoria AS categoria,
                                 dc.ano_producao,
                                 dc.ano_publicacao_inicio,
                                 dc.seculo_producao,
                                 dc.seculo_publicacao,
                                 dc.ano_documento,
                                 dc.seculo_documento,
                                 dc.midias
                          FROM DocumentoConsulta dc      
                          JOIN AutorDocumento ad
                           ON (dc.id = ad.Documento_id)
                          WHERE ad.Autor_id='%s'
                          ORDER BY dc.titulo ASC", 
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
	 * Adiciona o Autor no banco de dados
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	public function add($nome_completo, $pseudonimo = '', $nome_usual = '', $ano_nascimento = '', $seculo_nascimento = '',
						$cidade_nascimento, $estado_nascimento, $pais_nascimento, $detalhes_nasc='', $ano_morte = '',
						$seculo_morte = '', $cidade_morte, $estado_morte, $pais_morte, $detalhes_morte='', $fontes = array(),
						$catarinense = false, $piauiense = false, $descricao = '', $sexo = '') {
		

		if (!Auth::check()) {
			return json_encode(array('error' => 'Acesso negado'));
		}

		//Define os séculos caso o ano tenha sido especificado
		if ($ano_nascimento) {
			$seculo_nascimento = get_roman_century($ano_nascimento);
		}
		if ($ano_morte) {
			$seculo_morte = get_roman_century($ano_morte);
		}

		//Validação
		$invalid_fields = $this->validate_parameters($nome_completo, $fontes, $catarinense, $piauiense, $sexo);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}

		//Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['nome_completo'] = $nome_completo;
		$fields['pseudonimo'] = $pseudonimo;
		$fields['nome_usual'] = $nome_usual ? $nome_usual : $nome_completo;
		$fields['ano_nascimento'] = $ano_nascimento;
		$fields['seculo_nascimento'] = $seculo_nascimento;
		
		$fields['cidade_nasc_id'] = $cidade_nascimento;
		$fields['estado_nasc_id'] = $estado_nascimento;
		$fields['pais_nasc_id'] = $pais_nascimento;
		$fields['detalhes_nasc'] = $detalhes_nasc;

		$fields['cidade_morte_id'] = $cidade_morte;
		$fields['estado_morte_id'] = $estado_morte;
		$fields['pais_morte_id'] = $pais_morte;
		$fields['detalhes_morte'] = $detalhes_morte;
			
		$fields['ano_morte'] = $ano_morte;
		$fields['seculo_morte'] = $seculo_morte;
		$fields['catarinense'] = $catarinense;
		$fields['piauiense'] = $piauiense;
		$fields['descricao'] = $descricao;
		$fields['sexo'] = $sexo;
		
		//Log:
		$fields['Usuario_id'] = $_SESSION['id'];
		$fields['data_inclusao'] = date('Y-m-d h:i:s');
		
		/* Versão antiga:
		$controle_localizacao = ControleLocalizacao::getInstance();
		
		$local_nascimento='';
		
		if ($cidade_nascimento) {
			$fields['loc_nasc'] = $controle_localizacao->getNomeCidade($cidade_nascimento);
			$local_nascimento = $fields['loc_nasc'].", ";
		}
		if ($estado_nascimento) {
			$fields['regiao_nasc'] = $controle_localizacao->getSiglaEstado($estado_nascimento);
			$local_nascimento = $local_nascimento.$fields['regiao_nasc'].", ";
		}
		if ($pais_nascimento) {
			$fields['pais_nasc'] = $controle_localizacao->getNomePais($pais_nascimento);
			$local_nascimento = $local_nascimento.$fields['pais_nasc'];
		}
		$fields['local_nascimento'] = $local_nascimento;
		
		$local_morte='';
		
		if ($cidade_morte) {
			$fields['loc_morte'] = $controle_localizacao->getNomeCidade($cidade_morte);
			$local_morte = $fields['loc_morte'].", ";
		}
		if ($estado_morte) {
			$fields['regiao_morte'] = $controle_localizacao->getSiglaEstado($estado_morte);
			$local_morte = $local_morte.$fields['regiao_morte'].", ";
		}
		if ($pais_morte) {
			$fields['pais_morte'] = $controle_localizacao->getNomePais($pais_morte);
			$local_morte = $local_morte.$fields['pais_morte'];
		}
		$fields['local_morte'] = $local_morte;
		
		*/

		try {
			$this->DB->insert('Autor', $fields, false);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
		}

		$id = $this->DB->get_last_id();

		foreach ($fontes as $fonte) {
			//Inserção dos relacionamentos no Banco de Dados
			$query = sprintf("INSERT INTO AutorFonte (Autor_id, Fonte_id)
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

		//Nenhum erro ocorreu. Retorna nulo.
		return json_encode(array('error' => null, 'id' => $id));
	}

	/**
	 * Atualiza o Autor no banco de dados
	 *
	 * @return string Resultado da inserção no formato JSON
	 */
	public function update($id, $nome_completo, $pseudonimo = '', $nome_usual = '', $ano_nascimento = '', $seculo_nascimento = '',
						   $cidade_nascimento, $estado_nascimento, $pais_nascimento, $detalhes_nasc='', $ano_morte = '',
						   $seculo_morte = '', $cidade_morte, $estado_morte, $pais_morte, $detalhes_morte='', $fontes = array(),
						   $catarinense = false, $piauiense = false, $descricao = '', $sexo = '') {
		
		if (!Auth::check()) {
			return json_encode(array('error' => 'Acesso negado'));
		}
		
		$autor = $this->get($id)[0];
		

		//Define os séculos caso o ano tenha sido especificado
		if ($ano_nascimento) {
			$seculo_nascimento = get_roman_century($ano_nascimento);
		}
		if ($ano_morte) {
			$seculo_morte = get_roman_century($ano_morte);
		}

		//Validação
		if (!$id) {
			return json_encode(array('error' => 'ID não especificado'));
		}
		$invalid_fields = $this->validate_parameters($nome_completo, $fontes, $catarinense, $piauiense, $sexo);
		if ($invalid_fields) {
			return json_encode(array('error' => $invalid_fields));
		}

		//Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
		$fields = array();
		$fields['nome_completo'] = $nome_completo;
		$fields['pseudonimo'] = $pseudonimo;
		$fields['nome_usual'] = $nome_usual ? $nome_usual : $nome_completo;
		$fields['ano_nascimento'] = $ano_nascimento;
		$fields['seculo_nascimento'] = $seculo_nascimento;
		
		$fields['cidade_nasc_id'] = $cidade_nascimento;
		$fields['estado_nasc_id'] = $estado_nascimento;
		$fields['pais_nasc_id'] = $pais_nascimento;
		$fields['detalhes_nasc'] = $detalhes_nasc;
		
		
		$fields['cidade_morte_id'] = $cidade_morte;
		$fields['estado_morte_id'] = $estado_morte;
		$fields['pais_morte_id'] = $pais_morte;
		$fields['detalhes_morte'] = $detalhes_morte;
		

		$fields['ano_morte'] = $ano_morte;
		$fields['seculo_morte'] = $seculo_morte;
		$fields['catarinense'] = $catarinense;
		$fields['piauiense'] = $piauiense;
		$fields['descricao'] = $descricao;
		$fields['sexo'] = $sexo;
		
		/* Versão antiga
		 
		$controle_localizacao = ControleLocalizacao::getInstance();
		
		$local_nascimento='';
		
		if ($cidade_nascimento) {
			$fields['loc_nasc'] = $controle_localizacao->getNomeCidade($cidade_nascimento);
			$local_nascimento = $fields['loc_nasc'].", ";
		}
		if ($estado_nascimento) {
			$fields['regiao_nasc'] = $controle_localizacao->getSiglaEstado($estado_nascimento);
			$local_nascimento = $local_nascimento.$fields['regiao_nasc'].", ";
		}
		if ($pais_nascimento) {
			$fields['pais_nasc'] = $controle_localizacao->getNomePais($pais_nascimento);
			$local_nascimento = $local_nascimento.$fields['pais_nasc'];
		}
		$fields['local_nascimento'] = $local_nascimento;
		
		$local_morte='';
		
		if ($cidade_morte) {
			$fields['loc_morte'] = $controle_localizacao->getNomeCidade($cidade_morte);
			$local_morte = $fields['loc_morte'].", ";
		}
		if ($estado_morte) {
			$fields['regiao_morte'] = $controle_localizacao->getSiglaEstado($estado_morte);
			$local_morte = $local_morte.$fields['regiao_morte'].", ";
		}
		if ($pais_morte) {
			$fields['pais_morte'] = $controle_localizacao->getNomePais($pais_morte);
			$local_morte = $local_morte.$fields['pais_morte'];
		}
		$fields['local_morte'] = $local_morte;
		
		*/
		//Log:
		$fields['Usuario_id'] = $_SESSION['id'];
		$fields['data_atualizacao'] = date('Y-m-d h:i:s');
		
		$where = "id = '" . mysqlx_real_escape_string($id) . "'";

		try {
			$this->DB->update('Autor', $fields, $where);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage(), __FILE__);
			return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
		}

		//Exclui os antigos relacionamentos entre Autor e Fonte
		$query = sprintf("DELETE FROM AutorFonte
                          WHERE Autor_id = '%s'",
		mysqlx_real_escape_string($id));
		try {
			$this->DB->query($query);
			foreach ($fontes as $fonte) {
				//Inserção dos relacionamentos no Banco de Dados
				$query = sprintf("INSERT INTO AutorFonte (Autor_id, Fonte_id)
                                  VALUES('%s', '%s')",
				mysqlx_real_escape_string($id),
				mysqlx_real_escape_string($fonte));
				$this->DB->query($query);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Ocorreu um erro ao armazenar as fontes'));
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

		if (!Auth::check()) {
			return json_encode(array('error' => 'Acesso negado'));
		}
		if (!is_array($ids) || !$ids) {
			return json_encode(array('error' => 'IDs inválidos'));
		}

		//Exclui do banco de dados
		$ids_str = implode("','", $ids);
		$query = sprintf("DELETE FROM Autor
                          WHERE id IN ('%s')",
		$ids_str);
		try {
			$this->DB->query($query);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Autor não pode ser excluído pois possui documento cadastrado'));
		}

		//Nenhum erro ocorreu. Retorna nulo.
		return json_encode(array('error' => null));
	}

	/**
	 * Manipula os resultados da pesquisa no banco de dados para os dados da tabela
	 *
	 * @override (DataTables):
	 * @param resource $rResult Resultado da consulta
	 * @return array Matriz com os dados da tabela
	 */
	protected function fnParseResult($rResult) {
		$aaData = array();
		$sIndex = 0;
		while ($aRow = mysqli_fetch_array($rResult)) {
			$aaData[$sIndex] = array();
			$sId = array_shift($aRow);
			$aaData[$sIndex][] =  '<input type="checkbox" name="ids[]" value="'.$sId.'" />';
			array_shift($aRow);
			$aKeys = array_keys($aRow);
			foreach ($aKeys as $sKey) {
				if (!is_numeric($sKey)) {
					if ($aRow[$sKey] === null) {
						$aRow[$sKey] = '';
					}
					if ($sKey == 'nome_usual') {
						$nome = $aRow[$sKey];
						if ($aRow['nome_completo'] && $aRow['nome_completo'] != $nome) {
							$nome.= "<br /><em>{$aRow['nome_completo']}</em>";
						}
						$aaData[$sIndex][] = $nome;
					}
					elseif ($sKey == 'catarinense' && $aRow[$sKey] == 0) {
						$aaData[$sIndex][] = 'Não';
					}
					elseif ($sKey == 'catarinense' && $aRow[$sKey] == 1) {
						$aaData[$sIndex][] = 'Sim';
					}
					else {
						$aaData[$sIndex][] = $aRow[$sKey];
					}
				}
			}
			$sIndex++;
		}
		return $aaData;
	}

	/**
	 * Valida os campos (parâmetros) do Autor
	 *
	 * @return array Campos que não passaram no teste de validação
	 */
	private function validate_parameters($nome_completo, $fontes = array(), $catarinense = false, $piauiense = false, $sexo = false) {
		$invalid_fields = array();
		if (!$nome_completo) {
			$invalid_fields['nome_completo'] = 'O nome não pode ser vazio';
		}
		if (!$fontes){
			$invalid_fields['fontes'] = 'É necessário haver ao menos uma fonte';
		}
		elseif ($fontes && !is_array($fontes)) {
			$invalid_fields['fontes'] = 'Fontes inválidas';
		}
		if (!is_bool($catarinense)) {
			$invalid_fields['catarinense'] = 'Status catarinense inválido';
		}
		if (!is_bool($piauiense)) {
			$invalid_fields['piauiense'] = 'Status piauiense inválido';
		}
		if (!$sexo) {
			$invalid_fields['sexo'] = 'Selecione o sexo';
		}
		elseif ($sexo != 'M' && $sexo != 'F' && $sexo != 'I') {
			$invalid_fields['sexo'] = 'Sexo inválido';
		}
		return $invalid_fields;
	}

	public function getAnoSeculo($seculo){
		//essa funcao retorna o ano de inicio de um seculo. ex: getAnoSeculo(XV) = 1401
		//para usar o perido basta adicionar 99 no resultado e vc tem o periodo de um seculo= 1401-1500
		$array_sec = array(0 => "I", 1 => "II", 2 => "III", 3 => "IV", 4 => "V", 5 => "VI",
		6 => "VII", 7 => "VIII", 8 => "IX", 9 => "X", 10 => "XI",
		11 => "XII", 12 => "XIII", 13 => "XIV", 14 => "XV", 15 => "XVI",
		16 => "XVII", 17 => "XVIII", 18 => "XIX", 19 => "XX", 20 => "XXI",
		21 => "XXII", 22 => "XXIII");


		while(strcmp($seculo,current($array_sec))!=0){
			next($array_sec);
		}

		$ano=key($array_sec);
		$ano = ($ano*100)+1;

		return $ano;

	}

	public function getSeculoAno($ano){
		//essa funcao retorna seculo referente ao ano passado ex: 1940 = XX
		$array_sec = array(0 => "I", 1 => "II", 2 => "III", 3 => "IV", 4 => "V", 5 => "VI",
		6 => "VII", 7 => "VIII", 8 => "IX", 9 => "X", 10 => "XI",
		11 => "XII", 12 => "XIII", 13 => "XIV", 14 => "XV", 15 => "XVI",
		16 => "XVII", 17 => "XVIII", 18 => "XIX", 19 => "XX", 20 => "XXI",
		21 => "XXII", 22 => "XXIII");

		//pois se for 1400 é SEC XIV, entao volta um ano para facilitar calculo
		if($ano > 1){
			$ano = $ano-1;
		}

		$ano = intval($ano/100);


		while(key($array_sec)!=$ano){
			next($array_sec);
		}

		$seculo=current($array_sec);

		return $seculo;

	}

	public function getPaises(){
		$query = "SELECT nome FROM Paises p";
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
	
	public function getDistinctRegNasc(){
		$query = "SELECT DISTINCT en.id as regiao_id, en.nome as regiao_nasc FROM Autor a left join estados en on (a.estado_nasc_id=en.id) WHERE a.estado_nasc_id IS NOT NULL  ORDER BY en.nome;";
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

	public function getDistinctRegMorte(){
		$query = "SELECT DISTINCT en.id as regiao_id, en.nome as regiao_morte FROM Autor a left join estados en on (a.estado_morte_id=en.id) WHERE a.estado_morte_id IS NOT NULL  ORDER BY en.nome;";
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

	public function getDistinctPaisNasc(){
		$query = "SELECT DISTINCT pn.id as pais_id, pn.nome as pais_nasc FROM Autor a left join Paises pn on (a.pais_nasc_id=pn.id) WHERE a.pais_nasc_id IS NOT NULL  ORDER BY pn.nome;";
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

	public function getDistinctPaisMorte(){
		$query = "SELECT DISTINCT pm.id as pais_id, pm.nome as pais_morte FROM Autor a left join Paises pm on (a.pais_morte_id=pm.id) WHERE a.pais_morte_id IS NOT NULL  ORDER BY pm.nome;";
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
	
	public function getCidade($tipo, $id) {
		
		//Prepara a consulta SQL
		if ($tipo==0) 
		$query = sprintf("SELECT t1.id
				FROM cidades t1
				JOIN Autor t2
				ON (t1.id = t2.cidade_nasc_id)
				WHERE t2.id='%s'",
				mysqlx_real_escape_string($id));
		else 
			$query = sprintf("SELECT t1.id
					FROM cidades t1
					JOIN Autor t2
					ON (t1.id = t2.cidade_morte_id)
					WHERE t2.id='%s'",
					mysqlx_real_escape_string($id));
		try {
			
			$result_sql = $this->DB->query($query);
			$resposta = $this->DB->parse_result($result_sql);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		if ($resposta)
			return $resposta[0];
		else 
			return NULL;
	}
	
	public function getEstado($tipo, $id) {
		
		//Prepara a consulta SQL
		if ($tipo==0)
			$query = sprintf("SELECT t1.id
					FROM estados t1
					JOIN Autor t2
					ON (t1.id = t2.estado_nasc_id)
					WHERE t2.id='%s'",
					mysqlx_real_escape_string($id));
		else
			$query = sprintf("SELECT t1.id
					FROM estados t1
					JOIN Autor t2
					ON (t1.id = t2.estado_morte_id)
					WHERE t2.id='%s'",
					mysqlx_real_escape_string($id));
		try {
			$result_sql = $this->DB->query($query);
			$resposta = $this->DB->parse_result($result_sql);
				
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		if ($resposta)
			return $resposta[0];
		else 
			return NULL;
	}
	
	public function getPais($tipo, $id) {
		//Prepara a consulta SQL
		if ($tipo==0)
			$query = sprintf("SELECT t1.id, t1.nome
					FROM Paises t1
					JOIN Autor t2
					ON (t1.id = t2.pais_nasc_id)
					WHERE t2.id='%s'",
					mysqlx_real_escape_string($id));
		else
			$query = sprintf("SELECT t1.id, t1.nome
					FROM Paises t1
					JOIN Autor t2
					ON (t1.id = t2.pais_morte_id)
					WHERE t2.id='%s'",
					mysqlx_real_escape_string($id));
		try {
			$result_sql = $this->DB->query($query);
			$resposta = $this->DB->parse_result($result_sql);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		if ($resposta)
			return $resposta[0];
		else 
			return NULL;
	}
	
	public function getLocNasc($id) {
		$controle_localizacao = ControleLocalizacao::getInstance();
		$loc_nasc=NULL;
		$query = sprintf("Select cidade_nasc_id, estado_nasc_id, pais_nasc_id FROM Autor WHERE id='%s'",
		mysqlx_real_escape_string($id));
		try {
			$result_sql = $this->DB->query($query);
			$resposta = mysqli_fetch_array($result_sql);
			if ($resposta['cidade_nasc_id']!=0) 
				$loc_nasc = $controle_localizacao->getCidadeEstadoString($resposta['cidade_nasc_id']);
			else if ($resposta['estado_nasc_id']!=0) 
				$loc_nasc = $controle_localizacao->getEstadoString($resposta['estado_nasc_id']);
			else if ($resposta['pais_nasc_id']!=0) 
				$loc_nasc = $controle_localizacao->getNomePais($resposta['pais_nasc_id']);
			return $loc_nasc;
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
	}
	
	public function getLocMorte($id) {
		$controle_localizacao = ControleLocalizacao::getInstance();
		$loc_morte=NULL;
		$query = sprintf("Select cidade_morte_id, estado_morte_id, pais_morte_id FROM Autor WHERE id='%s'",
				mysqlx_real_escape_string($id));
		try {
			$result_sql = $this->DB->query($query);
			$resposta = mysqli_fetch_array($result_sql);
			if ($resposta['cidade_morte_id']!=0)
				$loc_morte = $controle_localizacao->getCidadeEstadoString($resposta['cidade_morte_id']);
			else if ($resposta['estado_morte_id']!=0)
				$loc_morte = $controle_localizacao->getEstadoString($resposta['estado_morte_id']);
			else if ($resposta['pais_morte_id']!=0)
				$loc_morte = $controle_localizacao->getNomePais($resposta['pais_morte_id']);
			return $loc_morte;
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
	}
	public function getEstatGenero($id) {
		$query = sprintf("SELECT DISTINCT g.nome as label, count(g.nome) as value
					FROM Genero g
					JOIN DocumentoGenero dg 
						ON (dg.Genero_id = g.id) 
					JOIN AutorDocumento ad 
						ON (ad.Documento_id = dg.Documento_id) 
					WHERE ad.Autor_id = '%s' GROUP BY g.nome",
				mysqlx_real_escape_string($id));
		try {
			$resposta = $this->DB->query($query);
			$data = array();
			for ($x = 0; $x < mysqli_num_rows($resposta); $x++) {
				$data[] = mysqli_fetch_assoc($resposta);
			}
			// return htmlspecialchars(json_encode($data, JSON_NUMERIC_CHECK),ENT_QUOTES, 'UTF-8');
			return json_encode($data, JSON_NUMERIC_CHECK);
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
	}
}
