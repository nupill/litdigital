<?php
require_once(dirname(__FILE__) . '/../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleComentarios.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
	default:
		require_once(APPLICATION_PATH . '/include/TemplateHandler.php');

		$template = new TemplateHandler();
		$template->set_css_files(array(
									'autores.css',
		    						'jquery.dataTables.css',
		    					));
		$template->set_js_files(array(
		    						'jquery.dataTables.min.js',
		    						'jquery.form.js',
		                            'jquery.loadTable.js',
		    					));
		$template->set_content_file('editoras/editoras.php');
		$template->set_authenticated_only(false);
		$template->show();
}