<?php
require_once (dirname(__FILE__) . '/../include/DB.php');
require_once (dirname(__FILE__) . '/../include/DataTables.php');
require_once (dirname(__FILE__) . '/../include/Logger.php');
require_once (dirname(__FILE__) . '/../include/Auth.php');
require_once (dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleCriticasAutor extends DataTables {

	protected $DB;
	private static $instance;

	public static function getInstance() {
		if (!self :: $instance) {
			self :: $instance = new self();
		}
		return self :: $instance;
	}

	private function __construct() {
		$this->DB = DB :: getInstance();
		$this->DB->connect();
	}

	private function __clone() {
	}

	protected function fnBuildQuery($aParams) {
        /* Query statements */
        $aClauses = $this->fnBuildQueryClauses($aParams);
        
        /* Query */
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS {$aParams['sColumns']}
                   FROM {$aParams['sTable']}
                   {$aClauses['sWhere']}
                   {$aClauses['sOrder']}
                   {$aClauses['sLimit']}";
                   
        return $sQuery;
    }

	public function get($id = '', $fields = array (), $start = 0, $limit = 0) {
		$result = array ();
		$index = 0;

		$columns = '*';
		if ($fields) {
			$columns = implode(',', $fields);
		}

		$query = "SELECT $columns FROM CriticaAutor";
		if ($id) {
			$query .= sprintf(" WHERE id='%s' LIMIT 1", mysqlx_real_escape_string($id));
		}
		elseif (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			$query .= sprintf(" LIMIT %u,%u", mysqlx_real_escape_string($start), mysqlx_real_escape_string($limit));
		}
		
		try {
			$result_sql = $this->DB->query($query);
		} catch (Exception $e) {
			Logger :: log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		while ($row = mysqli_fetch_array($result_sql)) {
			$keys = array_keys($row);
			foreach ($keys as $key) {
				if (!is_numeric($key)) {
					$result[$index][$key] = $row[$key];
				}
			}
			$index++;
		}
		return $result;
	}
	
	public function get_by_autor($id = '', $fields = array (), $start = 0, $limit = 0) {
		$result = array ();
		$index = 0;

		$columns = '*';
		if ($fields) {
			$columns = implode(',', $fields);
		}

		$query = "SELECT $columns FROM CriticaAutor";
		if ($id) {
			$query .= sprintf(" WHERE Autor_id='%s'", mysqlx_real_escape_string($id));
		}
		elseif (is_numeric($start) && $start >= 0 && is_numeric($limit) && $limit > 0) {
			$query .= sprintf(" LIMIT %u,%u", mysqlx_real_escape_string($start), mysqlx_real_escape_string($limit));
		}
		
		try {
			$result_sql = $this->DB->query($query);
		} catch (Exception $e) {
			Logger :: log($e->getMessage() . " (Query: $query)", __FILE__);
			return false;
		}
		while ($row = mysqli_fetch_array($result_sql)) {
			$keys = array_keys($row);
			foreach ($keys as $key) {
				if (!is_numeric($key)) {
					$result[$index][$key] = $row[$key];
				}
			}
			$index++;
		}
		return $result;
	}
	
	public function add($titulo, $autor_critica, $autor_id, $autor, $nome_arquivo, $mime, $tamanho_arquivo) {
		
		if (!Auth :: check()) {
			return json_encode(array (
				'error' => 'Acesso negado'
			));
		}

		//Validation
		$invalid_fields = $this->validate_parameters($titulo, $autor);
		if ($invalid_fields) {
			return json_encode(array (
				'error' => $invalid_fields
			));
		}

		//DB Insert

		$fields = array ();
		$fields['titulo'] = $titulo;
		$fields['autor_critica'] = $autor_critica;
		$fields['Autor_id'] = $autor_id;
		$fields['Usuario_id'] = $_SESSION['id'];
		$fields['Autor_nome_completo'] = $autor;
		$fields['nome_arquivo'] = $nome_arquivo;
		$fields['mime']	= $mime;
		$fields['tamanho'] = $tamanho_arquivo;
		
		try {
			$this->DB->insert('CriticaAutor', $fields, false);
		} catch (Exception $e) {
	//		print $e;
			Logger :: log($e->getMessage(), __FILE__);
			return json_encode(array (
				'error' => 'Erro ao inserir no banco de dados'
			));
		}

		return json_encode(array (
			'error' => null
		));
	}

	public function update($id, $titulo, $autor_critica, $nome_arquivo, $mime, $tamanho_arquivo) {
		
		if (!Auth :: check()) {
			return json_encode(array (
				'error' => 'Acesso negado'
			));
		}

		//Validation
		if (!$id) {
			return json_encode(array (
				'error' => 'ID não especificado'
			));
		}
		$invalid_fields = $this->validate_parameters_update($titulo);
		if ($invalid_fields) {
			return json_encode(array (
				'error' => $invalid_fields
			));
		}
		
		//DB Insert
		$fields = array ();
		$fields['titulo'] = $titulo;
		$fields['autor_critica'] = $autor_critica;
		$fields['nome_arquivo'] = $nome_arquivo;
		$fields['mime'] = $mime;
		$fields['tamanho'] = $tamanho_arquivo;

		$where = "id = '" . mysqlx_real_escape_string($id) . "'";

		try {
			$this->DB->update('CriticaAutor', $fields, $where, false);
		} catch (Exception $e) {
			Logger :: log($e->getMessage(), __FILE__);
			return json_encode(array (
				'error' => 'Erro ao atualizar no banco de dados'
			));
		}

		return json_encode(array (
			'error' => null
		));
	}

	public function del($ids) {
		if (!Auth :: check()) {
			return json_encode(array (
				'error' => 'Acesso negado'
			));
		}
		if (!is_array($ids) || !$ids) {
			return json_encode(array (
				'error' => 'IDs inválidos'
			));
		}
		//Delete from database
		$ids_str = implode("','", $ids);
		$query = sprintf("DELETE FROM CriticaAutor WHERE id IN ('%s')", $ids_str);
		try {
			$this->DB->query($query);
		} catch (Exception $e) {
			Logger :: log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array (
				'error' => 'Erro ao excluir no banco de dados'
			));
		}
		return json_encode(array (
			'error' => null
		));
	}

	private function validate_parameters($titulo, $autor) {
		
		$invalid_fields = array ();
		if (!$titulo) {
            $invalid_fields['titulo'] = 'O título não pode ser vazio';
        }
		if (!$autor) {
			$invalid_fields['autor'] = 'A crítica é atribuida à um autor';
		}
		return $invalid_fields;
	}
	
	private function validate_parameters_update($titulo) {
		
		$invalid_fields = array ();
		if (!$titulo) {
            $invalid_fields['titulo'] = 'O título não pode ser vazio';
		}
		return $invalid_fields;
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
            $maxFileSize = MAX_FILE_SIZE * 1024 * 1024; //50MB
            if ($xhr) {
                $file = new qqUploadedFileXhr();
            }
            elseif ($form_file) {
                if ($form_file['error'] !== UPLOAD_ERR_OK) {
                    $error_msg = file_upload_error_message($form_file['error']);
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
            $target_file = CRITICAS_PATH . $file_basename;
          
            if (!is_dir(CRITICAS_PATH)) {
                if (!@mkdir(CRITICAS_PATH, 0755)) {
                    return htmlspecialchars(json_encode(array('error'=>'Erro ao criar o diretório de uploads')), ENT_NOQUOTES);
                }
            }
            if (!is_writable(CRITICAS_PATH)) {
                if (!@chmod(CRITICAS_PATH, 0755)) {
                    return htmlspecialchars(json_encode(array('error'=>'Erro alterar permissões do diretório de uploads')), ENT_NOQUOTES);
                }
            }
            $i = 1;
            while (file_exists($target_file)) {
                $file_basename = clean_filename($file_info['filename'] . '-' . $i . '.' . $file_info['extension']);
                $target_file = CRITICAS_PATH . $file_basename;
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
	
}