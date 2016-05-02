<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');

$template = new TemplateHandler();
$template->set_css_files(array(                                
        				 ));
$template->set_js_files(array(
        				));

$template->set_content_file('contas/confirmar/confirmar.php');
$template->set_authenticated_only(false);
$template->show();