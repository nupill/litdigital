<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
require_once(dirname(__FILE__) . '/ControleFatosHistoricos.php');


class ControleBuscaAutorFato extends DataTables {

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
					nome_usual,
					CONCAT (loc_nasc,',',regiao_nasc,',',pais_nasc) AS loc_nasc,
				    CONCAT (loc_morte,',',regiao_morte,',',pais_morte) AS loc_morte,
				    id,
				    pseudonimo,
				    nome_completo,
				    ano_nascimento,
				    seculo_nascimento,
				    ano_morte,
				    seculo_morte
                   FROM {$aParams['sTable']}
				   JOIN AutorFonte df
				    ON (id = df.Autor_id)
                   {$aClauses['sWhere']}
                   GROUP BY id
                   {$aClauses['sOrder']}
                   {$aClauses['sLimit']}";
//         FB::log($sQuery);
        return $sQuery;
	}

	protected function fnBuildWhereClause($aColumns, $aParams) {
		$sWhere = '';
		if ($aParams['fato_id']) {
			$fato_id = $aParams['fato_id'];
			$controller= ControleFatosHistoricos::getInstance();
			$fato=$controller->get($fato_id);
			$fato=$fato[0];
			
			$ano_inicio=$fato['ano_inicio'];
			$ano_fim=$fato['ano_fim'];
			$sWhere = sprintf("WHERE ano_nascimento <=  %d AND (ano_morte  >= %d OR ano_morte IS NULL)",
								mysqlx_real_escape_string($ano_fim),
								mysqlx_real_escape_string($ano_inicio));
			
		}

		return $sWhere;
	}

	protected function fnBuildOrderClause($aColumns, $aParams) {
		$sOrder = '';
		if (isset($aParams['iSortCol_0'])) {
			$sOrder = 'ORDER BY ';
			for ($i=0; $i<$aParams['iSortingCols']; $i++) {
				$sDir = mysqlx_real_escape_string($aParams['sSortDir_'.$i]);
				if ($aColumns[$aParams['iSortCol_'.$i]] == 'loc_nasc') {
					$sOrder.= "ano_nascimento $sDir, seculo_nascimento $sDir, pt_normalize(loc_nasc) $sDir, pt_normalize(regiao_nasc) $sDir, pt_normalize(pais_nasc) $sDir, ";
				}
				elseif ($aColumns[$aParams['iSortCol_'.$i]] == 'loc_morte') {
					$sOrder.= "ano_morte $sDir, seculo_morte $sDir, pt_normalize(loc_morte) $sDir, pt_normalize(regiao_morte) $sDir, pt_normalize(pais_morte) $sDir, ";
				}
				elseif ($aColumns[$aParams['iSortCol_'.$i]] == 'nome_usual') {
					$sOrder.= "nome_usual_normalizado $sDir, ";
				}
				else {
					$sOrder.= $aColumns[$aParams['iSortCol_'.$i]] . ' ' . $sDir  . ', ';
				}
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
						case 'nome_usual':
							$nome = '<a href="'.AUTORES_URI.'?id='.$aRow['id'].'" alt="Detalhes do autor" title="Detalhes do autor">';
							$nome.= $aRow[$sKey];
							if ($aRow['nome_completo'] && $aRow['nome_completo'] != $aRow['nome_usual']) {
								$nome.= "<br /><em>{$aRow['nome_completo']}</em>";
							}
							if ($aRow['pseudonimo']) {
								$nome.= "<br /><em>{$aRow['pseudonimo']}</em>";
							}
							$nome.= '</a>';
							$aaData[$sIndex][] = $nome;
							break;
						case 'loc_nasc':
							$nascimento = '';
							if ($aRow['ano_nascimento']) {
								$nascimento.= $aRow['ano_nascimento'];
							}
							elseif ($aRow['seculo_nascimento']) {
								$nascimento.= $aRow['seculo_nascimento'];
							}
							if ($aRow['loc_nasc']) {
								$aRow['loc_nasc'] = str_replace(",,",",", $aRow['loc_nasc']);
								if (substr($aRow['loc_nasc'],0,1) == ','){
									$aRow['loc_nasc'] = substr($aRow['loc_nasc'],1);
								}
								$aRow['loc_nasc'] = str_replace(",",", ", $aRow['loc_nasc']);
								$nascimento.= $nascimento ? ': ' . $aRow['loc_nasc'] : $aRow['loc_nasc'];
							}
							$aaData[$sIndex][] = $nascimento;
							break;
						case 'loc_morte':
								$morte = '';
								if ($aRow['ano_morte']) {
									$morte.= $aRow['ano_morte'];
								}
								elseif ($aRow['seculo_morte']) {
									$morte.= $aRow['seculo_morte'];
								}
								if ($aRow['loc_morte']) {
									$aRow['loc_morte'] = str_replace(",,",",", $aRow['loc_morte']);
									if (substr($aRow['loc_morte'],0,1) == ','){
										$aRow['loc_morte'] = substr($aRow['loc_morte'],1);
									}
									$aRow['loc_morte'] = str_replace(",",", ", $aRow['loc_morte']);
									$morte.= $morte ? ': ' . $aRow['loc_morte'] : $aRow['loc_morte'];
								}
								$aaData[$sIndex][] = $morte;
								break;
						default:
							if ($sKey != 'id' && $sKey != 'pseudonimo' && $sKey != 'nome_completo' && $sKey != 'ano_nascimento' && $sKey != 'seculo_nascimento' && $sKey != 'ano_morte' && $sKey != 'seculo_morte' ) {
								$aaData[$sIndex][] = $aRow[$sKey];
							}
					}
				}
			}
			$sIndex++;
		}
		return $aaData;
	}

}