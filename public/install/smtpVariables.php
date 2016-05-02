<?php
session_start();

$smtpemail = $_POST['smtp_email'];
$smtphost = $_POST['smtp_host'];
$smtpuser = $_POST['smtp_user'];
$smtppassword = $_POST['smtp_password'];
$flagemail = $_POST['flag_email'];

$_SESSION['smtp_email']="$smtpemail";
$_SESSION['smtp_host']="$smtphost";
$_SESSION['smtp_user']="$smtpuser";
$_SESSION['smtp_password']="$smtppassword";
$_SESSION['flag_email']="$flagemail";

header("Location: gaSettings.php");
?>
