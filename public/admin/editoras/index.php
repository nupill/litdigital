<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleEditoras.php");
require_once(APPLICATION_PATH . "/controllers/ControleLocalizacao.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        $locais = isset($_POST['locais']) ? $_POST['locais'] : '';
        $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
        $periodico = isset($_POST['periodico']) ? true : false;
        
        
        $controller = ControleEditoras::getInstance();
        if ($action == 'add') {
            exit($controller->add($nome, $locais, $descricao, $periodico));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update($id, $nome, $locais, $descricao, $periodico));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleEditoras::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleEditoras::getInstance();
        $_GET['sTable'] = 'Editora';
        exit($controller->getTableData($_GET));
        break;
        
    case 'search_editora': //Called from forms at Documentos
        	$controller = ControleEditoras::getInstance();
        	$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
        	$results = $controller->get(null, array('id', 'nome'), 0, 15, $term);
        	$response = array();
        	foreach ($results as $key=>$result) {
        		$local = $controller->getLocal($result['id']);
        		$response[$key]['id'] = $result['id'];
        		$response[$key]['label'] = $result['nome'].", ".$local;
        		$response[$key]['value'] = $result['nome'].", ".$local;
        	}
        	exit(json_encode($response));
        	break;
        
     case 'getEstados':
        $paisid = isset($_GET['paisid']) ? $_GET['paisid'] : '';
        $controller = ControleLocalizacao::getInstance();
        exit(json_encode($controller->getEstadosPais($paisid)));
        break;
     case 'getCidades':
     	$estadoid = isset($_REQUEST['estadoid']) ? $_REQUEST['estadoid'] : '';
     	$paisid = isset($_GET['paisid']) ? $_GET['paisid'] : '';
     	if ($paisid!=1)
     		$estadoid=NULL;
     	$controller = ControleLocalizacao::getInstance();
     	exit(json_encode($controller->getCidades($paisid,$estadoid)));
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
        $template->set_content_file('admin/editoras/editoras.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_EDITORAS_ID);
        $template->show();
}