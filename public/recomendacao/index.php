<?php
require_once(dirname(__FILE__) . '/../../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
require_once (dirname ( __FILE__ ) . '/../../application/controllers/ControleDocumentos.php');
require_once (dirname ( __FILE__ ) . '/../../application/controllers/ControleRecomendacao.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if (Auth::check ()) {
	$idUsuario = isset ( $_SESSION ['id'] ) ? $_SESSION ['id'] : '';
}

switch ($action) {
		
		case 'getRecomendacoes':
			global $config;
			if ($config['recommendation_type']==1) {
				$controller = ControleDocumentos::getInstance();
				$recomendacoes = $controller->get_obras_recomendadas($idUsuario, 0, 10);
			} else {
				$controller = ControleRecomendacao::getInstance();
				$recomendacoes = $controller->get_obras_recomendadas($idUsuario, 0, 10);
			}
			
			include('parse.php');
			break;
	
		default:
			$template = new TemplateHandler();
			$template->set_css_files(array(
					'jquery.dataTables.css'
			));
			$template->set_js_files(array(
					'jquery.dataTables.min.js',
					'jquery.loadTable.js',
					'jquery.history.js'
			));
			$template->set_content_file('recomendacao/recomendacao.php');
			$template->set_authenticated_only(true);
			$template->set_active_nav_item(RECOMENDACAO_ID);
			$template->show();
				
	
}
