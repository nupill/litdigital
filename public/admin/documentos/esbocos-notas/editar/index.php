<?php
require_once(dirname(__FILE__) . '/../../../../../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
$template = new TemplateHandler();
$template->set_css_files(array(
                            'admin.css',
                            'smoothness/jquery-ui.custom.css',
                            'fileuploader.css'
                        ));
$template->set_js_files(array(
                            'admin_common.js',
                            'documento.js',
                            'jquery-ui.custom.min.js',
                            'jquery.selectboxes.min.js',
                            'fileuploader.js'
                        ));
$template->set_content_file('admin/documentos/esbocos-notas/editar/editar.php');
$template->set_authenticated_only(true);
$template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
$template->set_header_file('header_admin.php');
$template->set_body_file('body_admin.php');
$template->set_navigation_file('navigation_admin.php');
$template->set_active_nav_item(ADMIN_DOCUMENTOS_ID);
$template->show();