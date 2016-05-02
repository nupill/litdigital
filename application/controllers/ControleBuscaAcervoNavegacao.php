<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleBuscaAcervoNavegacao extends DataTables {
    
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
    
    
    protected function fnBuildQuery($aParams) {
        /* Query statements */
        $aClauses = $this->fnBuildQueryClauses($aParams);
        
        /* Query */
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS {$aParams['sColumns']}, id
                   FROM {$aParams['sTable']}
                   {$aClauses['sWhere']}
                   {$aClauses['sOrder']}
                   {$aClauses['sLimit']}";
                   FB::log($sQuery);
             //	   exit($sQuery);
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
                //$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
                $sWhere.= $aColumn . " REGEXP '[[:<:]]" . mysqlx_real_escape_string($sSearch) . "[[:>:]]' = 1 OR ";
            }
            $sWhere = substr($sWhere, 0, -4);
            $sWhere.= ')';
        }
        //Procura inicial
        
        //Adiciona os filtros
        $aWhere = array();
        if ($aParams['acervo']) {
        	if ($aParams['acervo'] != 'todos') {
        		$acervo = $aParams['acervo'];        		
        		$where = sprintf(" Acervo_id = '%s' ",
        		mysqlx_real_escape_string( $aParams['acervo']));
        		
        		$aWhere[] = $where;
        	}
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
							$aaData[$sIndex][] = $aRow['autores_nome_usual'];
							break;

						case 'nome_tipodocumento':
							$aaData[$sIndex][] = $aRow['nome_tipodocumento'];
							break;

						case 'nome_genero':
							$aaData[$sIndex][] = $aRow['nome_genero'];
							break;

						case 'ano_documento':

					//		if ($aRow['seculo_documento'])
					//			$aaData[$sIndex][] = $aRow['seculo_documento'];
							if ($aRow['ano_documento'])
								$aaData[$sIndex][] = $aRow['ano_documento'];
							else
								$aaData[$sIndex][] = '';
							break;

					}
				}
			}
				
			$sIndex++;
		}
		return $aaData;
	}
	
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
	
}