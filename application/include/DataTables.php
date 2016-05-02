<?php 
require_once(dirname(__FILE__) . '/DB.php');
require_once(dirname(__FILE__) . '/mysqli.php');
require_once(dirname(__FILE__) . '/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/php-gettext/i18n.php');
require_once(dirname(__FILE__) . '/../controllers/ControleDocumentos.php');

class DataTables {

	public function getTableData($aParams) {
	    $sQuery = $this->fnBuildQuery($aParams);
            $sQuery1= "SELECT FOUND_ROWS()";

	    try {
            $rResult = $this->DB->query($sQuery);
            $rResultFilterTotal = $this->DB->query($sQuery1);
            $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
            $iFiltereTotal = $aResultFilterTotal[0];
	    }
	    catch (Exception $e) {
	        Logger::log($e->getMessage() . " (Query: $sQuery)", __FILE__);
	        return $e->getMessage() . " (Query: $sQuery)";
	    }
        /* Output parameters */
        $iEcho = isset($aParams['sEcho']) ? $aParams['sEcho'] : 0;
        $iTotal = $this->count($aParams['sTable']);
        
     //   $iFilteredTotal = mysqli_num_rows($rResult);
        $sColumns = isset($aParams['sColumns']) ? $aParams['sColumns'] : '';
        $aaData = $this->fnParseResult($rResult);
        /* Output */
        $sOutput = $this->fnBuildOutput($iEcho, $iTotal, $iFiltereTotal, $sColumns, $aaData);
        return json_encode($sOutput);
    }
    
    protected function fnBuildQuery($aParams) {
        // Query statements 
        $aClauses = $this->fnBuildQueryClauses($aParams);
        
        // Query 
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS {$aParams['sColumns']}
                   FROM {$aParams['sTable']}
                   {$aClauses['sWhere']}
                   {$aClauses['sOrder']}
                   {$aClauses['sLimit']}";
        return $sQuery;
    }
    
    protected function fnBuildQueryClauses($aParams) {
        
        $aParams['sSearch'] = isset($aParams['sSearch']) ? $aParams['sSearch'] : '';
        $aParams['iDisplayStart'] = isset($aParams['iDisplayStart']) ? $aParams['iDisplayStart'] : 0;
        $aParams['iDisplayLength'] = isset($aParams['iDisplayLength']) ? $aParams['iDisplayLength'] : 1000;
        $aParams['sColumns'] = isset($aParams['sColumns']) ? $aParams['sColumns'] : '';
        $aColumns = explode(',', $aParams['sColumns']);
        
        /* Filtering */
        $sWhere = $this->fnBuildWhereClause($aColumns, $aParams);
        
        /* Ordering */
        $sOrder = $this->fnBuildOrderClause($aColumns, $aParams);
        
        /* Paging */
        $sLimit = $this->fnBuildLimitClause($aParams['iDisplayStart'], $aParams['iDisplayLength']);

        return array('sWhere' => $sWhere, 'sOrder' => $sOrder, 'sLimit' => $sLimit);
    }
    
    protected function fnBuildWhereClause($aColumns, $aParams) {
    	$sSearch = $aParams['sSearch'];
        $sWhere = '';
        if (is_array($aColumns) && $sSearch != '') {
            $sSearch = preg_replace('/\s\s+/', ' ', $sSearch); //Remove extra spaces
            $sSearch = str_replace(' ', '%', $sSearch); //Replace space with %
        	$sWhere = 'WHERE ';
        	array_shift($aColumns);
        	foreach ($aColumns as $aColumn) {
        	    $sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
        	}
        	$sWhere = substr($sWhere, 0, -4);
        }
        return $sWhere;
    }
    
    protected function fnBuildOrderClause($aColumns, $aParams) {
        $sOrder = '';
        if (isset($aParams['iSortCol_0']) && $aParams['iSortCol_0'] != '0') {
        	$sOrder = 'ORDER BY ';
        	for ($i=0; $i<$aParams['iSortingCols']; $i++) {
        		$sOrder .= $aColumns[$aParams['iSortCol_'.$i]] . ' ' . mysqlx_real_escape_string($aParams['sSortDir_'.$i]) . ', ';
        	}
        	$sOrder = substr($sOrder, 0, -2);
        }
        return $sOrder;
    }
    
    protected function fnBuildLimitClause($iDisplayStart, $iDisplayLength) {
        $sLimit = ''; 

        if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
        	$sLimit = 'LIMIT ' . intval($iDisplayStart) . ', ' .
        	                     intval($iDisplayLength);

        } 
        return $sLimit;
    }
    
    protected function fnBuildOutput($iEcho, $iTotal, $iFilteredTotal, $sColumns, $aaData) {
        /* Array Output */
        $sOutput = array();
        $sOutput['sEcho'] = intval($iEcho);
        $sOutput['iTotalRecords'] = intval($iTotal);
        $sOutput['iTotalDisplayRecords'] = intval($iFilteredTotal);
        $sOutput['sColumns'] = $sColumns;
        $sOutput['aaData'] = $aaData;
        return $sOutput;
    }
    
    protected function fnParseResult($rResult) {
        $aaData = array();
        $iIndex = 0;
        while ($aRow = mysqli_fetch_array($rResult))	{
            $aaData[$iIndex] = array();
            $sId = array_shift($aRow);
            $aaData[$iIndex][] =  '<input type="checkbox" name="ids[]" value="'.$sId.'" />';
            array_shift($aRow);
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
        return $aaData;
    }
    
    protected function count($table) {
	    $query = sprintf("SELECT COUNT(*) AS num 
	    	      		  FROM %s", 
	                      mysqlx_real_escape_string($table));
	    try {
            $result = $this->DB->query($query);
            $total_rows = mysqlx_result($result, 0, 'num');
	    }
	    catch (Exception $e) {
	        Logger::log($e->getMessage() . " (Query: $query)", __FILE__);
	        return 0;
	    }
        return $total_rows;
	}
	
}
