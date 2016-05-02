<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleUsuarios.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'update':
        $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $profissao = isset($_POST['profissao']) ? $_POST['profissao'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        $login = isset($_POST['login']) ? $_POST['login'] : '';
        $senha = isset($_POST['senha']) && $_POST['senha'] ? $_POST['senha'] : null;
        $papel = isset($_POST['papel']) ? $_POST['papel'] : '';
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $controller = ControleUsuarios::getInstance();
        exit($controller->update($id, $nome, $email, $login, $senha, $senha, $papel, $profissao, $url));
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleUsuarios::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleUsuarios::getInstance();
        $_GET['sTable'] = 'Usuario';
        exit($controller->getTableData($_GET));
        break;
        
    case 'email_available':
    	$email = isset($_POST['email']) ? $_POST['email'] : '';
        $controller = ControleUsuarios::getInstance();
        exit($controller->email_available($email));
        break;
   
    case 'login_available':
    	$login = isset($_POST['login']) ? $_POST['login'] : '';
        $controller = ControleUsuarios::getInstance();
        exit($controller->login_available($login));
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
        $template->set_content_file('admin/usuarios/usuarios.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_USUARIOS_ID);
        $template->show();
}
