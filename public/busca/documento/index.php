<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
//require_once(APPLICATION_PATH . "/controllers/ControleBusca.php");
require_once(APPLICATION_PATH . "/controllers/ControleBuscaDocumento.php");
require_once(APPLICATION_PATH . "/controllers/ControleDocumentos.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'getTableData':
        $controller = ControleBuscaDocumento::getInstance();
        $_GET['sTable'] = 'DocumentoView';
        exit($controller->getTableData($_GET));
        break;
        
    case 'getForm':
        include('documento.php');
        exit();
        break;
        
    case 'getCategorias':
    	$controller = ControleDocumentos::getInstance();
    	$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
    	exit(json_encode($controller->get_categorias($tipo)));
    	break;
    	
    case 'getGeneros':
        $controller = ControleDocumentos::getInstance();
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
        exit(json_encode($controller->get_generos($tipo)));
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
        
        if (isset($_REQUEST['titulo'])) {
            $template->set_content_file('busca/documento/resultados.php');
        }
        else {
            header('Location: ../');
            exit();
        }
        $template->set_authenticated_only(false);
        $template->set_active_nav_item(BUSCA_ID);
        $template->show(); 
}

