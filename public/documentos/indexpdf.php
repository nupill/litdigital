<?php
require_once (dirname ( __FILE__ ) . '/../../application/config/general.php');
require_once (APPLICATION_PATH . '/controllers/ControleDocumentos.php');
require_once (APPLICATION_PATH . '/controllers/ControleUsuarios.php');
require_once (APPLICATION_PATH . "/controllers/ControleComentarios.php");
require_once (dirname ( __FILE__ ) . '/../../application/controllers/ControleAutores.php');

$action = isset ( $_GET ['action'] ) ? $_GET ['action'] : '';

switch ($action) {
	case 'add_comment' :
		$id = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : '';
		$conteudo = isset ( $_POST ['comment'] ) ? $_POST ['comment'] : '';
		$titulo = isset ( $_POST ['title'] ) ? $_POST ['title'] : '';
		
		$controller = ControleComentarios::getInstance ();
		exit ( $controller->add_documento ( $id, $conteudo, $titulo ) );
		break;
	
	case 'reply_comment' :
		$id = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : '';
		$conteudo = isset ( $_POST ['reply'] ) ? $_POST ['reply'] : '';
		$id_comentario = isset ( $_REQUEST ['parent_id'] ) ? $_REQUEST ['parent_id'] : '';
		
		$controller = ControleComentarios::getInstance ();
		exit ( $controller->reply_documento ( $id, $conteudo, $id_comentario ) );
		break;
	
	case 'vote_up_comment' :
		$id = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : '';
		
		$controller = ControleComentarios::getInstance ();
		exit ( $controller->vote_up ( $id ) );
		break;
	
	case 'vote_down_comment' :
		$id = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : '';
		
		$controller = ControleComentarios::getInstance ();
		exit ( $controller->vote_down ( $id ) );
		break;
	
	case 'flag_comment' :
		$id = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : '';
		$motivo = isset ( $_POST ['reason'] ) ? $_POST ['reason'] : '';
		
		$controller = ControleComentarios::getInstance ();
		exit ( $controller->flag ( $id, $motivo ) );
		break;
	
	case 'download' :
		
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : '';
		
		$controller = ControleDocumentos::getInstance ();
		$midia = $controller->get_midias ( null, array ('Documento_id', 'nome_arquivo', 'titulo' ), $id );
		if ($midia) {
			$midia = $midia [0];
		} else {
			exit ( 'Mídia não encontrada' );
		}
		
		$nome_arquivo = $midia ['nome_arquivo'];
		$extensao = pathinfo ( $nome_arquivo, PATHINFO_EXTENSION );
		
		// codigo para incrementar a visita da midia
		$controller->visita_midia ( $id );
		
		// codigo para adaptabilidade
		if (Auth::check ()) {
			$id_usuario = isset ( $_SESSION ['id'] ) ? $_SESSION ['id'] : '';
			$id_obra = $midia ['Documento_id'];
			$documento = $controller->get ( $id_obra, array ('Genero_id' ) );
			$documento = current ( $documento );
			$id_genero = $documento ['Genero_id'];
			$id_autores = $controller->get_autores ( $id_obra, array ('id' ) );
			$controller_usuario = ControleUsuarios::getInstance ();
			$controller_usuario->atualiza_adaptabilidade ( $id_usuario, $id_autores, $id_genero,$id_obra );
		}
		
		/*
		 * Codigo da anotacao
		 */
		if ($extensao == 'html' || $extensao == 'htm') {
			if (Auth::check ()) {
				$id_usuario = isset ( $_SESSION ['id'] ) ? $_SESSION ['id'] : '';
				$controller_usuario = ControleUsuarios::getInstance ();
				$usuario_logado = $controller_usuario->get ( $id_usuario );
				$usuario_logado = $usuario_logado [0];
				
				// Se usuário habilitou a ferramenta de anotação
				if ($usuario_logado ['anotacao']) {
					
					// codigo para incrementar a visita da midia
					$controller->visita_midia ( $id );
					
					// codigo para adaptabilidade
					if (Auth::check ()) {
						$id_usuario = isset ( $_SESSION ['id'] ) ? $_SESSION ['id'] : '';
						$id_obra = $midia ['Documento_id'];
						$documento = $controller->get ( $id_obra, array ('Genero_id', 'titulo' ) );
						$documento = current ( $documento );
						$id_genero = $documento ['Genero_id'];
						$id_autores = $controller->get_autores ( $id_obra, array ('id' ) );
						$controller_usuario = ControleUsuarios::getInstance ();
						$controller_usuario->atualiza_adaptabilidade ( $id_usuario, $id_autores, $id_genero,$id_obra );
					}
					
					$controller_autores = ControleAutores::getInstance ();
					
					$autor = $controller_autores->get ( $id_autores [0] ['id'] );
					
					$url_documento = DOCUMENTS_URI . $midia ['nome_arquivo'];
					
					// O titulo da midia pode ser nulo
					$titulo_documento = $midia ['titulo'];
					if ($titulo_documento == null) {
						$titulo_documento = $documento ['titulo'];
					}
					
					$autor_documento = $autor [0] ['nome_completo'];
					
					$_SESSION ['url_documento'] = $url_documento;
					$_SESSION ['titulo_documento'] = $titulo_documento;
					$_SESSION ['autor_documento'] = $autor_documento;
					
					// Invocando o consumer
					global $public_config;
					header ( 'Location: ' . $public_config ['lti_consumer'] );
					exit ( '' );
				}
			}
			
// 			// Formatação da mídia
// 			exit ( file_get_contents ( DOCUMENTS_FORMAT_URI . $midia ['nome_arquivo'] ) );
			
			return ( file_get_contents ( DOCUMENTS_URI . $midia ['nome_arquivo'] ) );
		} 

		else {
			$finfo = finfo_open ( FILEINFO_MIME_TYPE ); // return mime type ala
			                                         // mimetype extension
			$mime_type = finfo_file ( $finfo, DOCUMENTS_PATH . $midia ['nome_arquivo'] );
			finfo_close ( $finfo );
			
			header ( 'Content-Type: ' . $mime_type );
			header ( 'Content-Disposition: attachment; filename="' . $midia ['nome_arquivo'] . '"' );
			header ( 'Content-Length: ' . filesize ( DOCUMENTS_PATH . $midia ['nome_arquivo'] ) );
		
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $midia ['nome_arquivo'] . '"');
			exit ( file_get_contents ( DOCUMENTS_URI . $midia ['nome_arquivo'] ) );
		}
		
		// header('Location: ' . DOCUMENTS_FORMAT_URI . $midia['nome_arquivo']);
		
		// // dá preferência para as novas versões das mídias .html
		// if (file_exists ( DOCUMENTS_PATH . '_formatted/' . $midia
		// ['nome_arquivo'] )) {
		// header ( 'Location: ' . DOCUMENTS_URI . '_formatted/' . $midia
		// ['nome_arquivo'] );
		// } else {
		// header ( 'Location: ' . DOCUMENTS_URI . $midia ['nome_arquivo'] );
		// }
		
		break;
	
	default :
		
		require_once (APPLICATION_PATH . '/include/TemplateHandler.php');
		$template = new TemplateHandler ();
		$template->set_css_files ( array ('documentos.css', 'jquery.dataTables.css', 'comments.css' ) );
		$template->set_js_files ( array ('jquery.dataTables.min.js', 'jquery.form.js', 'jquery.loadTable.js', 'comments.js','facebook.js' ) );
		if ($action == 'midias') {
			$template->set_content_file ( 'documentos/midias.php' );
		} else {
			$template->set_content_file ( 'documentos/documentos.php' );
		}
		$template->set_authenticated_only ( false );
		$template->show ();
}
