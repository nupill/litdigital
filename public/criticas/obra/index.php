<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleCriticasObra.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'download':
        
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        
        $controller = ControleCriticasObra::getInstance();
       	$critica_obra = $controller->get($id);

		if ($critica_obra) {
		    $critica_obra = $critica_obra[0];
		}
       	
        //codigo para incrementar a visita da midia
        //$controller->visita_midia($id);
        
        header('Location: ' . CRITICAS_URI . $critica_obra['nome_arquivo'] . '.' . $critica_obra['mime']);
        
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
		    $template->set_content_file('criticas/obra/midias.php');
		}
		else {
		   $template->set_content_file('criticas/obra/criticas.php');
		}
		$template->set_authenticated_only(false);
		$template->show();
}