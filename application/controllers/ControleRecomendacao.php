<?php
require_once(dirname(__FILE__) . '/ControleDocumentos.php');


class ControleRecomendacao {
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
	
	private function isJson($string) {
 		json_decode($string);
 		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	public function get_obras_recomendadas($id_usuario, $offset, $limit) {
		global $config;
		$url = $config['recommendation_URL']."rec/".$config['recommendation_apikey']."/";
		$url = $url."U".$id_usuario."/".$limit."/CF";

		// $response = file_get_contents($url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);// set post data to true
	//	curl_setopt($ch, CURLOPT_POSTFIELDS,"username=myname&password=mypass");   // post data
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		
		
		if ($this->isJson($response)) {
		
			$response = json_decode($response);
			$response = $response->{'recommendation'};
			$response = $response->{'entry'};
			$controller = ControleDocumentos::getInstance();
		
			$obra_recomendada = array();
		
			$size = sizeof($response);
			$divisor=0;
			for ($i=1;$i<=$size;$i++) {
				$divisor=$divisor+$i;
			}
			$i=0;
			foreach ($response as $item) {
				$obra = $controller->get($item->{'value'})[0];
				$obra_recomendada[$i] = array();
				$obra_recomendada[$i]['id'] = $item->{'value'};
				$obra_recomendada[$i]['titulo'] = $obra['titulo'];
				$obra_recomendada[$i]['escorePerfil']=($size-$i)/$divisor;
				$obra_recomendada[$i]['nome']=$controller->get_tipos($obra['TipoDocumento_id']);
				$autores = $controller->get_autores($item->{'value'});
				$primeiro=1;
				foreach ($autores as $autor) {
					if ($primeiro=1) {
						$nomes=$autor['nome_completo'];
					} else {
						$primeiro=0;
						$nomes=", ".$autor['nome_completo'];
					}
				}
				$obra_recomendada[$i]['nome_completo'] = $nomes;
				$i++;
			}
		} else {
			echo "ERROR: ". $response;
			die();
		}
		return $obra_recomendada;
	}
	
	public function registra_acesso($id_usuario, $id_item) {
		global $config;
		$url = $config['recommendation_URL']."access/".$config['recommendation_apikey']."/repositorio1/".time();
		
		$ontology = '<?xml version="1.0"?><!DOCTYPE rdf:RDF [<!ENTITY owl "http://www.w3.org/2002/07/owl#" ><!ENTITY xsd "http://www.w3.org/2001/XMLSchema#" ><!ENTITY rdfs "http://www.w3.org/2000/01/rdf-schema#" ><!ENTITY rdf "http://www.w3.org/1999/02/22-rdf-syntax-ns#" ><!ENTITY RecOnt "http://biblio.inf.ufsc.br/~anderson/RecOnt.owl#" ><!ENTITY Literature "http://biblio.inf.ufsc.br/~anderson/Literature.owl#" >]><rdf:RDF xmlns="http://www.literaturabrasileira.ufsc.br/access#" xml:base="http://www.literaturabrasileira.ufsc.br/access" xmlns:Literature="http://biblio.inf.ufsc.br/~anderson/Literature.owl#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:owl="http://www.w3.org/2002/07/owl#" xmlns:xsd="http://www.w3.org/2001/XMLSchema#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns:RecOnt="http://biblio.inf.ufsc.br/~anderson/RecOnt.owl#"><owl:Ontology rdf:about="http://www.literaturabrasileira.ufsc.br/access"><owl:imports rdf:resource="http://biblio.inf.ufsc.br/~anderson/LiteratureContext.owl"/></owl:Ontology>';
	
	$controller = ControleDocumentos::getInstance();
	$autores = $controller->get_autores($id_item);
	foreach ($autores as $autor) {
		$ontology = $ontology.'<owl:NamedIndividual rdf:about="http://www.literaturabrasileira.ufsc.br/access#'.'A'.$autor['id'].'"><rdf:type rdf:resource="&Literature;Author"/></owl:NamedIndividual>';
	}
	
	$generos = $controller->get_generos_new($id_item);
	foreach ($generos as $genero) {
		$ontology = $ontology.'<owl:NamedIndividual rdf:about="http://www.literaturabrasileira.ufsc.br/access#'.'G'.$genero['id'].'"><rdf:type rdf:resource="&Literature;Genre"/></owl:NamedIndividual>';
	}
	
	$ontology = $ontology . '<owl:NamedIndividual rdf:about="http://www.literaturabrasileira.ufsc.br/access#'. 'O' . $id_item. '"><rdf:type rdf:resource="&Literature;Literary_work"/>';
	
	foreach ($autores as $autor) 
		$ontology = $ontology . '<Literature:hasAuthor rdf:resource="http://www.literaturabrasileira.ufsc.br/access#'.'A'.$autor['id'].'"/>';
	foreach ($generos as $genero) 
		$ontology = $ontology . '<Literature:hasGenre rdf:resource="http://www.literaturabrasileira.ufsc.br/access#'.'G'.$genero['id'].'"/>';
		
	$ontology = $ontology .  '</owl:NamedIndividual><owl:NamedIndividual rdf:about="http://www.literaturabrasileira.ufsc.br/access#'. 'U'. $id_usuario .'"><rdf:type rdf:resource="&RecOnt;Audience"/><RecOnt:acessed rdf:resource="http://www.literaturabrasileira.ufsc.br/access#O1"/></owl:NamedIndividual></rdf:RDF>';
	

		
	
	$ch = curl_init();
	
	// 			"POST ".$page." HTTP/1.0",
	
	$headers = array(
			"Content-type: application/xml;charset=\"utf-8\"",
			"Accept: */*",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Content-length: ".strlen($ontology),
	);
	
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//	curl_setopt($ch, CURLOPT_USERAGENT, $defined_vars['HTTP_USER_AGENT']);
	
	// Apply the XML to our curl call
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ontology);
	
	$data = curl_exec($ch);
	
	if (curl_errno($ch)) {
		print "Error: " . curl_error($ch);
	} else {
		// Show me the result
		var_dump($data);
		curl_close($ch);
	}
	/*
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
	curl_setopt($ch, CURLOPT_POSTFIELDS,$ontology);   // post data
//	curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml;charset=utf-8"));
//	curl_setopt($ch,  CURLOPT_HTTPHEADER,array ("Accept: application/json"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	*/
	}
}