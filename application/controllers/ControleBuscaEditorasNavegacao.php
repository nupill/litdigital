<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleBuscaEditorasNavegacao extends DataTables {
    
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
		$sQuery = "SELECT SQL_CALC_FOUND_ROWS
					e.nome,
					e.local,
					e.id
                   FROM {$aParams['sTable']} e
                   {$aClauses['sWhere']}
                   GROUP BY id
                   {$aClauses['sOrder']}
                   {$aClauses['sLimit']}";
 //                  FB::log($sQuery);
 
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
        if ($aParams['letra']) {
        	$letra = strtolower($aParams['letra']);
           
        	$vogais = array('a', 'e', 'i', 'o', 'u');
        	
        	if (in_array($letra, $vogais)) {
        		$where_letra = sprintf(" normaliza(nome) LIKE '%s%%' ",
        		mysqlx_real_escape_string($letra));
        	}
        	else {
        		$where_letra = sprintf(" nome LIKE '%s%%' ",
        					mysqlx_real_escape_string($letra));
        	}
                
            $aWhere[] = $where_letra;
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
    	if (isset($aParams['iSortCol_0'])) {
    		$sOrder = 'ORDER BY ';
    		for ($i=0; $i<$aParams['iSortingCols']; $i++) {
    			if ($aColumns[$aParams['iSortCol_'.$i]] == 'local_nascimento'){
    				$aColumns[$aParams['iSortCol_'.$i]] = 'local_nascimento';
    			}
    			else {
    				if ($aColumns[$aParams['iSortCol_'.$i]] == 'nome_completo'){
    					$aColumns[$aParams['iSortCol_'.$i]] = 'nome_completo';
    				}
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
        while ($aRow = mysqli_fetch_array($rResult)) {
            $aaData[$sIndex] = array();
            $aKeys = array_keys($aRow);
            foreach ($aKeys as $sKey) {
                if (!is_numeric($sKey)) {
                    if ($aRow[$sKey] === null) {
                        $aRow[$sKey] = '';
                    }
                    switch ($sKey) {
                    	case 'nome':
							$nome = '<a href="'.EDITORAS_URI.'?id='.$aRow['id'].'" alt="Detalhes da editora" title="Detalhes da editora">';
							$nome.= $aRow[$sKey];
							$nome.= '</a>';
							$aaData[$sIndex][] = $nome;
							break;
						case 'local':
							$local = '';
							if ($aRow['local']) {
								$local = $aRow[$sKey];
							}
							$aaData[$sIndex][] = $local;
							break;
                    }
                }
            }
            $sIndex++;
        }
        return $aaData;
    }
}