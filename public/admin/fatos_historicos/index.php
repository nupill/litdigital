<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleFatosHistoricos.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        
        $ano_inicio = isset($_POST['ano_inicio']) ? $_POST['ano_inicio'] : '';
        $ano_fim = isset($_POST['ano_fim']) ? $_POST['ano_fim'] : '';
        $fato = isset($_POST['fato']) ? $_POST['fato'] : '';
        
        $controller = ControleFatosHistoricos::getInstance();
        if ($action == 'add') {
            exit($controller->add($ano_inicio, $ano_fim, $fato));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update($id, $ano_inicio, $ano_fim, $fato));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleFatosHistoricos::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleFatosHistoricos::getInstance();
        $_GET['sTable'] = 'FatoHistorico';
        exit($controller->getTableData($_GET));
        break;
        
    case 'search_fato': //Called from forms at Documentos
        	$controller = ControleFatosHistoricos::getInstance();
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
        $template->set_content_file('admin/fatos_historicos/fatos_historicos.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_FATOS_HISTORICOS_ID);
        $template->show();
}