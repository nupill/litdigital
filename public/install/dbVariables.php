<?php

session_start();

/*!
	Variables recieved from imputs	
*/
$language = $_POST['language'];
$timezone = $_POST['timezone'];
$phone = $_POST['phone'];
$conEmail = $_POST['con_email'];
$dbhost = $_POST['dbhost'];
$dbname = strtolower ( trim ( $_POST['dbname']) );
$dbuser = strtolower ( trim ( $_POST['dbuser']) );
$dbpassword = strtolower ( trim ( $_POST['dbpassword']) );
$logemail = strtolower ( trim ( $_POST['log_email'] ) );


/*!
	Variables saved on session	
*/
$_SESSION['log_email'] = "$logemail";
$_SESSION['language'] = "$language";
$_SESSION['phone'] = "$phone";
$_SESSION['con_email'] = "$conEmail";
$_SESSION['timezone'] = "$timezone";
$_SESSION['dbname'] = "$dbname";
$_SESSION['dbuser'] = "$dbuser";
$_SESSION['dbpassword'] = "$dbpassword";

header("Location: smtpSettings.php");

?>
