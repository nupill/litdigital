<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/DataTables.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

class ControleBuscaAutor extends DataTables {

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
					a.nome_usual,
					CONCAT (cn.nome,',',en.sigla,',',pn.nome) AS loc_nasc,
					a.estado_nasc_id,
				    a.pais_nasc_id,
				    a.estado_morte_id,
				    a.pais_morte_id,
				    a.id,
				    a.pseudonimo,
				    a.nome_completo,
				    a.ano_nascimento,
				    a.seculo_nascimento,
				    a.ano_morte,
				    a.seculo_morte,
				    cn.nome as cidade_nasc,
				    cm.nome as cidade_morte
                   FROM {$aParams['sTable']} a
				   JOIN AutorFonte df
				    ON (id = df.Autor_id)
				   LEFT JOIN cidades cn
				    ON (cidade_nasc_id = cn.id)
				   LEFT JOIN cidades cm
				    ON (cidade_morte_id = cm.id)
				   LEFT JOIN estados en
				    ON (estado_nasc_id = en.id)
				   LEFT JOIN Paises pn
				    ON (pais_nasc_id = pn.id)
                   {$aClauses['sWhere']}
                   GROUP BY id
                   {$aClauses['sOrder']}
                   {$aClauses['sLimit']}";
//         FB::log($sQuery);
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
				if ($aColumn <> 'loc_nasc') 
				$sWhere.= $aColumn . " LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
				//$sWhere.= $aColumn . " REGEXP '[[:<:]]" . mysqlx_real_escape_string($sSearch) . "[[:>:]]' = 1 OR ";
			}
			$sWhere.= "nome_completo LIKE '%" . mysqlx_real_escape_string($sSearch) . "%' OR ";
			$sWhere.= "pseudonimo LIKE '%" . mysqlx_real_escape_string($sSearch) . "%'";
			$sWhere.= ')';
		}
		//Procura inicial

		//Adiciona os filtros
		$aWhere = array();
		if ($aParams['nome']) {

			if (get_magic_quotes_gpc()) {
				$aParams['nome'] = stripslashes($aParams['nome']);
			}

			//Verifica a forma de busca
			if ($aParams['nome'] && $aParams['nome'][0] == '"' && $aParams['nome'][strlen($aParams['nome'])-1] == '"') { //Frase exata
				$aParams['nome'] = str_replace('"', '', $aParams['nome']);
				if (!remove_stop_words($aParams['nome'])) {
					return false;
				}
			}
			else { //Qualquer palavra
				$aParams['nome'] = remove_stop_words($aParams['nome']);
				if (!$aParams['nome']) {
					return false;
				}
				$aParams['nome'] = explode(' ', $aParams['nome']);
			}

			if (is_array($aParams['nome'])) { //Qualquer palavra
				$where_nome = "(";
				foreach ($aParams['nome'] as $search_term) {
					$search_term = mysqlx_real_escape_string(trim(strtolower(normalize($search_term))));
					$where_nome .= sprintf(" nome_completo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                            nome_usual_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                            pseudonimo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR",
											$search_term,
											$search_term,
											$search_term);
				}
				$where_nome = substr($where_nome, 0, -3); //Remove o último OR
				$where_nome.= ")";
				$aWhere[] = $where_nome;
			}
			else { //Frase exata
				$search_term = mysqlx_real_escape_string(trim(strtolower(normalize($aParams['nome']))));
				$aWhere[] = sprintf("(nome_completo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                    nome_usual_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1 OR
                                    pseudonimo_normalizado REGEXP '[[:<:]]%s[[:>:]]' = 1) ",
									$search_term,
									$search_term,
									$search_term);
			}
		}

		if ($aParams['ano_nascimento_inicio'] > $aParams['ano_nascimento_fim']) {
			$aParams['ano_nascimento_fim'] = '';
		}

		if ($aParams['ano_nascimento_inicio'] && $aParams['ano_nascimento_fim']) {
			$aWhere[] = sprintf("(ano_nascimento BETWEEN %d AND %d)",
								mysqlx_real_escape_string(intval($aParams['ano_nascimento_inicio'])),
								mysqlx_real_escape_string(intval($aParams['ano_nascimento_fim'])));
		}
		elseif ($aParams['ano_nascimento_inicio']) {
			$aWhere[] = sprintf("(ano_nascimento = %d)",
								mysqlx_real_escape_string(intval($aParams['ano_nascimento_inicio'])));
		}
		elseif ($aParams['ano_nascimento_fim']) {
			$aWhere[] = sprintf("(ano_nascimento = %d)",
								mysqlx_real_escape_string(intval($aParams['ano_nascimento_fim'])));
		}

		if ($aParams['seculo_nascimento_inicio'] && $aParams['seculo_nascimento_fim']) {
			$intervalo_seculos = get_roman_centuries($aParams['seculo_nascimento_inicio'], $aParams['seculo_nascimento_fim']);
			$ano_inicio = get_first_century_year($aParams['seculo_nascimento_inicio']);
			$ano_fim = get_first_century_year($aParams['seculo_nascimento_fim']);
			if ($ano_inicio > $ano_fim) {
				$ano_fim = $ano_inicio;
			}
			$aWhere[] = sprintf("((seculo_nascimento IN ('%s')) OR (ano_nascimento BETWEEN %d AND %d))",
								implode("', '", $intervalo_seculos),
								$ano_inicio,
								$ano_fim+99);
		}
		elseif ($aParams['seculo_nascimento_inicio']) {
			$ano_inicio = get_first_century_year($aParams['seculo_nascimento_inicio']);
			$aWhere[] = sprintf("((seculo_nascimento = '%s') OR (ano_nascimento BETWEEN %d AND %d))",
								mysqlx_real_escape_string($aParams['seculo_nascimento_inicio']),
								$ano_inicio,
								$ano_inicio+99);
		}
		elseif ($aParams['seculo_nascimento_fim']) {
			$ano_fim = get_first_century_year($aParams['seculo_nascimento_fim']);
			$aWhere[] = sprintf("((seculo_nascimento = '%s') OR (ano_nascimento BETWEEN %d AND %d))",
								mysqlx_real_escape_string($aParams['seculo_nascimento_fim']),
								$ano_fim,
								$ano_fim+99);
		}

		if ($aParams['cidade_nasc']) {
			$aWhere[] = sprintf("(cn.nome REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['cidade_nasc']));
		}

		if ($aParams['regiao_nasc']) {
			$aWhere[] = sprintf("(estado_nasc_id REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['regiao_nasc']));
		}

		if ($aParams['pais_nasc']) {
			$aWhere[] = sprintf("(pais_nasc_id REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['pais_nasc']));
		}

		if (isset($aParams['catarinense']) && $aParams['catarinense']) {
			$aWhere[] = "(catarinense = 1)";
		}

		if ($aParams['ano_morte_inicio'] > $aParams['ano_morte_fim']) {
			$aParams['ano_morte_fim'] = '';
		}

		if ($aParams['ano_morte_inicio'] && $aParams['ano_morte_fim']) {
			$aWhere[] = sprintf("(ano_morte BETWEEN '%s' AND '%s')",
								mysqlx_real_escape_string($aParams['ano_morte_inicio']),
								mysqlx_real_escape_string($aParams['ano_morte_fim']));
		}
		elseif ($aParams['ano_morte_inicio']) {
			$aWhere[] = sprintf("(ano_morte = '%s')",
								mysqlx_real_escape_string($aParams['ano_morte_inicio']));
		}
		elseif ($aParams['ano_morte_fim']) {
			$aWhere[] = sprintf("(ano_morte = '%s')",
								mysqlx_real_escape_string($aParams['ano_morte_fim']));
		}

		if ($aParams['seculo_morte_inicio'] && $aParams['seculo_morte_fim']) {
			$intervalo_seculos = get_roman_centuries($aParams['seculo_morte_inicio'], $aParams['seculo_morte_fim']);
			$ano_inicio = get_first_century_year($aParams['seculo_morte_inicio']);
			$ano_fim = get_first_century_year($aParams['seculo_morte_fim']);
			if ($ano_inicio > $ano_fim) {
				$ano_fim = $ano_inicio;
			}
			$aWhere[] = sprintf("((seculo_morte IN ('%s')) OR (ano_morte BETWEEN '%s' AND '%s'))",
								implode("', '", $intervalo_seculos),
								$ano_inicio,
								$ano_fim+99);
		}
		elseif ($aParams['seculo_morte_inicio']) {
			$ano_inicio = get_first_century_year($aParams['seculo_morte_inicio']);
			$aWhere[] = sprintf("((seculo_morte = '%s') OR (ano_morte BETWEEN '%s' AND '%s'))",
								mysqlx_real_escape_string($aParams['seculo_morte_inicio']),
								$ano_inicio,
								$ano_inicio+99);
		}
		elseif ($aParams['seculo_morte_fim']) {
			$ano_fim = get_first_century_year($aParams['seculo_morte_fim']);
			$aWhere[] = sprintf("((seculo_morte = '%s') OR (ano_morte BETWEEN '%s' AND '%s'))",
								mysqlx_real_escape_string($aParams['seculo_morte_inicio']),
								$ano_fim,
								$ano_fim+99);
		}
		
		if ($aParams['cidade_morte']) {
			$aWhere[] = sprintf("(pt_normalize(cm.nome) REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string(trim(strtolower(normalize($aParams['cidade_morte'])))));
		}
		
		if ($aParams['regiao_morte']) {
			$aWhere[] = sprintf("(estado_morte_id REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['regiao_morte']));
		}

		if ($aParams['pais_morte']) {
			$aWhere[] = sprintf("(pais_morte_id REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['pais_morte']));
		}

		if ($aParams['descricao']) {
			$aWhere[] = sprintf("(descricao REGEXP '[[:<:]]%s[[:>:]]' = 1)",
								mysqlx_real_escape_string($aParams['descricao']));
		}
		if ($aParams['fonte']) {
			$aWhere[] = sprintf("(df.Fonte_id = %d)",
								mysqlx_real_escape_string($aParams['fonte']));
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
				$sDir = mysqlx_real_escape_string($aParams['sSortDir_'.$i]);
				if ($aColumns[$aParams['iSortCol_'.$i]] == 'loc_nasc') {
					$sOrder.= "ano_nascimento $sDir, seculo_nascimento $sDir, pt_normalize(cn.nome) $sDir, pt_normalize(en.nome) $sDir, pt_normalize(pn.nome) $sDir, ";
				}
				elseif ($aColumns[$aParams['iSortCol_'.$i]] == 'loc_morte') {
					$sOrder.= "ano_morte $sDir, seculo_morte $sDir, pt_normalize(cm.nome) $sDir, pt_normalize(em.nome) $sDir, pt_normalize(pm.nome) $sDir, ";
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