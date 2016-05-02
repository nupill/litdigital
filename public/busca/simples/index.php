<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . '/controllers/ControleBuscaSimples.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'getTableData':
    	$controller = ControleBuscaSimples::getInstance();
    	$_GET['sTable'] = 'Documento';
        exit($controller->getTableData($_GET));
        break;
        
    case 'getForm':
        include('simples.php');
        exit();
        break;
        
    default:
        
        require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
        $template = new TemplateHandler();
        $template->set_css_files(array(
                                'busca.css',
        						'jquery.dataTables.css'
        						));
        $template->set_js_files(array(
        						'jquery.dataTables.min.js',
        						'jquery.loadTable.js'
        						));
        
        if (isset($_REQUEST['termo'])) {
            $template->set_content_file('busca/simples/resultados.php');
        }
        else {
            header('Location: ../');
            exit();
        }
        $template->set_authenticated_only(false);
        $template->set_active_nav_item(BUSCA_ID);
        $template->show(); 
}

