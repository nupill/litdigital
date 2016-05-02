<?php
require_once(dirname(__FILE__) . '/../../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');

$template = new TemplateHandler();
$template->set_css_files(array(
							'busca.css',
        					'jquery.dataTables.css',
        					'navegacao.css'
                        ));
$template->set_js_files(array(
							'jquery.dataTables.min.js',
        					'jquery.loadTable.js',
        					'jquery.history.js'
                        ));
$template->set_content_file('navegacao/navegacao.php');
$template->set_authenticated_only(false);
$template->set_active_nav_item(NAVEGACAO_ID);
$template->show();
