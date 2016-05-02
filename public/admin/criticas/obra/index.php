<?php
require_once(dirname(__FILE__) . '/../../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleCriticasObra.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
        $autor_critica = isset($_POST['autor_critica']) ? $_POST['autor_critica'] : '';
        $obra_id = isset($_POST['obra_id']) ? $_POST['obra_id'] : '' ;
     	$obra = isset($_POST['obra']) ? $_POST['obra'] : '' ;
        $arquivo = isset( $_POST['arquivos'][0]) ? $_POST['arquivos'][0] : '' ;
        if ($arquivo == null) {
        	$nome_arquivo = null;
        	$mime = null;
        	$tamanho_arquivo = null;
        }
        else{
        	$arquivo = explode('.', $arquivo);
	        $nome_arquivo = $arquivo[0];
	        $mime = $arquivo[1];
	        $tamanho_arquivo = filesize(CRITICAS_PATH.$nome_arquivo.'.'.$mime);
        }
       
       	
        $controller = ControleCriticasObra::getInstance();
        if ($action == 'add') {
            exit($controller->add($titulo, $autor_critica, $obra_id, $obra, $nome_arquivo, $mime, $tamanho_arquivo));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update($id, $titulo, $autor_critica, $nome_arquivo, $mime, $tamanho_arquivo));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleCriticasObra::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleCriticasObra::getInstance();
        $_GET['sTable'] = 'CriticaObraLiteraria';
        exit($controller->getTableData($_GET));
        break;
    
    case 'upload':
    	$xhr = false;
    	$file = null;
	    if (isset($_GET['qqfile'])){
	        $xhr = true;
	    } elseif (isset($_FILES['qqfile'])){
	        $file = $_FILES['qqfile'];
	    }
        
        $controller = ControleCriticasObra::getInstance();
        exit($controller->upload($xhr, $file));
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
        $template->set_content_file('admin/criticas/obra/obra.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_CRITICAS_ID);
        $template->show();
}