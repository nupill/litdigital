<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleComentarios extends DataTables {
	
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
     * Pesquisa e retorna os comentários de autor ou documento do banco de dados
     * 
     * @param integer $id_autor ID do autor
     * @param integer $id_documento ID do documento
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
    private function get($id_autor = '', $id_documento = '', $fields = array(), $start = 0, $limit = 0) {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }
        //Prepara a consulta SQL
        $query = "SELECT c.$columns, SUM(cv.tipo) AS score, u.nome AS usuario
        		  FROM Comentario c
        		  LEFT JOIN ComentarioVoto cv
        		   ON (c.id = cv.Comentario_id)
        		  JOIN Usuario u
        		   ON (c.Usuario_id = u.id)";
		
        if ($id_autor) {
        	$query.= sprintf(" WHERE Autor_id='%s'", mysqlx_real_escape_string($id_autor));
        }
        elseif ($id_documento) {
        	$query.= sprintf(" WHERE Documento_id='%s'", mysqlx_real_escape_string($id_documento));
        }
        
        $query.= " GROUP BY c.id
        		   ORDER BY c.data_inclusao DESC";
        
        //Verifica se os parâmetros para limite/paginação foram passados
        if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
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
     * Pesquisa e retorna os comentários de um autor do banco de dados
     *
     * @param integer $id ID do autor
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
    public function get_autor($id, $fields = array(), $start = 0, $limit = 0) {
		return $this->get($id, null, $fields, $start, $limit);
    }
    
    /**
     * Pesquisa e retorna os comentários de um documento do banco de dados
     *
     * @param integer $id ID do documento
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
    public function get_documento($id, $fields = array(), $start = 0, $limit = 0) {
    	return $this->get(null, $id, $fields, $start, $limit);
    }
    
    /**
     * Pesquisa e retorna os votos de um comentário de autor ou documento do banco de dados
     *
     * @param integer $id_autor ID do autor
     * @param integer $id_documento ID do documento
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
    private function get_votos($id_autor = '', $id_documento = '', $fields = array(), $start = 0, $limit = 0) {
    	$columns = '*';
    	if ($fields) {
    		//Junta os campos/colunas para a consulta SQL
    		$columns = implode(',', $fields);
    	}
    	//Prepara a consulta SQL
    	$query = "SELECT cv.$columns
    			  FROM ComentarioVoto cv
    			  JOIN Comentario c
			       ON (cv.Comentario_id = c.id)";
    
    	if ($id_autor) {
	    	$query.= sprintf(" WHERE Autor_id='%s'", mysqlx_real_escape_string($id_autor));
	    }
	    elseif ($id_documento) {
	    	$query.= sprintf(" WHERE Documento_id='%s'", mysqlx_real_escape_string($id_documento));
	    }
    
	    //Verifica se os parâmetros para limite/paginação foram passados
	    if (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
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
     * Pesquisa e retorna os votos de comentários de um autor do banco de dados
     *
     * @param integer $id ID do autor
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
    public function get_votos_autor($id, $fields = array(), $start = 0, $limit = 0) {
    	return $this->get_votos($id, null, $fields, $start, $limit);
    }
    
    /**
     * Pesquisa e retorna os votos de comentários de um documento do banco de dados
     *
     * @param integer $id ID do documento
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
    public function get_votos_documento($id, $fields = array(), $start = 0, $limit = 0) {
    	return $this->get_votos(null, $id, $fields, $start, $limit);
    }
    	
	/**
     * Adiciona o Comentário no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    private function add($id_autor, $id_documento, $conteudo, $titulo = '', $id_comentario = null) {
        
        if (!Auth::check()) {
            return json_encode(array('error' => 'É necessário se logar para fazer comentários'));
        }
        
        //Validação
        if (!$id_autor && !$id_documento) {
        	return json_encode(array('error' => 'É necessário informar o ID do autor ou documento'));
        }
        if ($id_comentario == null && !$titulo) {
        	return json_encode(array('error' => 'Informe o título do comentário'));
        }
        if (!$conteudo) {
        	return json_encode(array('error' => 'Informe o conteúdo do comentário'));
        }
        
        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['titulo'] = $titulo;
        $fields['conteudo'] = substr($conteudo, 0, 4000);
        $fields['Usuario_id'] = $_SESSION['id'];
        if ($id_documento) {
        	$fields['Documento_id'] = $id_documento;
        }
        if ($id_autor) {
        	$fields['Autor_id'] = $id_autor;
        }
        $operacao = "Novo Comentário";
        if ($id_comentario) {
        	$fields['Comentario_id'] = $id_comentario;
        	$operacao = "Resposta";
        }
        
        try {         
            $this->DB->insert('Comentario', $fields, false);
            $id=$this->DB->get_last_id();
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }
        
        $this->notify($operacao, $id, '');
        
        //Nenhum erro ocorreu. Retorna nulo + dados do comentário cadastrado
        return json_encode(array('error' => null,
        						 'id' => $id,
        						 'usuario' => $_SESSION['nome'],
        						 'titulo' => $titulo,
        						 'conteudo' => $conteudo,
        						 'data_inclusao' => date('d/m/Y H:i:s')));
    }
    
    /**
     * Adiciona o Comentário de autor no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function add_autor($id, $conteudo, $titulo = '') {
    	return $this->add($id, null, $conteudo, $titulo);
    }
    
    /**
     * Adiciona o Comentário de documento no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function add_documento($id, $conteudo, $titulo = '') {
    	return $this->add(null, $id, $conteudo, $titulo);
    }
    
    /**
     * Adiciona a resposta ao Comentário de autor no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function reply_autor($id, $conteudo, $id_comentario) {
    	return $this->add($id, null, $conteudo, null, $id_comentario);
    }
    
    /**
     * Adiciona a resposta ao Comentário de documento no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function reply_documento($id, $conteudo, $id_comentario) {
    	return $this->add(null, $id, $conteudo, null, $id_comentario);
    }
    
    /**
     * Remove os registros do banco de dados
     * 
     * @param array $ids IDs dos registros a serem excluídos
     * @return string Resultado no formato JSON
     */
    public function del($ids) {
        
        if (!Auth::check()) {
            return json_encode(array('error' => 'É necessário se logar para fazer comentários'));
        }
        if (!is_array($ids) || !$ids) {
            return json_encode(array('error' => 'IDs inválidos'));
        }
        
        //Exclui do banco de dados
        $ids_str = implode("','", $ids);
        $query = sprintf("DELETE FROM Comentario
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
     * Remove os registros de denúncias do banco de dados
     *
     * @param array $ids IDs dos registros a serem excluídos
     * @return string Resultado no formato JSON
     */
    public function del_denuncia($ids) {
    
    	if (!Auth::check()) {
    		return json_encode(array('error' => 'É necessário se logar para fazer comentários'));
    	}
    	if (!is_array($ids) || !$ids) {
    		return json_encode(array('error' => 'IDs inválidos'));
    	}
    
    	//Exclui do banco de dados
    	$ids_str = implode("','", $ids);
    	$query = sprintf("DELETE FROM ComentarioDenuncia
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
     * Adiciona o voto de Comentário no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    private function vote($id, $tipo) {
    
    	if (!Auth::check()) {
    		return json_encode(array('error' => 'É necessário se logar para fazer comentários'));
    	}
    
    	//Validação
    	if (!$id) {
    		return json_encode(array('error' => 'ID do comentário não especificado'));
    	}
    	if ($tipo !== -1 & $tipo !== 1) {
    		return json_encode(array('error' => 'O tipo deve ser -1 (negativo) ou 1 (positivo)'));
    	}

    	/* Condições:
    	1) Se já votou do mesmo tipo, remove (toggle)
    	2) Se já votou mas tipo diferente, remove anterior e insere novo
    	3) Se não votou, insere
    	*/
    	
    	//Verifica se o usuário já votou nesse comentário
    	$query = sprintf("SELECT tipo
		    			  FROM ComentarioVoto
     					  WHERE Comentario_id = '%s'
    			           AND Usuario_id = '%s'",
    					 mysqlx_real_escape_string($id),
    					 $_SESSION['id']);
    	try {
    		$result = $this->DB->query($query);
    		$voto_anterior = $this->DB->parse_result($result);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
    	}
    	
    	//Exclui o voto
    	if ($voto_anterior) {
    		$query = sprintf("DELETE FROM ComentarioVoto
    		    			  WHERE Comentario_id = '%s'
    		    			   AND Usuario_id = '%s'",
    		    			 mysqlx_real_escape_string($id),
    		    			 $_SESSION['id']);
    		try {
    			$this->DB->query($query);
    		}
    		catch (Exception $e) {
    			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		    return json_encode(array('error' => 'Erro ao excluir no banco de dados'));
    		}	
    	}
    	
    	// Registra o voto
    	if (!$voto_anterior || (isset($voto_anterior[0]['tipo']) && $voto_anterior[0]['tipo'] != $tipo)) {
    		
	    	//Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
	    	$fields = array();
	    	$fields['Comentario_id'] = $id;
	    	$fields['Usuario_id'] = $_SESSION['id'];
	    	$fields['tipo'] = $tipo;
	    	
	    	try {
	    		$this->DB->insert('ComentarioVoto', $fields, false);
	    	}
	    	catch (Exception $e) {
	    		Logger::log($e->getMessage(), __FILE__);
	    		return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
	    	}
	    }
    	
    	//Pesquisa a soma de votos deste comentário
    	$query = sprintf("SELECT SUM(tipo) AS score
    					  FROM ComentarioVoto
			    		  WHERE Comentario_id = '%s'",
			    		  mysqlx_real_escape_string($id));
    	try {
    		$result = $this->DB->query($query);
    		$score = mysqlx_result($result, 0, 'score');
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
    	}
    	
    	if (!$score) {
    		$score = '0';
    	}
    	
    	//Nenhum erro ocorreu. Retorna nulo.
    	return json_encode(array('error' => null, 'score' => $score));
    }
    
    /**
     * Adiciona o voto positivo de Comentário no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function vote_up($id) {
    	return $this->vote($id, 1);
    }
    
    /**
     * Adiciona o voto negativo de Comentário no banco de dados
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function vote_down($id) {
    	return $this->vote($id, -1);
    }
    
    /**
     * Reporta a denúncia de um comentário inapropriado
     *
     * @return string Resultado da inserção no formato JSON
     */
    public function flag($id, $motivo) {
    	
    	if (!Auth::check()) {
    		return json_encode(array('error' => 'É necessário se logar para fazer comentários'));
    	}
    	if (!$id) {
    		return json_encode(array('error' => 'ID não especificado'));
    	}
    	
    	//Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
    	$fields = array();
    	$fields['Comentario_id'] = $id;
    	$fields['Usuario_id'] = $_SESSION['id'];
    	$fields['motivo'] = $motivo;
    	
    	try {
    		$this->DB->insert('ComentarioDenuncia', $fields, false);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage(), __FILE__);
    		return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
    	}
    	
    	$this->notify('Denúncia', $id, $motivo);
    	return json_encode(array('error' => null));
    }
    
    
    /**
     * Sobrescreve função para criar a consulta SQL de busca
     */
    protected function fnBuildQuery($aParams) {
    	/* Query statements */
    	$aClauses = $this->fnBuildQueryClauses($aParams);
    	
    	switch ($aParams['iType']) {
    		// Autor
    		case 1:
    			/* Query */
    			$sQuery = "SELECT SQL_CALC_FOUND_ROWS c.id, c.Comentario_id, c.Autor_id AS Objeto_id, c.data_inclusao, a.nome_usual AS autor, c.titulo, u.nome AS usuario
    					   FROM Comentario c
		    			   JOIN Autor a
		    			    ON (c.Autor_id = a.id)
		    			   JOIN Usuario u
		    			    ON (c.Usuario_id = u.id)
		    			   {$aClauses['sWhere']}
		    			   {$aClauses['sOrder']}
		    			   {$aClauses['sLimit']}";
    			break;
    			
    		// Documento
    		case 2:
    			/* Query */
    			$sQuery = "SELECT SQL_CALC_FOUND_ROWS c.id, c.Comentario_id, c.Documento_id AS Objeto_id, c.data_inclusao, d.titulo AS documento, c.titulo, u.nome AS usuario
		    			   FROM Comentario c
		    		   	   JOIN Documento d
		    			    ON (c.Documento_id = d.id)
		    			   JOIN Usuario u
		    			    ON (c.Usuario_id = u.id)
		    			   {$aClauses['sWhere']}
		    			   {$aClauses['sOrder']}
		    			   {$aClauses['sLimit']}";
    			break;
    			
    		// Denúncias
    		case 3:
    			/* Query */
    			$sQuery = "SELECT SQL_CALC_FOUND_ROWS cd.id, c.titulo, cd.motivo, u.nome AS usuario
			    		   FROM ComentarioDenuncia cd
			    		   JOIN Comentario c
			    		    ON (cd.Comentario_id = c.id)
			    		   JOIN Usuario u
		    			    ON (cd.Usuario_id = u.id)
			    		   {$aClauses['sWhere']}
			    		   {$aClauses['sOrder']}
			    		   {$aClauses['sLimit']}";
    			break;
    	}
    	
    	return $sQuery;
    }
    
    protected function fnParseResult($rResult) {
    	$aaData = array();
    	$iIndex = 0;
    	$aTitles = array();
    	while ($aRow = mysqli_fetch_array($rResult))	{
    		$aaData[$iIndex] = array();
    		$aTitles[$aRow['id']] = $aRow['titulo'];
    		$aKeys = array_keys($aRow);
    		foreach ($aKeys as $sKey) {
    			if (!is_numeric($sKey)) {
    				if ($aRow[$sKey] === null) {
    					$aRow[$sKey] = '';
    				}
    				$aaData[$iIndex][] = $aRow[$sKey];
    			}
    		}
    		$iIndex++;
    	}
    	
    	// Verifica se houveram resultados
    	if (!$aaData) {
    		return $aaData;
    	}
    	// Ou se são resultados da busca por denuncias
    	elseif (sizeof($aaData[0]) == 4) {
    		foreach ($aaData as $iIndex=>$aData) {
    			$aaData[$iIndex][0] = '<input type="checkbox" name="ids[]" value="'.$aData[0].'" id="'.$aData[2].'" />';
    		}
    		return $aaData;
    	}
    	
    	//Obtem a soma de votos dos comentários
    	$query = "SELECT Comentario_id, SUM(tipo) AS score
    			  FROM ComentarioVoto
				  GROUP BY Comentario_id";
    	try {
    		$result = $this->DB->query($query);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
    	}
    	$aScores = array();
    	while ($row = mysqli_fetch_array($result))	{
    		$aScores[$row['Comentario_id']] = $row['score'];
    	}
    	
    	//Obtem as denúncias de um comentário
    	$query = "SELECT Comentario_id, COUNT(*) AS denuncias
    			  FROM ComentarioDenuncia
				  GROUP BY Comentario_id";
    	try {
    		$result = $this->DB->query($query);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
    	}
    	$aDenuncias = array();
    	while ($row = mysqli_fetch_array($result))	{
    		$aDenuncias[$row['Comentario_id']] = $row['denuncias'];
    	}
    	
		foreach ($aaData as $iIndex=>$aData) {
			// Adiciona o checkbox
			$aaData[$iIndex][0] = '<input type="checkbox" name="ids[]" value="'.$aData[0].'" id="'.$aData[2].'" />';
			// Verifica se é uma resposta (ou seja, se Comentario_id não é nulo)
			if ($aData[1]) {
				$aaData[$iIndex][5] = $aTitles[$aData[1]] . ': Resposta';
			}
			// Adiciona o score
			$aaData[$iIndex][] = isset($aScores[$aData[0]]) ? $aScores[$aData[0]] : 0;
			$aaData[$iIndex][] = isset($aDenuncias[$aData[0]]) ? $aDenuncias[$aData[0]] : 0;
			// Remove o id do comentário pai (1) e o id do autor/documento (2)
			array_splice($aaData[$iIndex], 1, 2);
		}
		
    	return $aaData;
    }
    
    private function notify($operacao,$id,$motivo) {
    	global $config;
    	 
    	if (isset($config['follow_email'])) {
    	require_once(APPLICATION_PATH . "/include/PHPMailer/PHPMailerAutoload.php");
    
    	//Obtem os dados do comentário
    	$query = sprintf("SELECT c.titulo, c.conteudo, c.Documento_id, c.Autor_id, u.nome, u.email
		    			  FROM Comentario c
    					  JOIN Usuario u
    					    ON (c.Usuario_id = u.id)
		    			  WHERE c.id = '%s'",
    			mysqlx_real_escape_string($id));
    	try {
    		$result = $this->DB->query($query);
    		$info = $this->DB->parse_result($result);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return json_encode(array('error' => 'Erro ao pesquisar no banco de dados'));
    	}
    	 
    	if (!$info) {
    		return json_encode(array('error' => 'Erro ao notificar comentário'));
    	}
    	 
    	$info = $info[0];
    	 
    	$url = $info['Documento_id'] ? (DOCUMENTOS_URI . '?id=' . $info['Documento_id']) : (AUTORES_URI . '?id=' . $info['Autor_id']);
    	
    	if ($operacao=='Denúncia') {
    		$subject = 'Denúncia de comentário (' . $id . ')';
    		$message = 'O seguinte comentário foi reportado como inapropriado:<br /><br />' .
    			'<b>Autor:</b> ' . $info['nome'] . '<br />' .
    			'<b>Título:</b> ' . $info['titulo'] . '<br />' .
    			'<b>Conteúdo:</b> ' . $info['conteudo'] . '<br />' .
    			'<b>Autor da denúncia:</b> ' . $_SESSION['nome'] . ' (' . $_SESSION['login'] . ')' . '<br />' .
    			'<b>Motivo:</b> ' . ($motivo ? $motivo : 'Não especificado') . '<br />' .
    			'<b>Localização do comentário:</b> ' . $url;
    	} else if ($operacao=='Novo Comentário') {
    		$subject = 'Novo comentário (' . $id . ')';
    		$message = 'O seguinte comentário feito:<br /><br />' .
    				'<b>Autor:</b> ' . $info['nome'] . '<br />' .
    				'<b>Título:</b> ' . $info['titulo'] . '<br />' .
    				'<b>Conteúdo:</b> ' . $info['conteudo'] . '<br />' .
    				'<b>Localização do comentário:</b> ' . $url;
    	} else if ($operacao=='Resposta') {
    		$subject = 'Resposta de comentário (' . $id . ')';
    		$message = 'A seguinte resposta foi postada:<br /><br />' .
    				'<b>Autor:</b> ' . $info['nome'] . '<br />' .
    				'<b>Título:</b> ' . $info['titulo'] . '<br />' .
    				'<b>Conteúdo:</b> ' . $info['conteudo'] . '<br />' .
    				'<b>Localização do comentário:</b> ' . $url;
    	}
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
    		if (!mail($config['flag_email'], $subject, $message)) {
    			Logger::log('Erro ao enviar email de denúncia de comentário ' . $id, __FILE__);
    			return json_encode(array('error' => 'Erro ao reportar a denúncia'));
    		}
    	}
    	}
    }
}