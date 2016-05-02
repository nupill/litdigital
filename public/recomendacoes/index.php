<?php
require_once(dirname(__FILE__) . '/../../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');

$template = new TemplateHandler();
$template->set_css_files(array(                                
                              'jquery.dataTables.css',
                              'contas.css'
                         ));
$template->set_js_files(array(
                              'jquery.dataTables.min.js',
                              'jquery.loadTable.js'
                        ));

$template->set_content_file('recomendacoes/recomendacoes.php');
$template->set_authenticated_only(true);
$template->show();