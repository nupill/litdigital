<?php
require_once(dirname(__FILE__) . '/../../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleComentarios.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleComentarios::getInstance();
        exit($controller->del_denuncia($ids));
        break;
        
    case 'getTableData':
        $controller = ControleComentarios::getInstance();
        $_GET['sTable'] = 'ComentarioDenuncia';
        $_GET['iType'] = 3;
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
        $template->set_content_file('admin/comentarios/denuncias/denuncias.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_COMENTARIOS_ID);
        $template->show();
}