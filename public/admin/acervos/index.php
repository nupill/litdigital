<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleAcervos.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
        
        $controller = ControleAcervos::getInstance();
        if ($action == 'add') {
            exit($controller->add($descricao));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update($id, $descricao));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleAcervos::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleAcervos::getInstance();
        $_GET['sTable'] = 'Acervo';
        exit($controller->getTableData($_GET));
        break;
        
    case 'search_acervo': //Called from forms at Documentos
        	$controller = ControleAcervos::getInstance();
        	$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
        	$results = $controller->get(null, array('id', 'descricao'), 0, 15, $term);
        	$response = array();
        	foreach ($results as $key=>$result) {
        		$response[$key]['id'] = $result['id'];
        		$response[$key]['label'] = $result['descricao'];
        		$response[$key]['value'] = $result['descricao'];
        	}
        	exit(json_encode($response));
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
        $template->set_content_file('admin/acervos/acervos.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_EDITORAS_ID);
        $template->show();
}