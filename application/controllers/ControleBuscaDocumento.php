<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/../include/functions.php');
require_once(dirname(__FILE__) . '/ControleDocumentos.php');

class ControleBuscaDocumento extends DataTables {

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

	public function getTableData($aParams) {
		$sColumns = isset($aParams['sColumns']) ? $aParams['sColumns'] : '';
		$aColumns = explode(',', $sColumns);
		$sQuery = $this->fnBuildQuery($aParams);
		try {
			$rResult = $this->DB->query($sQuery);
			$sQuery = 'SELECT FOUND_ROWS()';
			$rResultFilterTotal = $this->DB->query($sQuery);
			$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
			$iFilteredTotal = $aResultFilterTotal[0];
		}
		catch (Exception $e) {
			Logger::log($e->getMessage() . " (Query: $sQuery)", __FILE__);
			return false;
		}
		/* Output parameters */
		$iEcho = isset($aParams['sEcho']) ? $aParams['sEcho'] : 0;
		$iTotal = $this->count($aParams['sTable']);
//                $iFilteredTotal = mysqli_num_rows($rResult);
		$aaData = $this->fnParseResult($rResult);

		if ($aaData) {
			if ($aColumns[$aParams['iSortCol_0']] == 'ano_documento') {
				foreach ($aaData as $key => $row) {
					if (is_roman_century($row[$aParams['iSortCol_0']])) {
						$row[$aParams['iSortCol_0']] = get_first_century_year($row[$aParams['iSortCol_0']]);
					}
					$ano[$key] = $row[$aParams['iSortCol_0']];
				}
				if ($aParams['sSortDir_0'] == 'desc') {
					array_multisort($ano, SORT_DESC, $aaData);
				}
				else {
					array_multisort($ano, SORT_ASC, $aaData);
				}
				//$aaData = sort2d($aaData, $aParams['iSortCol_0'], $aParams['sSortDir_0']);
			} elseif ($aColumns[$aParams['iSortCol_0']] == 'escore') {
				foreach ($aaData as $key => $row) {
					$escore[$key] = $row[$aParams['iSortCol_0']];
				}
				if ($aParams['sSortDir_0'] == 'desc') {
					array_multisort($escore, SORT_DESC, $aaData);
				}
				else {
					array_multisort($escore, SORT_ASC, $aaData);
				}
			}
			$aaData = array_slice($aaData, $aParams['iDisplayStart'], $aParams['iDisplayLength']);
		}

		/* Output */
		$sOutput = $this->fnBuildOutput($iEcho, $iTotal, $iFilteredTotal, $sColumns, $aaData);
		return json_encode($sOutput);
	}

	protected function fnBuildQuery($aParams) {
		/* Query statements */
		$aClauses = $this->fnBuildQueryClauses($aParams);
		/* Query */

		//$aParams['sColumns'] = str_replace(",escore","",$aParams['sColumns']);
		$sQuery = "SELECT SQL_CALC_FOUND_ROWS
					midias, 
					titulo,
					autores_nome_usual,
					autores_nome_completo,
					nome_tipodocumento,
					nome_genero,
					ano_documento,
					id, 
					seculo_documento, 
					Autor_ids, 
					Genero_id
				   FROM DocumentoConsulta d
				   LEFT JOIN DocumentoFonte df
				    ON (d.id = df.Documento_id)
				   {$aClauses['sWhere']}
				   GROUP BY id";
				   //{$aClauses['sOrder']}
				   //{$aClauses['sLimit']}
				  
	    $aColumns = explode(',', $aParams['sColumns']);
	    if ($aColumns[$aParams['iSortCol_0']] != 'seculo_documento' && $aColumns[$aParams['iSortCol_0']] != 'escore') {
	   		$sQuery.= ' ' . $aClauses['sOrder'];
	    }
	    
	    //FB::log($sQuery);

	    return $sQuery;
	}

	protected function fnBuildWhereClause($aColumns, $aParams) {
		$sWhere = '';
			
		//Procurar nos resultados
		if (is_array($aColumns) && $aParams['sSearch'] != '') {
			$sSearch = preg_replace('/\s\s+/', ' ', $aParams['sSearch']); //Remove extra spaces
			$sSearch = str_replace(' ', '%', $sSearch); //Replace space with %
			$sWhere = 'WHERE (';
			foreach ($aColumns as $aColumn) {
				$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
			}
			$sWhere = substr($sWhere, 0, -4);
			$sWhere.= ' )';
		}
		//Procura inicial

		//Adiciona os filtros
		$aWhere = array();

		if ($aParams['titulo']) {
			if (get_magic_quotes_gpc()) {
				$aParams['titulo'] = stripslashes($aParams['titulo']);
			}
			// Verifica a forma de busca
			if ($aParams['forma_busca'] == 1) { // Qualquer palavra
				$aParams['titulo'] = explode(' ', $aParams['titulo']);
			}

			// Se o filtro do título for por 'qualquer palavra'
			if (is_array($aParams['titulo'] )) {
				$where_titulo = "(";
				foreach ($aParams['titulo'] as $search_term) {
					$search_term = mysqlx_real_escape_string(trim(strtolower(normalize($search_term))));
					$where_titulo .= sprintf("d.titulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                              d.subtitulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                              d.titulo_alternativo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR ",
											$search_term,
											$search_term,
											$search_term);
				}
				$where_titulo = substr($where_titulo, 0, -3); //Remove o último OR
				$where_titulo.= ")";
				$aWhere[] = $where_titulo;
					
			}
			// Se o filtro do título for por 'frase exata'
			else {
				$aParams['titulo'] = substr(substr($aParams['titulo'], 1), 0, -1);
				$search_term = mysqlx_real_escape_string(trim(strtolower(normalize($aParams['titulo']))));
				$aWhere[] = sprintf("(d.titulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                    d.subtitulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                    d.titulo_alternativo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1) ",
									$search_term,
									$search_term,
									$search_term);

			}
		}

		if ($aParams['autores']) {

			$num_autores = substr_count($aParams['autores'], ',');

			if ($num_autores > 1) {
				$autor = explode(',', $aParams['autores']);
				$where_autor = "(";
				for ($i=0; $i<sizeof($autor)-1; $i++) {
					$autor[$i] = preg_replace('/\s\s+/', ' ', $autor[$i]); //Remove extra spaces
					//     $autor[$i] = str_replace(' ', '%', $autor[$i]); //Replace space with %
					$autor[$i] = mysqlx_real_escape_string(trim(strtolower(normalize($autor[$i]))));
					$where_autor .= sprintf("d.autores_nome_completo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
	                                         d.autores_nome_usual_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
	                                         d.autores_pseudonimo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR ",
											$autor[$i],
											$autor[$i],
											$autor[$i]);

				}
				$where_autor = substr($where_autor, 0, -3); //Remove o último OR
				$where_autor.= ")";
				$aWhere[] = $where_autor;
			}
			else {
				$autor = preg_replace('/\s\s+/', ' ', $aParams['autores']); //Remove extra spaces
				$autor = str_replace(',', '', $autor); //Replace , with ''
				$autor = mysqlx_real_escape_string(trim(strtolower(normalize($autor))));
				$aWhere[].= sprintf("(d.autores_nome_completo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                     d.autores_nome_usual_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                     d.autores_pseudonimo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1)",
									$autor,
									$autor,
									$autor);
			}
		}

		$classificacao = '';
		if (isset($aParams['tipo']) && $aParams['tipo'] == DOCUMENTOS_OBRA_LITERARIA_ID) {
			$classificacao = sprintf("(d.TipoDocumento_id = '%d')",
								mysqlx_real_escape_string($aParams['tipo']));
		}
		elseif ((isset($aParams['tipo']) && $aParams['tipo'] == 'acervo') && (!isset($aParams['tipo_acervo']) || $aParams['tipo_acervo'] == null)) {
			$classificacao = sprintf("(d.TipoDocumento_id != '%d')",
								DOCUMENTOS_OBRA_LITERARIA_ID);
		}
		elseif (isset($aParams['tipo_acervo']) && $aParams['tipo_acervo']) {
			$classificacao = sprintf("(d.TipoDocumento_id = '%d')",
								mysqlx_real_escape_string($aParams['tipo_acervo']));
		}
		
        if (isset($aParams['genero']) && $aParams['genero']) {
        	if ($classificacao) {
        		$classificacao.= sprintf(" AND (d.Genero_id = '%d')",
                                         mysqlx_real_escape_string($aParams['genero']));
        	}
        	else {
        		$classificacao.= sprintf(" d.Genero_id = '%d'",
                                         mysqlx_real_escape_string($aParams['genero']));
        	}
		}

		if (isset($aParams['categoria']) && $aParams['categoria']) {
			if ($classificacao) {
				$classificacao.= sprintf(" AND (d.Categoria_id = '%d')",
				mysqlx_real_escape_string($aParams['categoria']));
			}
		}

		if ($classificacao) {
			$aWhere[] = $classificacao;
		}

		if ($aParams['ano_inicio'] && $aParams['ano_fim'] && $aParams['ano_inicio'] <= $aParams['ano_fim']) {

			//Obtém os séculos completos que fazem parte dos anos passados por parâmetros
			$seculos = get_complete_roman_centuries($aParams['ano_inicio'], $aParams['ano_fim']);

			$anos = sprintf("(d.ano_documento BETWEEN %d AND %d)",
							mysqlx_real_escape_string(intval($aParams['ano_inicio'])),
							mysqlx_real_escape_string(intval($aParams['ano_fim'])));

			if (!empty($seculos)) {
				$aWhere[] = sprintf("($anos OR (d.seculo_documento IN ('%s')))",
				implode("', '", $seculos));
			}
			else {
				$aWhere[] = $anos;
			}
		}
		elseif ($aParams['ano_inicio']) {
			$aWhere[] = sprintf("(d.ano_documento = %d)",
								mysqlx_real_escape_string(intval($aParams['ano_inicio'])));
		}
		elseif ($aParams['ano_fim']) {
			$aWhere[] = sprintf("(d.ano_documento = %d)",
								mysqlx_real_escape_string(intval($aParams['ano_fim'])));
		}

		if ($aParams['seculo_inicio'] && $aParams['seculo_fim']) {
			$intervalo_seculos = get_roman_centuries($aParams['seculo_inicio'], $aParams['seculo_fim']);
			$ano_inicio = get_first_century_year($aParams['seculo_inicio']);
			$ano_fim = get_first_century_year($aParams['seculo_fim']);
			if ($ano_inicio > $ano_fim) {
				$ano_fim = $ano_inicio;
			}
			$aWhere[] = sprintf("((d.seculo_documento IN ('%s')) OR (d.ano_documento BETWEEN %d AND %d))",
			implode("', '", $intervalo_seculos),
			$ano_inicio,
			$ano_fim+99);
		}
		elseif ($aParams['seculo_inicio']) {
			$ano_inicio = get_first_century_year($aParams['seculo_inicio']);
			$aWhere[] = sprintf("((d.seculo_documento = '%s') OR (d.ano_documento BETWEEN %d AND %d))",
								mysqlx_real_escape_string($aParams['seculo_inicio']),
			$ano_inicio,
			$ano_inicio+99);
		}
		elseif ($aParams['seculo_fim']) {
			$ano_fim = get_first_century_year($aParams['seculo_fim']);
			$aWhere[] = sprintf("((d.seculo_documento = '%s') OR (d.ano_documento BETWEEN %d AND %d))",
								mysqlx_real_escape_string($aParams['seculo_fim']),
			$ano_fim,
			$ano_fim+99);
		}

		if ($aParams['descricao']) {
			$aWhere[] = sprintf("(d.descricao REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['descricao']));
		}
		if ($aParams['idioma']) {
			$aWhere[] = sprintf("(d.Idioma_id = '%d')",
								mysqlx_real_escape_string($aParams['idioma']));
		}
		
		if ($aParams['editora']) {
			$aWhere[] = sprintf("(d.nome_editora REGEXP '[[:<:]]%s[[:>:]]' = 1)",
					mysqlx_real_escape_string($aParams['editora']));
		}
		if ($aParams['localeditora']) {
			$aWhere[] = sprintf("(d.local_editoras REGEXP '[[:<:]]%s[[:>:]]' = 1)",
					mysqlx_real_escape_string($aParams['localeditora']));
		}
		
		if ($aParams['fonte']) {
			$aWhere[] = sprintf("(df.Fonte_id = %d)",
								mysqlx_real_escape_string($aParams['fonte']));
		}

		if (isset($aParams['somente_midias']) && $aParams['somente_midias']) {
			$aWhere[] = '(midias > 0)';
		}

		if ($aWhere) {
			if ($sWhere) {
				$sWhere.= ' AND ' . implode(' AND ', $aWhere);
			}
			else {
				$sWhere.= ' WHERE ' . implode(' AND ', $aWhere);
			}
		}
		return $sWhere;
	}

	protected function fnBuildOrderClause($aColumns, $aParams) {
		$sOrder = '';
		if (isset($aParams['iSortCol_0']) && ($aParams['iSortCol_0'] != 'escore')) {
			$sOrder = 'ORDER BY ';
			for ($i=0; $i<$aParams['iSortingCols']; $i++) {
				if ($aColumns[$aParams['iSortCol_'.$i]] == 'titulo'){
					$aColumns[$aParams['iSortCol_'.$i]] = 'titulo_normalizado';
				}
				elseif ($aColumns[$aParams['iSortCol_'.$i]] == 'autores_nome_usual') {
					$aColumns[$aParams['iSortCol_'.$i]] = 'autores_nome_usual_normalizado';
				}
				$sOrder .= $aColumns[$aParams['iSortCol_'.$i]] . ' ' . mysqlx_real_escape_string($aParams['sSortDir_'.$i]) . ', ';
			}
			$sOrder = substr($sOrder, 0, -2);
		}
		return $sOrder;
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
		$controller = ControleDocumentos::getInstance();

		$personalizacao = false;
		if (Auth::check()){
			$personalizacao = Auth::checa_personalizacao();
		}

		while ($aRow = mysqli_fetch_array($rResult)) {

			$aaData[$sIndex] = array();
				
			$aKeys = array_keys($aRow);
			foreach ($aKeys as $sKey) {
				// Se índice não é numérico
				if (!is_numeric($sKey)) {
					if ($aRow[$sKey] === null) {
						$aRow[$sKey] = '';
					}
					switch ($sKey) {
						case 'midias':
							if ($aRow['midias'] == 0) {
								$midia = '<img src="' .IMAGES_URI . 'ico_download2_disabled.png" alt="Não disponível para visualização" title="Não disponível para visualização" />';
							}
							else {
								$midia = '<a href="'.DOCUMENTOS_URI.'?action=midias&id='.$aRow['id'].'" alt="Visualizar obra" title="Visualizar obra">
                       				      <img src="' .IMAGES_URI . 'ico_download2.png" />
                       				      </a>';
							}
							$aaData[$sIndex][] = $midia;
							break;

						case 'titulo':
							$titulo = '<a href="'.DOCUMENTOS_URI.'?id='.$aRow['id'].'" alt="Detalhes do documento" title="Detalhes do documento">' . $aRow['titulo'] . '</a>';
							$aaData[$sIndex][] = $titulo;
							break;

						case 'autores_nome_usual':
							if ($aRow['autores_nome_usual']) {
								$aaData[$sIndex][] = $aRow['autores_nome_usual'];
							}
							else {
								$aaData[$sIndex][] = $aRow['autores_nome_completo'];
							}
							break;

						case 'nome_tipodocumento':
							$aaData[$sIndex][] = $aRow['nome_tipodocumento'];
							break;

						case 'nome_genero':
							$aaData[$sIndex][] = $aRow['nome_genero'];
							break;

						case 'ano_documento':
							if ($aRow['seculo_documento']) {
								$aaData[$sIndex][] = $aRow['seculo_documento'];
							}
							else if ($aRow['ano_documento']) {
								$aaData[$sIndex][] = $aRow['ano_documento'];
							}
							else {
								$aaData[$sIndex][] = '';
							}
							break;
					}
				}
			}
			
			if ($personalizacao) {
				$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : '';
				$aRow = $controller->calc_adaptacao($aRow, $id_usuario);
				$aaData[$sIndex][] = $aRow['escore_total'];
			}
				
			$sIndex++;
		}
		//        // Retirando o indice que contem o id
		//        $aaData_sliced = array();
		//        foreach ($aaData as $array) {
		//    		$array = array_slice($array, 1);
		//    		$v = $array[5];
		//    		array_pop($array);
		//    		array_splice($array, 0, 0, $v);
		//    		array_push($aaData_sliced, $array);
		//    	}
		//        return $aaData_sliced;
		return $aaData;
	}

	function getSeculosCompletos($ano_inicio, $ano_fim) {
		$seculo_inicio = get_roman_century($ano_inicio);
		$seculo_fim = get_roman_century($ano_fim);
		$seculos = get_roman_centuries($seculo_inicio, $seculo_fim);
		if (!substr($ano_inicio, '01')) {
			array_shift($seculos);
		}
		if (!substr($ano_fim, '00')) {
			array_pop($seculos);
		}
		return $seculos;
	}
}
