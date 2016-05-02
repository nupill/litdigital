<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleIdiomas.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
        $iso = isset($_POST['iso']) ? $_POST['iso'] : '';
        $controller = ControleIdiomas::getInstance();
        if ($action == 'add') {
            exit($controller->add($descricao,$iso));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update($id, $descricao,$iso));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleIdiomas::getInstance();
        exit($controller->del($ids));
        break;
    case 'getTableData':
        $controller = ControleIdiomas::getInstance();
        $_GET['sTable'] = 'Idioma';
        exit($controller->getTableData($_GET));
        break;
    default:
        require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
        $template = new TemplateHandler();
        $template->set_css_files(array(
                                'admin.css',
        						'jquery.dataTables.css'
        						));
        $template->set_js_files(array(
        						'jquery.dataTables.min.js',
                                'jquery.loadTable.js'
        						));
        $template->set_content_file('admin/idiomas/idiomas.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_FONTES_ID);
        $template->show();
}
