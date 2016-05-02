<?php
require_once (dirname ( __FILE__ ) . '/../include/DB.php');
require_once (dirname ( __FILE__ ) . '/../include/DataTables.php');
require_once (dirname ( __FILE__ ) . '/../include/Logger.php');
require_once (dirname ( __FILE__ ) . '/../include/Auth.php');
require_once (dirname ( __FILE__ ) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/ControleEditoras.php');

class ControleDocumentoConsulta  {

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
    private function __clone() {
    }

    public function reset() {
    	// Atualiza localização das editoras
    	$controle_editoras = ControleEditoras::getInstance();
    	$controle_editoras->atualizaLocais();
    	
    	// Reinicia tabela DocumentoConsulta
    	$query = "TRUNCATE TABLE DocumentoConsulta";
    	try {
    		$result = $this->DB->query($query);
    		$query = "INSERT INTO DocumentoConsulta (id, titulo, titulo_normalizado, subtitulo, subtitulo_normalizado, 
    				  titulo_alternativo, titulo_alternativo_normalizado, Autor_ids, autores_nome_completo, 
    				  autores_nome_completo_normalizado, autores_nome_usual, autores_nome_usual_normalizado, 
    				  autores_pseudonimo, autores_pseudonimo_normalizado, TipoDocumento_id, nome_tipodocumento,
				      Genero_id, nome_genero, Categoria_id, nome_categoria, Idioma_id, nome_idioma, ano_producao, 
    				  ano_producao_fim, ano_publicacao_inicio, ano_publicacao_fim, nome_editora, descricao, 
    				  seculo_producao, seculo_publicacao, midias, Acervo_id, Editora_id, local_editoras)
    	
				SELECT
					d.id,
					d.titulo,
					pt_normalize(d.titulo),
					o.subtitulo,
					pt_normalize(o.subtitulo),
					o.titulo_alternativo,
					pt_normalize(o.titulo_alternativo),
					GROUP_CONCAT(DISTINCT `a`.`id` SEPARATOR ';') AS Autor_ids,
					GROUP_CONCAT(DISTINCT `a`.`nome_completo` SEPARATOR ';') AS autores_nome_completo,
					pt_normalize(GROUP_CONCAT(DISTINCT `a`.`nome_completo` SEPARATOR ';')),
					GROUP_CONCAT(`a`.`nome_usual` SEPARATOR ';') AS autores_nome_usual,
					pt_normalize(GROUP_CONCAT(DISTINCT `a`.`nome_usual` SEPARATOR ';')),
					GROUP_CONCAT(`a`.`pseudonimo` SEPARATOR ';') AS autores_pseudonimo,
					pt_normalize(GROUP_CONCAT(DISTINCT `a`.`pseudonimo` SEPARATOR ';')),
					t.id,
					t.nome,
					GROUP_CONCAT(DISTINCT `g`.`id` SEPARATOR ';') AS Genero_id,
					GROUP_CONCAT(DISTINCT `g`.`nome` SEPARATOR ';') AS nome_genero,
					c.id,
					c.nome,
					GROUP_CONCAT(DISTINCT `i`.`id` SEPARATOR ';') AS Idioma_id,
					GROUP_CONCAT(DISTINCT `i`.`descricao` SEPARATOR ';') AS nome_idioma,
					d.ano_producao,
					o.ano_producao_fim,
					o.ano_publicacao_inicio,
					o.ano_publicacao_fim,
					CONCAT (GROUP_CONCAT(DISTINCT `ed`.`nome` SEPARATOR '; '),' ; ', GROUP_CONCAT(DISTINCT `ed`.`descricao` SEPARATOR '; ')) AS nome_editora,
					d.descricao,
					o.seculo_producao,
					o.seculo_publicacao,
					(SELECT COUNT(*) FROM Midia WHERE (Midia.Documento_id = d.id)) AS midias,
					d.Acervo_id,
					GROUP_CONCAT(DISTINCT `ed`.`id` SEPARATOR ';') AS Editora_ids,
					GROUP_CONCAT(DISTINCT `ed`.`local` SEPARATOR ';') AS local_editoras_ids
				FROM Documento d
				LEFT JOIN AutorDocumento ad
					ON d.id = ad.Documento_id
				LEFT JOIN Autor a
					ON a.id = ad.Autor_id
				LEFT JOIN ObraLiteraria o
					ON d.id = o.Documento_id
				LEFT JOIN TipoDocumento t
					ON d.TipoDocumento_id = t.id
				LEFT JOIN DocumentoGenero dg
					ON d.id = dg.Documento_id
				LEFT JOIN Genero g
					ON g.id = dg.Genero_id
				LEFT JOIN Categoria c
					ON d.Categoria_id = c.id
				LEFT JOIN DocumentoIdioma di
					ON d.id = di.Documento_id
				LEFT JOIN Idioma i
					ON i.id = di.Idioma_id
				LEFT JOIN DocumentoEditora de
					ON d.id = de.Documento_id
				LEFT JOIN Editora ed
					ON ed.id = de.Editora_id
				GROUP BY d.id";
    	
    		$result = $this->DB->query($query);
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return false;
    	}
    	$this->set_ano_doc();
    }
    public function set_ano_doc() {
    	$query = "SELECT id,
				 ano_producao,
				 ano_producao_fim,
				 ano_publicacao_inicio,
				 ano_publicacao_fim,
				 seculo_producao,
				 seculo_publicacao
		  FROM DocumentoConsulta";
    	try {
    		$result = $this->DB->query($query);
       		while ($row = mysqli_fetch_array($result)) {
    			$id = $row['id'];
    			$ano_producao = $row['ano_producao'];
    			$ano_producao_fim = $row['ano_producao_fim'];
    			$ano_publicacao_inicio = $row['ano_publicacao_inicio'];
    			$ano_publicacao_fim = $row['ano_publicacao_fim'];
    			$seculo_producao = $row['seculo_producao'];
    			$seculo_publicacao = $row['seculo_publicacao'];
    	
    			$ano_doc = null;
    			$seculo_doc = null;
    	
    			if ($seculo_producao != "") {
    				$seculo_doc = $seculo_producao;
    			}
    			else if ($seculo_publicacao != "") {
    				$seculo_doc = $seculo_publicacao;
    			}
    	
    			if ($ano_producao != "") {
    				$ano_doc = $ano_producao;
    			}
    			else if ($ano_producao_fim != "") {
    				$ano_doc = $ano_producao_fim;
    			}
    			else if ($ano_publicacao_inicio != "" && $seculo_doc == null) {
    				$ano_doc = $ano_publicacao_inicio;
    			}
    			else if ($ano_publicacao_fim != "" && $seculo_doc == null) {
    				$ano_doc = $ano_publicacao_fim;
    			}
    	
    			$ano_doc = $ano_doc ? "'$ano_doc'" : 'NULL';
    			$seculo_doc = $seculo_doc ? "'$seculo_doc'" : 'NULL';
    	
    			$query2 = "UPDATE DocumentoConsulta SET ano_documento = $ano_doc, seculo_documento = $seculo_doc
    					WHERE id = '$id'";
    	
    			$this->DB->query($query2);
    		}
    	}
    	catch (Exception $e) {
    		Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
    		return false;
    	}
    } 
}
