<?php
global $config;
if (isset($config['ga_profile']) && isset($config['ga_email']) && isset($config['ga_password']) && isset($config['ga_tracker'])) {
	header('Location: estatisticas/');
} else {
	header('Location: usuarios/index.php');
}