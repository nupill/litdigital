<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleGenero.php");
require_once(APPLICATION_PATH . "/controllers/ControleDocumentos.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
        $tipoDoc = isset($_POST['tipoDoc']) ? $_POST['tipoDoc'] : '';
        $controller = ControleGenero::getInstance();
        if ($action == 'add') {
            exit($controller->add($nome,$tipoDoc));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update($id, $nome,$tipoDoc));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleGenero::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleGenero::getInstance();
        $_GET['sTable'] = 'Genero';
        exit($controller->getTableData($_GET));
        break;
        
    case 'search_genero': //Called from forms at Documentos
        $controllerDoc = ControleDocumentos::getInstance();
        $controller = ControleGenero::getInstance();
        $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $currentIdType = $controllerDoc->get_id_tipo_obra_atual($type);
        $results = $controller->get(null, array('id', 'nome'), 0, 15, $term, $currentIdType);
        $response = array();        
        foreach ($results as $key=>$result) {
           $response[$key]['id'] = $result['id'];
           $response[$key]['label'] = $result['nome'];
           $response[$key]['value'] = $result['nome'];
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
        $template->set_content_file('admin/generos/generos.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_FONTES_ID);
        $template->show();
}
