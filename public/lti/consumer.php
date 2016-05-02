<?php
session_start ();
// require_once ("../../application/include/mysqli.php");
require_once ("ims-blti/blti_util.php");
require_once ("config.php");

function mysqlx_result($res,$row=0,$col=0){
	$numrows = mysqli_num_rows($res);
	if ($numrows && $row <= ($numrows-1) && $row >=0){
		mysqli_data_seek($res,$row);
		$resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
		if (isset($resrow[$col])){
			return $resrow[$col];
		}
	}
	return false;
}


 $con1 = mysqli_connect ( $config ['mysql_host'], $config ['mysql_user'], $config ['mysql_password'], $config ['mysql_database']) or die ( 'Error connecting to mysql' );
// mysql_connect ( $config ['mysql_host'], $config ['mysql_user'], $config ['mysql_password'] ) or die ( 'Error connecting to mysql' );

$query = "SELECT * FROM " . $config ['mysql_table'];
$result = mysqli_query ($con1, $query );
// $result = mysql_query ($query );

// trocar abaixo por mysqlx_result
$key = mysqlx_result ( $result, 0, 1 );
$secret = mysqlx_result ( $result, 0, 2 );
$endpoint = mysqlx_result ( $result, 0, 3 );

$atividade = "default";
$email_usuario = isset ( $_SESSION ['email'] ) ? $_SESSION ['email'] : '';
$nome_usuario = isset ( $_SESSION ['nome'] ) ? $_SESSION ['nome'] : '';
$autor_documento = isset ( $_SESSION ['autor_documento'] ) ? $_SESSION ['autor_documento'] : '';
$url_documento = isset ( $_SESSION ['url_documento'] ) ? $_SESSION ['url_documento'] : '';
$titulo_documento = isset ( $_SESSION ['titulo_documento'] ) ? $_SESSION ['titulo_documento'] : '';

$lmsdata = array ("custom_activity" => $atividade, "lis_person_contact_email_primary" => $email_usuario, "lis_person_name_full" => $nome_usuario, "custom_document_author" => $autor_documento, "custom_document_url" => $url_documento, "custom_document_title" => $titulo_documento, "context_id" => "456434513", "tool_consumer_instance_guid" => "www.inf.ufsc.br" );

//$lmsdata = array ("activity" => $atividade, "email_usuario" => $email_usuario, "nome_usuario" => $nome_usuario, "autor_documento" => $autor_documento, "url_documento" => $url_documento, "titulo_documento" => $titulo_documento, "context_id" => "456434513" );

$parms = $lmsdata;

// Add oauth_callback to be compliant with the 1.0A spec
$parms ["oauth_callback"] = "about:blank";

$parms = signParameters ( $parms, $endpoint, "POST", $key, $secret, "Press to Launch", $tool_consumer_instance_guid, $tool_consumer_instance_description );

$content = postLaunchHTML ( $parms, $endpoint, false, false );
print ($content) ;

?>
