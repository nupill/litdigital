<?php

if (!file_exists('../application/config/general.php')) {
	header('Location: install/');
	die;
}


require_once(dirname(__FILE__) . "/../application/config/general.php");
global $config;

if (!$config['local_test']) {

	if (substr($_SERVER['HTTP_HOST'],0,3) != 'www') {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: http://www.'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}
}

setcookie("id","",time()-3600);
require_once(dirname(__FILE__) . '/../application/config/general.php');
require_once(APPLICATION_PATH . '/include/TemplateHandler.php');

$template = new TemplateHandler();
$template->set_css_files(array(
                        'home.php',
						));
$template->set_js_files(array(
						));
$template->set_content_file('home.php');
$template->set_body_file('body_home.php');
$template->set_authenticated_only(false);
$template->set_active_nav_item(HOME_ID);
$template->show();
