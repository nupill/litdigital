<?php
session_start();

$gaprofile = $_POST['ga_profile'];
$gaemail = $_POST['ga_email'];
$gapassword = $_POST['ga_password'];
$gatracker = $_POST['ga_tracker'];
$url = $_POST['url'];

$_SESSION['ga_profile'] = "$gaprofile";
$_SESSION['ga_email'] = "$gaemail";
$_SESSION['ga_password'] = "$gapassword";
$_SESSION['ga_tracker'] = "$gatracker";
$_SESSION['url'] = "$url";

header("Location: install.php");
?>
