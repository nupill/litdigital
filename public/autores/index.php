<?php
require_once(dirname(__FILE__) . '/../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleComentarios.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
	case 'add_comment':
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
		$conteudo = isset($_POST['comment']) ? $_POST['comment'] : '';
		$titulo = isset($_POST['title']) ? $_POST['title'] : '';
		
		$controller = ControleComentarios::getInstance();
		exit($controller->add_autor($id, $conteudo, $titulo));
		break;
		
	case 'reply_comment':
			$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
			$conteudo = isset($_POST['reply']) ? $_POST['reply'] : '';
			$id_comentario = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : '';
		
			$controller = ControleComentarios::getInstance();
			exit($controller->reply_autor($id, $conteudo, $id_comentario));
			break;
			
	case 'vote_up_comment':
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	
		$controller = ControleComentarios::getInstance();
		exit($controller->vote_up($id));
		break;
		
	case 'vote_down_comment':
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	
		$controller = ControleComentarios::getInstance();
		exit($controller->vote_down($id));
		break;
		
	case 'flag_comment':
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
		$motivo = isset($_POST['reason']) ? $_POST['reason'] : '';
		
		$controller = ControleComentarios::getInstance();
		exit($controller->flag($id, $motivo));
		break;
		
	default:
		require_once(APPLICATION_PATH . '/include/TemplateHandler.php');

		$template = new TemplateHandler();
		$template->set_css_files(array(
									'autores.css',
		    						'jquery.dataTables.css',
									'comments.css'
		    					));
		$template->set_js_files(array(
		    						'jquery.dataTables.min.js',
		    						'jquery.form.js',
		                            'jquery.loadTable.js',
									'comments.js',
									'd3.min.js',
									'd3pie.min.js',
									'graficoGenero.js'
		    					));
		$template->set_content_file('autores/autores.php');
		$template->set_authenticated_only(false);
		$template->show();
}