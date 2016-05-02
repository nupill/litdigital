<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleEstados.php");
require_once(APPLICATION_PATH . "/controllers/ControleLocalizacao.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'add':
    case 'update':
        $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
        $sigla = isset($_POST['sigla']) ? $_POST['sigla'] : $estado;
        $pais = isset($_POST['pais']) ? $_POST['pais'] : '';
        $lat = isset($_POST['latitude']) ? $_POST['latitude'] : '';
        $lng = isset($_POST['longitude']) ? $_POST['longitude'] : '';
        
        $controller = ControleEstados::getInstance();
        if ($action == 'add') {
            exit($controller->add($estado, $sigla, $pais, $lat, $lng));
        }
        else {
        	$idAnt = isset($_GET['id']) ? $_GET['id'] : '';
            exit($controller->update($idAnt,$estado, $sigla, $pais, $lat, $lng));
        }
        break;
 
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleEstados::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleEstados::getInstance();
        $_GET['sTable'] = 'estados';
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
        $template->set_content_file('admin/estados/estados.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_ESTADOS_ID);
        $template->show();
}
