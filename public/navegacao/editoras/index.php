<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . '/controllers/ControleBuscaEditorasNavegacao.php');
require_once(APPLICATION_PATH . "/controllers/ControleEditoras.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'getTableData':
        $controller = ControleBuscaEditorasNavegacao::getInstance();
        $_GET['sTable'] = 'Editora';
        exit($controller->getTableData($_GET));
        break;
        
    case 'getForm':
        include('editoras.php');
        exit();
        break;
        
    default:
   
        require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
        $template = new TemplateHandler();
        $template->set_css_files(array(
                                'busca.css',
        						'jquery.dataTables.css',
        						'navegacao.css'
        						));
        $template->set_js_files(array(
        						'jquery.dataTables.min.js',
                                'jquery.loadTable.js'
        						));
       
        if (isset($_REQUEST['letra'])) {
            $template->set_content_file('navegacao/editoras/resultados.php');
        }
        else {
            header('Location: ../');
            exit();
        }
        $template->set_authenticated_only(false);
        $template->set_active_nav_item(NAVEGACAO_ID);
        $template->show(); 
}

