<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleCriticasAutor.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'download':
        
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        
        $controller = ControleCriticasAutor::getInstance();
       	$critica_autor = $controller->get($id);

		if ($critica_autor) {
		    $critica_autor = $critica_autor[0];
		}
       	
        //codigo para incrementar a visita da midia
        //$controller->visita_midia($id);
        
        header('Location: ' . CRITICAS_URI . $critica_autor['nome_arquivo'] . '.' . $critica_autor['mime']);
        
        break;

		default:
		require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
		$template = new TemplateHandler();
		$template->set_css_files(array(
		    						'jquery.dataTables.css',
		    						'documentos.css'
		    					));
		$template->set_js_files(array(
		    						'jquery.dataTables.min.js',
		    						'jquery.form.js',
		                            'jquery.loadTable.js'
		    					));
		
		if ($action == 'midias') {
		    $template->set_content_file('criticas/autor/midias.php');
		}
		else {
		   $template->set_content_file('criticas/autor/criticas.php');
		}
		$template->set_authenticated_only(false);
		$template->show();
}