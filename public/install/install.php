
<?php
session_start();

global $config;
global $public_config;

// Solicitar dados do banco: nome, usuario e senha

$language = @$_SESSION['language'];
$name = @$_SESSION ['dbname'];
$user = @$_SESSION ['dbuser'];
$password = @$_SESSION ['dbpassword'];

// Log E-mail
$log_email = @$_SESSION ['log_email'];


// Consumer Settings
// $ltiurl = @$_SESSION ['lti_consumer_url'];

// Send Emails
$smtp_email = @$_SESSION ['smtp_email'];
$smtp_host = @$_SESSION ['smtp_host'];
$smtp_user = @$_SESSION ['smtp_user'];
$smtp_password = @$_SESSION ['smtp_password'];
$flag_email = @$_SESSION ['flag_email'];


// Google Analytics
$ga_profile = @$_SESSION ['ga_profile'];
$ga_email = @$_SESSION ['ga_email'];
$ga_password = @$_SESSION ['ga_password'];
$ga_tracker = @$_SESSION ['ga_tracker'];

// Timezone Set
$timezone = @$_SESSION ['timezone'];

$db_path = dirname(__FILE__) . "libDB.sql";

/* MODELO */

// redirecionar para o public

// linhas de código do arquivo config.php:


// "require_once"s do arquivo
$txt_inicial1 = '<?php' . "\n" . 'require_once(dirname(__FILE__) . \'/constants.php\');' . "\n";
$txt_inicial2 = 'require_once(dirname(__FILE__) . \'/../include/Logger.php\');' . "\n";
$txt_inicial3 = 'require_once(dirname(__FILE__) . \'/../include/functions.php\');' . "\n";
$txt_inicial4 = 'require_once(dirname(__FILE__) . \'/../controllers/ControleInstall.php\');' . "\n" . "\n" .'global $config;' . "\n" .'global $con;' . "\n". "\n";

$txt_config_description = '//Database configuration settings' . "\n";
$txt_codigo_config1 = '$config [\'mysql_host\'] = ' . "'" . 'localhost' . "';" . "\n";
$txt_codigo_config2 = '$config [\'mysql_user\'] = ' . "'" . $user . "';" . "\n";
$txt_codigo_config3 = '$config [\'mysql_password\'] = ' . "'" . $password. "';" . "\n";
$txt_codigo_config4 = '$config [\'mysql_database\'] = ' . "'" . $name . "';" . "\n" . "\n";

$txt_config_log_description = '/*Log Settings' . '*/' . "\n";
$txt_config_log1 = '$config[\'log_verbosity\'] =' . 'LOG_VERBOSITY_DEBUG;' . "          ";
$txt_config_log2 = '$config[\'log_enabled\'] =' . 'true;' . "          ";
$txt_config_log3 = '$config[\'firephp_log_enabled\'] =' . 'true;' . "          ";
$txt_config_log4 = '$config[\'log_email\'] =' ."'".$log_email."';" . "          ";
$txt_config_log5 = '$config[\'local_test\'] =' . 'true;' . "          ";

// Comentários de descrição das Log Settings
$txt_config_log_d1 = ' //Log verbosity level' . "\n";
$txt_config_log_d2 = '//Log errors into DB table / E-mail' . "\n";
$txt_config_log_d3 = '//Log errors with FirePHP' . "\n";
$txt_config_log_d4 = '//E-mail address to send the error logs (when log DB insert fails)' . "\n";
$txt_config_log_d5 = '// Setado para realizar testes em máquina Windows. Altere para true no caso de instalação no windows.' . "\n" . "\n";

//$txt_lti_consumer_description = '//LTI consumer settings' . "\n";
//$txt_lti_consumer = '//$public_config[\'lti_consumer\'] = ' . "'" . $ltiurl . "';" . "\n" . "\n";

$txt_send_email_description = "/* Send email */" . "\n";
$txt_send_email1 = '$config[\'smtp_email\'] = ' . "'" . $smtp_email . "';" . "\n";
$txt_send_email2 = '$config[\'smtp_host\'] = ' . "'" . $smtp_host . "';" . "\n";
$txt_send_email3 = '$config[\'smtp_port\'] = ' . "'465';" . "\n";
$txt_send_email4 = '$config[\'smtp_user\'] = ' . "'" . $smtp_user . "';" . "\n";
$txt_send_email5 = '$config[\'smtp_password\'] = ' . "'" . $smtp_password . "';" . "\n";

$txt_flag_report_description = '/* COMMENTS ' . '*/' . "\n";
$txt_flag_report = '$config[\'flag_email\'] = ' . "'" . "something" . "';" . "\n";

/*
 * Google Analytics
 *
 * Get the Profile ID at Google Analytics resports URL (example: https://www.google.com/analytics/reporting/?id=33428003)
 * or by Data Feed Query Explorer: http://code.google.com/apis/analytics/docs/gdata/gdataExplorer.html
 */
if (isset($ga_profile)) {
	$txt_ga_description = '/*' . "\n" . '*Get the Profile ID at Google Analytics resports URL (example: https://www.google.com/analytics/reporting/?id=33428003)' . "\n" . '*or by Data Feed Query Explorer: http://code.google.com/apis/analytics/docs/gdata/gdataExplorer.html' . "\n" . '*/';

	$txt_google_analytics1 = '$config[\'ga_profile\'] = ' . "'ga:" . $ga_profile . "';" . "\n";
	$txt_google_analytics2 = '$config[\'ga_email\'] = ' . "'" . $ga_email . "';" . "\n";
	$txt_google_analytics3 = '$config[\'ga_password\'] = ' . "'" . $ga_password . "';" . "\n" . "\n";

	$txt_ga_comment1 = '//Other settings' . "\n";
	$txt_google_analytics4 = '$public_config[\'ga_tracker\'] = ' . "'" . $ga_tracker . "';" . "           ";

	$txt_ga_tracker_comment = '//Google Analytics Tracker ID' . "\n" . "\n";
}

$txt_exception_description = '/* Error/Exception Handling */' . "         "."\n";
$txt_exceptions1 = 'set_exception_handler(\'handle_exception\');' . "         ";
$txt_exceptions2 = 'set_error_handler(\'handle_error\');' . "         ";
$txt_exceptions3 = 'error_reporting(E_ALL);' . "         ";
$txt_exceptions4 = '//error_reporting(0);' . "         ";
$txt_exceptions5 = 'ini_set(\'display_errors\', \'1\');' . "         ";

$txt_exception_comments1 = '//Define the function to handle exceptions' . "\n";
$txt_exception_comments2 = '//Define the function to handle errors' . "\n";
$txt_exception_comments3 = '//Sets which PHP errors are reported' . "\n";
$txt_exception_comments4 = '//Sets which PHP errors are reported' . "\n";
$txt_exception_comments5 = '//Turn error displaying on or off' . "\n" . "\n";

$txt_timezone = 'date_default_timezone_set(\'America/Sao_Paulo\');' . "\n" . "\n";


$txt_final = '?>';

// caminho do arquivo config.php:
$arquivo = '\general.php'; 
$diretorio = dirname('..\..\application\config\config');
$final_path = $diretorio  . $arquivo;
echo $final_path;
// abertura do arquivo config.php:

$handle = fopen ( $final_path, 'w' ) or die ( "Unable to create file" );

// escrita no arquivo config.php:

fwrite ( $handle, $txt_inicial1 );
fwrite ( $handle, $txt_inicial2 );
fwrite ( $handle, $txt_inicial3 );
fwrite ( $handle, $txt_inicial4 );

// escrita do mysql config
fwrite ( $handle, $txt_config_description );
fwrite ( $handle, $txt_codigo_config1 );
fwrite ( $handle, $txt_codigo_config2 );
fwrite ( $handle, $txt_codigo_config3 );
fwrite ( $handle, $txt_codigo_config4 );

// escrita dos Logs Settings
fwrite ( $handle, $txt_config_log_description );
fwrite ( $handle, $txt_config_log1 );
fwrite ( $handle, $txt_config_log_d1 );
fwrite ( $handle, $txt_config_log2 );
fwrite ( $handle, $txt_config_log_d2 );
fwrite ( $handle, $txt_config_log3 );
fwrite ( $handle, $txt_config_log_d3 );
fwrite ( $handle, $txt_config_log4 );
fwrite ( $handle, $txt_config_log_d4 );
fwrite ( $handle, $txt_config_log5 );
fwrite ( $handle, $txt_config_log_d5 );

// escrita do consumer settings
//fwrite ( $handle, $txt_lti_consumer_description );
//fwrite ( $handle, $txt_lti_consumer );

// escrita de Send E-mail Settings
fwrite ( $handle, $txt_send_email_description );
fwrite ( $handle, $txt_send_email1 );
fwrite ( $handle, $txt_send_email2 );
fwrite ( $handle, $txt_send_email3 );
fwrite ( $handle, $txt_send_email4 );
fwrite ( $handle, $txt_send_email5 );

// escrita de Flag Repot Comments
fwrite ( $handle, $txt_flag_report_description );
fwrite ( $handle, $txt_flag_report );

// escrita da parte de Google Analytics
fwrite ( $handle, $txt_ga_description );

// fechamento do arquivo config.php:
fwrite ( $handle, $txt_google_analytics1 );
fwrite ( $handle, $txt_google_analytics2 );
fwrite ( $handle, $txt_google_analytics3 );
fwrite ( $handle, $txt_ga_comment1 );
fwrite ( $handle, $txt_google_analytics4 );
fwrite ( $handle, $txt_ga_tracker_comment );

// escrita das Exception Functions
fwrite ( $handle, $txt_exception_description );
fwrite ( $handle, $txt_exceptions1 );
fwrite ( $handle, $txt_exception_comments1 );
fwrite ( $handle, $txt_exceptions2 );
fwrite ( $handle, $txt_exception_comments2 );
fwrite ( $handle, $txt_exceptions3 );
fwrite ( $handle, $txt_exception_comments3 );
fwrite ( $handle, $txt_exceptions4 );
fwrite ( $handle, $txt_exception_comments4 );
fwrite ( $handle, $txt_exceptions5 );
fwrite ( $handle, $txt_exception_comments5 );

// escrita do Default Timezone Set
fwrite ( $handle, $txt_timezone );


fwrite ( $handle, $txt_final );

fclose ( $handle );
require $final_path;

echo "done";

//header("Location: dbcreate.php");

?>
