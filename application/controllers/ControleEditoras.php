<?php
require_once (dirname ( __FILE__ ) . '/../include/DB.php');
require_once (dirname ( __FILE__ ) . '/../include/DataTables.php');
require_once (dirname ( __FILE__ ) . '/../include/Logger.php');
require_once (dirname ( __FILE__ ) . '/../include/Auth.php');
require_once (dirname ( __FILE__ ) . '/../include/FirePHPCore/fb.php');
require_once (dirname ( __FILE__ ) . '/ControleLocalizacao.php');

class ControleEditoras extends DataTables {

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

    private function notify($operacao,$nome,$local,$descricao) {
    	require_once(APPLICATION_PATH . "/include/PHPMailer/PHPMailerAutoload.php");
    	global $config;
    
    	if (isset($config['follow_email'])) {
    
    		$subject = 'Cadastramento de Editoras: '.$operacao.' de editora';
    		$message = 'A seguinte editora foi alterada:<br /><br />' .
    				'<b>Fonte:</b> ' . $nome . '<br />'. 
    				'<b>Local:</b> ' . $local . '<br />'.
    				'<b>Descricao:</b> ' . $descricao . '<br />';
    		
    			
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
     * Pesquisa e retorna os registros de editoras do banco de dados
     * 
     * @param integer $id ID do autor (opcional)
     * @param array $fields Campos (colunas) da tabela a serem retornados
     * @param integer $start Deslocamento do índice de início dos resultados, útil para paginação (opcional)
     * @param integer $limit Quantidade de resultados a serem retornados a partir do deslocamento (opcional)
     * @return array Resultado da pesquisa
     */
public function get($id = '', $fields = array(), $start = 0, $limit = 0, $term='') {
        $columns = '*';
        if ($fields) {
            //Junta os campos/colunas para a consulta SQL
            $columns = implode(',', $fields);
        }
        //Prepara a consulta SQL
        $query = "SELECT $columns FROM Editora";
        //Caso o ID tenha sido especificado, retornará apenas os dados referentes a aquele ID
        if ($id) {
            $query.= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
        } else if ($term) {
			$query.= sprintf(" WHERE concat(nome,', ',local) LIKE '%%%s%%'", mysqlx_real_escape_string($term));
		}
        //Senão, caso a descrição tenha sido especificada, aplica o filtro
        
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

    public function periodico($id) {
    	$query = sprintf("Select periodico From Editora WHERE id='%s'", 
    			mysqlx_real_escape_string($id));
    	try {
    		$result_sql = $this->DB->query($query);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return false;
    	}
    	//Retorna os resultados em forma de matriz
    	return $this->DB->parse_result($result_sql)[0]['periodico']; 
    }
    public function getDescricao($id) {
    	$query = sprintf("Select descricao From Editora WHERE id='%s'",
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
    
    public function getLocal($id) {
    	$localstr = "";
    	$primeiro=1;
    	$controle_localizacao = ControleLocalizacao::getInstance();
    	
    	$cidades = $this->getLocalCidades($id);
    	 
    	if ($cidades) {
    		foreach ($cidades as $cidade) {
    			$loc = $controle_localizacao->getCidadeEstadoString($cidade['id']);
    			if ($primeiro==1) {
    			 	$localstr = $loc;
    			 	$primeiro=0;
    			 } else 
    			 	$localstr = $localstr."; ".$loc;
    		}
    	}
    	$estados = $this->getLocalEstados($id);
    	if ($estados) {
    		foreach ($estados as $estado) {
    			$loc = $controle_localizacao->getEstado($estado['id'])['sigla'];
    			if ($primeiro==1) {
    				$localstr = $loc;
    				$primeiro=0;
    			} else
    				$localstr = $localstr."; ".$loc;
    		}
    	}
    	$paises = $this->getLocalPaises($id);
    	if ($paises) {
    		foreach ($paises as $pais) {
    			$loc = $controle_localizacao->getPaises($pais['id']);
    			$loc = $loc[0]['nome'];
    			if ($primeiro==1) {
    				$localstr = $loc;
    				$primeiro=0;
    			} else
    				$localstr = $localstr."; ".$loc;
    		}
    	}
    	if ($localstr == "")
    		$localstr = '[S.I]';
    	return $localstr;
    	
    }
    
    public function getLocalCidades($id, $fields = array()) {
    	$columns = '*';
    	if ($fields) {
    		//Junta os campos/colunas para a consulta SQL
    		$columns = implode(', t1.', $fields);
    	}
    	//Prepara a consulta SQL
    	$query = sprintf("SELECT t1.$columns
    			FROM cidades t1
    			JOIN EditoraCidade t2
    			ON (t1.id = t2.Cidade_id)
    			WHERE t2.Editora_id='%s'",
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
    
    public function getLocalEstados($id, $fields = array()) {
    	$columns = '*';
    	if ($fields) {
    		//Junta os campos/colunas para a consulta SQL
    		$columns = implode(', t1.', $fields);
    	}
    	//Prepara a consulta SQL
    	$query = sprintf("SELECT t1.$columns
    			FROM estados t1
    			JOIN EditoraEstado t2
    			ON (t1.id = t2.Estado_id)
    			WHERE t2.Editora_id='%s'",
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
    
    public function getLocalPaises($id, $fields = array()) {
    	$columns = '*';
    	if ($fields) {
    		//Junta os campos/colunas para a consulta SQL
    		$columns = implode(', t1.', $fields);
    	}
    	//Prepara a consulta SQL
    	$query = sprintf("SELECT t1.$columns
    			FROM Paises t1
    			JOIN EditoraPais t2
    			ON (t1.id = t2.Pais_id)
    			WHERE t2.Editora_id='%s'",
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
     * Adiciona o Editora no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add($nome, $locais, $descricao, $periodico) {
    	
 
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        $cidades = Array();
        $estados = Array();
        $paises = Array();
        
        if (!$nome)
        	$nome="[s.n.]";
        
        $nbLocais=0;
       
        if ($locais) {
        	foreach ($locais as $local) { 
        		
        		$posCidade = strpos ( $local, 'L1' );
        		$posEstado = strpos ( $local, 'L2' );
        		$posPais = strpos ( $local, 'L3' );
        		
        		$idCidade=NULL;
        		$idEstado=NULL;
        		$idPais=NULL;
        		
        		if ($posCidade !='') {
        			$idCidade = substr ($local, 0, $posCidade );
        			$cidades[$nbLocais] = $idCidade;
        		}
        		else $posCidade = 0;
        		if ($posEstado!='') {
  					if ($posCidade==0)
        				$idEstado = substr ($local, $posCidade, $posEstado);
  					else 
  						$idEstado = substr ($local, $posCidade+2, $posEstado-$posCidade-2);   
  					$estados[$nbLocais] = $idEstado;
  						
        		} 
        		if ($posPais!='') {
  					if (($posEstado==0)&&($posCidade==0))
        				$idPais = substr ($local, 0,$posPais);
  					else if (($posCidade!=0)&&($posEstado==0))
  						$idPais = substr ($local, $posCidade+2,$posPais-$posCidade-2);
  					else 
  						$idPais = substr ($local, $posEstado+2,$posPais-$posEstado-2);
  					$paises[$nbLocais] = $idPais;
        		}
        		$nbLocais++;
        	}
        }

      
        
        $localEditora = '';
        $controle_localizacao = ControleLocalizacao::getInstance();
        
        for ($i=0;$i<$nbLocais;$i++) {
        	if (isset($cidades[$i])) {
        		$localidade = $controle_localizacao->getCidadeEstado($cidades[$i]);
        		if (isset($localidade['estado_sigla'])) {
        			$localEditora = $localEditora.$localidade['cidade'].", ".$localidade['estado_sigla'];
        		} else {
        			$localEditora = $localEditora.$localidade['cidade'].", ".$localidade['pais'];
        		}  
        	} else if (isset($estados[$i])) {
        		$localidade = $controle_localizacao->getEstado($estados[$i]);
        		$localEditora = $localEditora.$localidade['sigla'].", ".$localidade['pais'];
        	} else if (isset($paises[$i])) {
        		$localidade = $controle_localizacao->getPaises($paises[$i])[0];
        		$localEditora = $localEditora.$localidade['nome'];
        	}
        	if (($nbLocais>1)&&($i<$nbLocais-1))
        		$localEditora = $localEditora."; ";
        	 
        }
        
        if (! $localEditora) {
			$localEditora = "[S.I.]";
			
        }
        
		// Validação
		$invalid_fields = $this->validate_parameters ( $nome, $localEditora );
		
		if ($invalid_fields) {
			return json_encode ( array (
				'error' => $invalid_fields ) );
		}

	// Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
	$fields = array ();
	$fields ['nome'] = $nome;
	$fields ['local'] = $localEditora;
	$fields ['descricao'] = $descricao;
	$fields ['periodico'] = $periodico;

	try {
		$this->DB->insert ( 'Editora', $fields, false );
		$id = $this->DB->get_last_id();
		// incluir relacionamentos de localidade
		for ($i=0;$i<$nbLocais;$i++) {
			if (isset($cidades[$i])) {
				$fields = array ();
				$fields ['Editora_id'] = $id;
				$fields ['Cidade_id'] = $cidades[$i];
				$this->DB->insert ( 'EditoraCidade', $fields, false );
			} else if (isset($estados[$i])) {
				$fields = array ();
				$fields ['Editora_id'] = $id;
				$fields ['Estado_id'] = $estados[$i];
				$this->DB->insert ( 'EditoraEstado', $fields, false );
			} else if (isset($paises[$i])) {
				$fields = array ();
				$fields ['Editora_id'] = $id;
				$fields ['Pais_id'] = $paises[$i];
				$this->DB->insert ( 'EditoraPais', $fields, false );			
			}
		}
		
		
	} catch ( Exception $e ) {
		Logger::log ( $e->getMessage (), __FILE__ );
		return json_encode ( array (
				'error' => 'Erro ao inserir no banco de dados' 
		) );
	}
	$this->notify('adiçao',$nome,$localEditora,$descricao);
	
	return json_encode(array('error' => null));
 }

    /**
     * Atualiza o Documento no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update($id, $nome, $locais, $descricao,$periodico) {
 
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }

     
        //Validação
        if (!$id || !is_numeric($id)) {
            return json_encode(array('error' => 'ID não especificado ou inválido'));
        }
        
        
        $cidades = Array();
        $estados = Array();
        $paises = Array();
        
        if (!$nome)
        	$nome="[s.n.]";
        
        $nbLocais=0;
if ($locais) {
        	foreach ($locais as $local) { 
        		
        		$posCidade = strpos ( $local, 'L1' );
        		$posEstado = strpos ( $local, 'L2' );
        		$posPais = strpos ( $local, 'L3' );
        		
        		$idCidade=NULL;
        		$idEstado=NULL;
        		$idPais=NULL;
        		
        		if ($posCidade !='') {
        			$idCidade = substr ($local, 0, $posCidade );
        			$cidades[$nbLocais] = $idCidade;
        		}
        		else $posCidade = 0;
        		if ($posEstado!='') {
  					if ($posCidade==0)
        				$idEstado = substr ($local, $posCidade, $posEstado);
  					else 
  						$idEstado = substr ($local, $posCidade+2, $posEstado-$posCidade-2);   
  					$estados[$nbLocais] = $idEstado;
  						
        		} 
        		if ($posPais!='') {
  					if (($posEstado==0)&&($posCidade==0))
        				$idPais = substr ($local, 0,$posPais);
  					else if (($posCidade!=0)&&($posEstado==0))
  						$idPais = substr ($local, $posCidade+2,$posPais-$posCidade-2);
  					else 
  						$idPais = substr ($local, $posEstado+2,$posPais-$posEstado-2);
  					$paises[$nbLocais] = $idPais;
        		}
        		$nbLocais++;
        	}
        }

      
        
        $localEditora = '';
        $controle_localizacao = ControleLocalizacao::getInstance();
        
        for ($i=0;$i<$nbLocais;$i++) {
        	if (isset($cidades[$i])) {
        		$localidade = $controle_localizacao->getCidadeEstado($cidades[$i]);
        		if (isset($localidade['estado_sigla'])) {
        			$localEditora = $localEditora.$localidade['cidade'].", ".$localidade['estado_sigla'];
        		} else {
        			$localEditora = $localEditora.$localidade['cidade'].", ".$localidade['pais'];
        		}  
        	} else if (isset($estados[$i])) {
        		$localidade = $controle_localizacao->getEstado($estados[$i]);
        		$localEditora = $localEditora.$localidade['sigla'].", ".$localidade['pais'];
        	} else if (isset($paises[$i])) {
        		$localidade = $controle_localizacao->getPaises($paises[$i])[0];
        		$localEditora = $localEditora.$localidade['nome'];
        	}
        	if (($nbLocais>1)&&($i<$nbLocais-1))
        		$localEditora = $localEditora."; ";
        	 
        }
        
        if (! $localEditora)
			$localEditora = "[S.I.]";
        
        // Validação
        $invalid_fields = $this->validate_parameters ( $nome, $localEditora );
        if ($invalid_fields) {
        	return json_encode ( array (
        			'error' => $invalid_fields ) );
        }
        
        // Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array ();
        $fields ['nome'] = $nome;
        $fields ['local'] = $localEditora;
        $fields ['descricao'] = $descricao;
        $fields ['periodico'] = $periodico;
        
        //Clausula WHERE para atualizar apenas o registro especificado
        $where = "id = '" . mysqlx_real_escape_string($id) . "'";
        
        try {
        	$this->DB->update ( 'Editora', $fields, $where );
        	
        	//Exclui os antigos relacionamentos entre Autor e Documento
        	$query = sprintf("DELETE FROM EditoraCidade
                          WHERE Editora_id = '%s'",
        			mysqlx_real_escape_string($id));
        	$this->DB->query($query);
        	
        	$query = sprintf("DELETE FROM EditoraEstado
                          WHERE Editora_id = '%s'",
        			mysqlx_real_escape_string($id));
        	$this->DB->query($query);
        	
        	$query = sprintf("DELETE FROM EditoraPais
                          WHERE Editora_id = '%s'",
        			mysqlx_real_escape_string($id));
        	$this->DB->query($query);
        	
        	 
        	// incluir relacionamentos de localidade
        	for ($i=0;$i<$nbLocais;$i++) {
        		if (isset($cidades[$i])) {
        			$fields = array ();
        			$fields ['Editora_id'] = $id;
        			$fields ['Cidade_id'] = $cidades[$i];
        			$this->DB->insert ( 'EditoraCidade', $fields, false );
        		} else if (isset($estados[$i])) {
        			$fields = array ();
        			$fields ['Editora_id'] = $id;
        			$fields ['Estado_id'] = $estados[$i];
        			$this->DB->insert ( 'EditoraEstado', $fields, false );
        		} else if (isset($paises[$i])) {
        			$fields = array ();
        			$fields ['Editora_id'] = $id;
        			$fields ['Pais_id'] = $paises[$i];
        			$this->DB->insert ( 'EditoraPais', $fields, false );
        		}
        	}
        
        
        } catch ( Exception $e ) {
        	Logger::log ( $e->getMessage (), __FILE__ );
        	return json_encode ( array (
        			'error' => 'Erro ao atualizar no banco de dados'
        	) );
        }
        $this->notify('modificação',$nome,$localEditora,$descricao);
        return json_encode(array('error' => null));
        }


    /**
     * Remove os registros do banco de dados
     * 
     * @param array $ids IDs dos registros a serem excluídos
     * @return string Resultado no formato JSON
     */
    public function del($ids,$force=null) {
    	
    	$ids_str = implode("','", $ids);

    	$queryAviso = sprintf("Select * FROM Editora
                          WHERE id IN ('%s')",
    			$ids_str);
    	
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        if (!is_array($ids) || !$ids) {
            return json_encode(array('error' => 'IDs inválidos'));
        }
        
        $query = sprintf("SELECT COUNT(*) AS DocumentoUsando FROM DocumentoEditora WHERE Editora_id IN ('%s')", $ids_str); 
        try {
        	$result = $this->DB->query($query);
        	$editoraEmUso = mysqlx_result($result, 0, 'DocumentoUsando');
        } catch (Exception $e) {
            Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
            return json_encode(array('error' => 'Erro geral no banco de dados'));
        }
        
        if (!$force && $editoraEmUso!=0) {
        	return json_encode(array('error' => 'Esta Editora não pode ser apagada pois está sendo utilizada'));
        }
        else {
        	//Exclui do banco de dados

        	$query = sprintf("DELETE FROM DocumentoEditora
                          WHERE Editora_id IN ('%s')", $ids_str);
        	$queryDelete = sprintf("DELETE FROM Editora
                          WHERE id IN ('%s')", $ids_str);
        
        	try {
        		$result = $this->DB->query($queryAviso);
        		while ($row = mysqli_fetch_array($result)) {
        			$this->notify("remoção",$row['nome'],$row['local'],$row['descricao']);
        		}
        	
        		$this->DB->query($query);
        		$this->DB->query($queryDelete);
        		
        	} catch (Exception $e) {
          	  Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
         	   return json_encode(array('error' => 'Erro ao excluir no banco de dados'));
       	 	}

        	//Nenhum erro ocorreu. Retorna nulo.
        	return json_encode(array('error' => null));
        }
    }

    /**
     * Valida os campos (parâmetros) do Editora
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($nome, $local) {
        $invalid_fields = array();
        if (!$nome) {
            $invalid_fields['nome'] = 'Nome da Editora deve ser especificado';
        }
        if (!$local) {
            $invalid_fields['locais'] = 'Local da editora não especificado';
        }
        return $invalid_fields;
    }
    
    public function atualizaLocais() {
    	$query ="SELECT * From Editora WHERE 1";
    	try {
    		$result = $this->DB->query($query);
    		while ($row = mysqli_fetch_array($result)) {
    			$local = $this->getLocal($row['id']);
    			$query = sprintf("Update Editora set local='%s' WHERE id='%s'", $local, $row['id']);
    			$this->DB->query($query);
    		}
    	} catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return json_encode(array('error' => 'Erro geral no banco de dados'));
    	}
    }
    	
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
                          JOIN DocumentoEditora de
                           ON (dc.id = de.Documento_id)
                          WHERE de.Editora_id='%s'
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
    

}
