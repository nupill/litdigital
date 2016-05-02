<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleBusca.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'getTableData':
        $controller = ControleBusca::getInstance();
        $_GET['sTable'] = 'DocumentoView';
        exit($controller->getTableData($_GET));
        break;
        
    case 'getForm':
        
        include('conteudo.php');
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
        						'jquery.form.js',
        						'jquery.md5.js',
        						'jquery.dataTables.min.js'
        						));
        
        if (isset($_REQUEST['termo'])) {
            $template->set_content_file('busca/conteudo/resultados.php');
        }
        else {
            header('Location: ../');
            exit();
        }
        $template->set_authenticated_only(false);
        $template->set_active_nav_item(BUSCA_ID);
        $template->show(); 
}

