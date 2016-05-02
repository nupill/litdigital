<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControlePaises.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
		$lat = isset($_POST['latitude']) ? $_POST['latitude'] : '';
        $lng = isset($_POST['longitude']) ? $_POST['longitude'] : '';  
        
        $controller = ControlePaises::getInstance();
        if ($action == 'add') {
            exit($controller->add($nome, $lat, $lng));
        }
        else {
        	$idAnt = isset($_GET['id']) ? $_GET['id'] : '';
            exit($controller->update($idAnt,$nome, $lat, $lng));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControlePaises::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControlePaises::getInstance();
        $_GET['sTable'] = 'Paises';
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
        $template->set_content_file('admin/paises/paises.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_FONTES_ID);
        $template->show();
}
