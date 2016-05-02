<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
$template = new TemplateHandler();
$template->set_css_files(array(
                        'admin.css',
						'jquery.dataTables.css'
						));
$template->set_js_files(array(
						'jquery.dataTables.min.js',
						'jquery.form.js',
                        'jquery.loadTable.js'
						));
$template->set_content_file('admin/criticas/criticas.php');
$template->set_authenticated_only(true);
$template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
$template->set_header_file('header_admin.php');
$template->set_body_file('body_admin.php');
$template->set_navigation_file('navigation_admin.php');
$template->set_active_nav_item(ADMIN_CRITICAS_ID);
$template->show();
