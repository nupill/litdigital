<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/ControleDocumentos.php');

class ControleBuscaSimples extends DataTables {

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
//		$iFilteredTotal = mysqli_num_rows($rResult);

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
		$sQuery = "SELECT SQL_CALC_FOUND_ROWS
                    id,
                    midias,
                    titulo,
                    Autor_ids,
                    autores_nome_usual,
                    autores_nome_completo,
                    nome_tipodocumento,
                    Genero_id,
                    nome_genero,
                    ano_documento,
                    seculo_documento
                   FROM DocumentoConsulta
                   {$aClauses['sWhere']}";
                   //{$aClauses['sOrder']}";
                   //{$aClauses['sLimit']}";

                   /*
                    $sQuery = "SELECT SQL_CALC_FOUND_ROWS
                    dw.*,
                    t.nome AS tipo,
                    d.ano_producao,
                    g.nome AS genero,
                    g.id AS Genero_id,
                    o.seculo_producao,
                    o.seculo_publicacao,
                    o.ano_publicacao_inicio
                    FROM DocumentoView dw
                    JOIN TipoDocumento t
                    ON t.id = dw.TipoDocumento_id
                    JOIN Documento d
                    ON d.id = dw.id
                    JOIN Genero g
                    ON g.id = d.Genero_id
                    LEFT JOIN ObraLiteraria o
                    ON o.Documento_id = dw.id
                    {$aClauses['sWhere']}";
                    //{$aClauses['sOrder']}";
                    //{$aClauses['sLimit']}";
                    */
	    $aColumns = explode(',', $aParams['sColumns']);
	    if ($aColumns[$aParams['iSortCol_0']] != 'ano_documento' && $aColumns[$aParams['iSortCol_0']] != 'escore') {
	        $sQuery.= ' ' . $aClauses['sOrder'];
	    }
	    
	    return $sQuery;
	}

	protected function fnBuildWhereClause($aColumns, $aParams) {
		$sWhere = '';
		//Procurar nos resultados
		if (is_array($aColumns) && $aParams['sSearch'] != '') {
			$sSearch = preg_replace('/\s\s+/', ' ', $aParams['sSearch']); //Remove extra spaces
			$sSearch = str_replace(' ', '%', $sSearch); //Replace space with %
			$sWhere = 'WHERE (';
			//array_shift($aColumns);
			foreach ($aColumns as $aColumn) {
				if ($aColumn != 'escore') {
					$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
					//$sWhere.= $aColumn . " REGEXP '[[:<:]]" . mysqlx_real_escape_string($sSearch) . "[[:>:]]' = 1 OR ";
				}
			}
			$sWhere = substr($sWhere, 0, -4);
			//			$sMatch = '';
			//			$sAgainst = '';
			//
			//            unset($aColumns[array_search('escore', $aColumns)]);
			//            $sMatch = implode($aColumns, "`,`");
			//            $sMatch = "MATCH (`" . $sMatch . "`)";
			//
			//            $sAgainst = "AGAINST ('" . mysqlx_real_escape_string($sSearch) . "')";
			//
			//            $sWhere.= "$sMatch $sAgainst";
			$sWhere.= ')';
		}
		//Procura inicial
		if ($aParams['termo']) {

			if (get_magic_quotes_gpc()) {
				$aParams['termo'] = stripslashes($aParams['termo']);
			}

			//Verifica a forma de busca
			if ($aParams['termo'] && $aParams['termo'][0] == '"' && $aParams['termo'][strlen($aParams['termo'])-1] == '"') { //Frase exata
				$aParams['termo'] = str_replace('"', '', $aParams['termo']);
				if (!remove_stop_words($aParams['termo'])) {
					return false;
				}
			}
			else { //Qualquer palavra
				$aParams['termo'] = remove_stop_words($aParams['termo']);
				if (!$aParams['termo']) {
					return false;
				}
				$aParams['termo'] = explode(' ', $aParams['termo']);
			}

			if (!$sWhere) {
				$sWhere = ' WHERE (';
			}
			else {
				$sWhere.= ' AND (';
			}
			if (is_array($aParams['termo'])) { //Qualquer palavra
				foreach ($aParams['termo'] as $search_term) {
					$search_term = mysqlx_real_escape_string(trim(strtolower(normalize($search_term))));
					$sWhere.= sprintf(" titulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                    				  subtitulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
									  titulo_alternativo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                    				  autores_nome_completo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
									  autores_nome_usual_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR",
									  $search_term,
									  $search_term,
									  $search_term,
									  $search_term,
									  $search_term);
				}
				$sWhere = substr($sWhere, 0, -3); //Remove o último OR
				$sWhere.= ')';
			}
			else { //Frase exata
				$search_term = mysqlx_real_escape_string(trim(strtolower(normalize($aParams['termo']))));
				$sWhere.= sprintf("titulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
               					   subtitulo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
								   titulo_alternativo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
               					   autores_nome_completo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                   autores_nome_usual_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								   $search_term,
								   $search_term,
								   $search_term,
								   $search_term,
								   $search_term);
			}
		}

		if (isset($aParams['somente_midias']) && $aParams['somente_midias']) {
			if ($sWhere) {
				$sWhere.= ' AND midias > 0';
			}
			else {
				$sWhere = 'WHERE midias > 0';
			}
		}
		return $sWhere;
	}
	
	protected function fnBuildOrderClause($aColumns, $aParams) {
		$sOrder = '';
		if (isset($aParams['iSortCol_0'])) {
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

	protected function fnParseResult($rResult) {
		$aaData = array();
		$iIndex = 0;
		$controller = ControleDocumentos::getInstance();

		$personalizacao = false;
		if (Auth::check()){
			$personalizacao = Auth::checa_personalizacao();
		}

		while ($aRow = mysqli_fetch_array($rResult)) {

			$aaData[$iIndex] = array();

			if ($personalizacao){
				$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : '';
				$aRow = $controller->calc_adaptacao($aRow, $id_usuario);
			}
			
			if ($aRow['autores_nome_usual']) {
				$aRow['autores'] = replace_quotes($aRow['autores_nome_usual'], false);
				$aRow['autores'] = explode(';', $aRow['autores']);
			}
			else {
				$aRow['autores'] = replace_quotes($aRow['autores_nome_completo'], false);
				$aRow['autores'] = explode(';', $aRow['autores']);
			}
			$aRow['Autor_ids'] = explode(';', $aRow['Autor_ids']);

			if (sizeof($aRow['Autor_ids']) != sizeof($aRow['autores'])) {
				$reverseAutores = implode(';', $aRow['autores']);
				$aRow['autores'] = array();
				$aRow['autores'][0] = $reverseAutores;
				$reverseAutoresIds = implode(';', $aRow['Autor_ids']);
				$aRow['Autor_ids'] = array();
				$aRow['Autor_ids'][0] = $reverseAutoresIds;
			}
			
			if ($aRow['midias']) {
				$aaData[$iIndex][] = '<a href="'.DOCUMENTOS_URI.'?action=midias&id='.$aRow['id'].'">' .
                                     '<img src="'.IMAGES_URI.'ico_download2.png" alt="Visualizar obra" title="Visualizar obra" />' .
                                     '</a>';
			}
			else {
				$aaData[$iIndex][] = '<img src="'.IMAGES_URI.'ico_download2_disabled.png" alt="Não disponível para visualização" title="Não disponível para visualização" />';
			}

			$aaData[$iIndex][] = '<a href="'.DOCUMENTOS_URI.'?id='.$aRow['id'].'" alt="Detalhes sobre a obra" title="Detalhes sobre a obra">'.$aRow['titulo'].'</a>';

			$autores = '';
			for ($i=0; $i<sizeof($aRow['autores']); $i++) {
				$autores.= '<a href="'.AUTORES_URI.'?id='.$aRow['Autor_ids'][$i].'" alt="Detalhes do autor" title="Detalhes do autor">'.trim($aRow['autores'][$i]).'</a>';
// 				if ($i<sizeof($aRow['autores'])-1) {
// 					$autores.= '; ';
// 				}
			}

			$aaData[$iIndex][] = $autores;
			$aaData[$iIndex][] = $aRow['nome_tipodocumento'];
			$aaData[$iIndex][] = $aRow['nome_genero'];			

			$ano = '';
			if ($aRow['ano_documento']) {
				$ano = $aRow['ano_documento'];
			}
			elseif ($aRow['seculo_documento']) {
				$ano = $aRow['seculo_documento'];
			}

			$aaData[$iIndex][] = $ano ? $ano : '-';

			if ($personalizacao){
				$aaData[$iIndex][] = $aRow['escore_total'];
			}

			$iIndex++;
		}
		return $aaData;
	}
}
