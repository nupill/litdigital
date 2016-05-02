<?php
require_once(dirname(__FILE__) . '/ControleDocumentos.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControlePublicacoesImprensa extends ControleDocumentos {
	
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
     * Adiciona a Publicação na Imprensa no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function add(//Atributos do Documento:
                        $titulo, $autores, $generos = array(), $categoria = array(), $fontes = array(),
                        $id_material = '', $id_editora = '', $abrangencia = '',
                        $direitos = '', $acervo_id = '', $localizacao = '', $estado = '', $descricao = '',
                        $ano_producao = '', $dimensao = '', $idiomas = array(),
                        //Atributos da Publicação na Imprensa:
                        $volume = '', $num_paginas = '', $artigo = '',
                        $local_publicacao = '', $data_inicial = '',
                        $data_final = '', $num_fasciculo = '',
                        //Atributos das Mídias:
                        $arquivos = array(), $titulos_arquivos = array(),
                        $descricoes_arquivos = array(), $fontes_arquivos = array()) {
        
        //Verifica se possui permissão para esta ação
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
       //Validação dos campos do Documento
        $invalid_fields = $this->validate_parameters_documentos($titulo, $autores,
                                                                $generos, $categoria,
                                                                $fontes, $id_material,
                                                                $estado, $ano_producao,
                                                                $idiomas);
        
        //Validação dos campos de Publicacoes
        $invalid_fields = $this->validate_parameters($volume, $num_paginas,
                                                    $data_inicial, $data_final);
                                                                   
        //Retorna os erros de validação caso houverem
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Adiciona o pai (Documento) no banco de dados
        $result = $this->add_documento(DOCUMENTOS_PUBLICACOES_IMPRENSA_ID, $titulo, $autores, $generos, $categoria, $fontes,
		                               $id_material, $id_editora, $abrangencia, $direitos, $acervo_id,
		                               $localizacao, $estado, $descricao, $ano_producao, $dimensao, $idiomas);

        //Verifica se ocorreram erros ao inserir o Documento
        $result_decoded = json_decode($result, true);
        if ($result_decoded['error']) {
            exit($result);
        }

        $id = $result_decoded['id'];
        
        //Reformatando as datas para poder salvar no mysql (depois de passar da validação, as datas devem estar no formato dd/mm/yyyy)
        $lista_data_inicial = explode('-', $data_inicial);
        $data_inicial = implode('-', array_reverse($lista_data_inicial));
        $lista_data_final = explode('-', $data_final);
        $data_final = implode('-', array_reverse($lista_data_final));

        //Inserção no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['Documento_id'] = $id;
        $fields['volume'] = $volume;
        $fields['num_paginas'] = $num_paginas;
        $fields['artigo'] = $artigo;
        $fields['local_publicacao'] = $local_publicacao;
        $fields['data_inicial'] = $data_inicial;
        $fields['data_final'] = $data_final;
        $fields['num_fasciculo'] = $num_fasciculo;
        
        try {
            $this->DB->insert('PublicacaoImprensa', $fields, false);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            //Remove o pai (Documento) do banco de dados
            try {
                $this->DB->query("DELETE FROM Documento WHERE id='$id'");
            }
            catch (Exception $e) {
                Logger::log($e->getMessage(), __FILE__);
                return json_encode(array('error' => 'Erro no banco de dados. Inconsistêcia gerada. Contate o administrador.'));
            }
            return json_encode(array('error' => 'Erro ao inserir no banco de dados'));
        }

        //Inclusão das mídias no banco de dados
        $result = $this->add_midias($id, $arquivos,
                                    $titulos_arquivos, $descricoes_arquivos,
                                    $fontes_arquivos);

        //Verifica se ocorreram erros ao incluir as mídias
        $result_decoded = json_decode($result, true);
        if ($result_decoded['error']) {
            return $result;
        }

        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null, 'id' => $id));
    }
    
    /**
     * Atualiza a Publicação na Imprensa no banco de dados
     * 
     * @return string Resultado da inserção no formato JSON
     */
    public function update(//Atributos do Documento:
                            $id, $titulo, $autores, $generos = array(), $categoria = array(), $fontes = array(),
                            $id_material = '', $id_editora = '', $abrangencia = '',
                            $direitos = '', $acervo_id = '', $localizacao = '', $estado = '', $descricao = '',
                            $ano_producao = '', $dimensao = '', $idiomas = array(),
                            //Atributos da Publicação na Imprensa:
                            $volume = '', $num_paginas = '', $artigo = '',
                            $local_publicacao = '', $data_inicial = '',
                            $data_final = '', $num_fasciculo = '',
                            //Atributos das Mídias:
                            $arquivos = array(), $arquivos_substituidos = array(), $titulos_arquivos = array(),
                            $descricoes_arquivos = array(), $fontes_arquivos = array()) {
        
        //Verifica se possue permissão para esta ação   
        if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
            return json_encode(array('error' => 'Acesso negado'));
        }
        
        //Validação dos campos do Documento
        $invalid_fields = $this->validate_parameters_documentos($titulo, $autores,
                                                                $generos, $categoria,
                                                                $fontes, $id_material,
                                                                $estado, $ano_producao,
                                                                $idiomas, $id);
        
        //Validação dos campos de Publicacoes
        $invalid_fields = $this->validate_parameters($volume, $num_paginas,
                                                    $data_inicial, $data_final);
                                                                   
        //Retorna os erros de validação caso houverem
        if ($invalid_fields) {
            return json_encode(array('error' => $invalid_fields));
        }

        //Atualiza o pai (Documento) no banco de dados
        $result = $this->update_documento($id, $titulo, $autores, $generos, $categoria, $fontes, $id_material,
                                          $id_editora, $abrangencia, $direitos, $acervo_id,
                                          $localizacao, $estado, $descricao, $ano_producao, $dimensao, $idiomas);


        //Verifica se ocorreram erros ao modificar o Documento
        $result_decoded = json_decode($result, true);
        if ($result_decoded['error']) {
            exit($result);
        }

        //Reformatando as datas para poder salvar no mysql (depois de passar da validação, as datas devem estar no formato dd/mm/yyyy)
        $lista_data_inicial = explode('-', $data_inicial);
        $data_inicial = implode('-', array_reverse($lista_data_inicial));
        $lista_data_final = explode('-', $data_final);
        $data_final = implode('-', array_reverse($lista_data_final));
        
        //Atualização no Banco de Dados (exemplo: $fields['nome_da_coluna'] = 'valor';)
        $fields = array();
        $fields['volume'] = $volume;
        $fields['num_paginas'] = $num_paginas;
        $fields['artigo'] = $artigo;
        $fields['local_publicacao'] = $local_publicacao;
        $fields['data_inicial'] = $data_inicial;
        $fields['data_final'] = $data_final;
        $fields['num_fasciculo'] = $num_fasciculo;
        
        //Clausula WHERE para atualizar apenas o registro especificado
        $where = "Documento_id = '" . mysqlx_real_escape_string($id) . "'";
        
        try {
            $this->DB->update('PublicacaoImprensa', $fields, $where);
        }
        catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
        }
        
                            //Atualiza os indices do Lucene
		$query = sprintf("SELECT id FROM Midia
                          WHERE Documento_id IN ('%s')
                           AND nome_arquivo LIKE '%%.htm%%'",
		 				 mysqlx_real_escape_string($id));
		try {
			$result_sql = $this->DB->query($query);
			while ($row = mysqli_fetch_array($result_sql)) {
				$this->remove_index_one_document($row['id']);
			}
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
			return json_encode(array('error' => 'Erro ao excluir índices do Lucene'));
		}
        
        //Exclui os antigos relacionamentos entre Midia e Documento
        $query = sprintf("DELETE FROM Midia
                          WHERE Documento_id = '%s'",
                          mysqlx_real_escape_string($id));
        try {
            $this->DB->query($query);
        }
	    catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return json_encode(array('error' => 'Erro ao atualizar no banco de dados'));
        }
        
        //Renomeia os arquivos substituídos
        foreach ($arquivos_substituidos as $index=>$arquivo_substituido) {
			if ($arquivo_substituido && file_exists(DOCUMENTS_PATH . $arquivo_substituido)) {

				//Renomeia apenas se o novo arquivo for do mesmo tipo do antigo
				if (file_extension($arquivos[$index]) != file_extension($arquivo_substituido)) {
					$arquivos[$index] = $arquivo_substituido;
				}
				else {
					
					//Verifica se outro documento utiliza este mesmo arquivo
					$query = sprintf("SELECT COUNT(*) AS num
				                  	  FROM Midia
				                  	  WHERE nome_arquivo = '%s'
				                  	   AND id != '%s'",
									 $arquivos[$index],
									 mysqlx_real_escape_string($id));
					try {
						$result = $this->DB->query($query);
						$total_rows = mysqlx_result($result, 0, 'num');
					}
					catch (Exception $e) {
						Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
						return json_encode(array('error'=>'Erro ao pesquisar no banco de dados'));
					}
					
					//Renomeia apenas se não houver outro documento utilizando este mesmo arquivo
					if ($total_rows > 0) {
						$arquivos[$index] = $arquivo_substituido;
					}
					else {
						$replaced_path = DOCUMENTS_PATH . '_replaced';
						
						if (!is_dir($replaced_path)) {
							if (!@mkdir($replaced_path, 0755)) {
								return json_encode(array('error'=>'Erro ao criar o diretório de arquivos substituídos'));
							}
						}
						if (!is_writable($replaced_path)) {
							if (!@chmod($replaced_path, 0755)) {
								return json_encode(array('error'=>'Erro ao alterar permissões do diretório de arquivos substituídos'));
							}
						}
						
						//Move o arquivo antigo para o diretório de arquivos substituídos
						if (!@rename(DOCUMENTS_PATH . $arquivos[$index], $replaced_path . '/' . $arquivos[$index])) {
							return json_encode(array('error'=>'Erro ao mover o arquivo antigo'));
						}
						
						//Renomeia o arquivo novo para manter o mesmo nome do arquivo antigo
						if (!@rename(DOCUMENTS_PATH . $arquivo_substituido, DOCUMENTS_PATH . $arquivos[$index])) {
							return json_encode(array('error'=>'Erro ao renomear o novo arquivo'));
						}
					}
				}
			}
		}
        
        //Inclusão das mídias no banco de dados
        $result = $this->add_midias($id, $arquivos, $titulos_arquivos, $descricoes_arquivos, $fontes_arquivos);
        
        //Verifica se ocorreram erros ao incluir as mídias
        $result_decoded = json_decode($result, true);
        if ($result_decoded['error']) {
        	return $result;
        }

        //Nenhum erro ocorreu. Retorna nulo.
        return json_encode(array('error' => null));
    }
    
    /**
     * Valida os campos (parâmetros) da Publicação na Imprensa
     * 
     * @return array Campos que não passaram no teste de validação
     */
    private function validate_parameters($volume = '', $num_paginas = '',
                                        $data_inicial = '', $data_final = '') {
        $invalid_fields = array();

        // Retirado de "Tipos de Documentos - classes e seus atributos", documento compartilhado pelo prof. Willrich
        if ($volume && !is_numeric($volume) && (strtolower($volume) != 'u')) {
            $invalid_fields['volume'] = 'Volume inválido. Valores aceitos são números ou, em caso de volume único, a letra "U"';
        }
        if ($num_paginas && !is_numeric($num_paginas)) {
            $invalid_fields['num_paginas'] = 'Número de páginas inválida';
        }
        if (validate_date($data_inicial)) {
            $invalid_fields['data_inicial'] = 'Data inicial inválida. O formato aceito é DD-MM-AAAA';
        }
        if (validate_date($data_final)) {
            $invalid_fields['data_final'] = 'Data final inválida. O formato aceito é DD-MM-AAAA ';
        }
        return $invalid_fields;
    }
}