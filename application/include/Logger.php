<?php
require_once(dirname(__FILE__) . "/../config/general.php");
require_once(dirname(__FILE__) . "/mysqli.php");
require_once(dirname(__FILE__) . "/DB.php");
require_once(dirname(__FILE__) . "/FirePHPCore/fb.php");

class Logger {
	private static $DB;
	
    public static function log($message, $location = '') {
        global $config;
        
        if ($config['firephp_log_enabled']) {
            FB::log($message);
        }
        
        if (!self::$DB) {
            self::$DB = DB::getInstance();
        }
        
        $user_host = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : ''; 
        
        $query = sprintf("INSERT INTO logs_erro
        				  (data, descricao, local, ip_usuario, host_usuario)
        				  VALUES (NOW(), '%s', '%s', INET_ATON('%s'), '%s')",
                          mysqlx_real_escape_string($message),
                          mysqlx_real_escape_string($location),
                          $_SERVER['REMOTE_ADDR'],
                          $user_host);
        try {
            self::$DB->query($query);
        }
        catch (Exception $e) {
            
            $message = "Descrição: $message\n";
            $message.= "Local: $location\n";
            $message.= "IP do usuário: {$_SERVER['REMOTE_ADDR']}\n";
            $message.= "Host do usuário: {$user_host}\n";
            $message.= "Erro ao atualizar a tabela de logs: {$e->getMessage()}\n";
            
            @mail($config['log_email'], 'Erro de log', $message);
        }
    }
}
?>
