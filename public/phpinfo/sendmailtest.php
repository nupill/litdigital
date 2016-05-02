<?php
$to      = 'roberto.willrich@gmail.com';
$subject = 'Fake sendmail test';
$message = 'If we can read this, it means that our fake Sendmail setup works!';
$headers = 'From: literaturabrasileira@sistemas.ufsc.br' . "\r\n" .
		'Reply-To: literaturabrasileira@sistemas.ufsc.br' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

if(mail($to, $subject, $message, $headers)) {
	echo 'Email sent successfully!';
} else {
	die('Failure: Email was not sent!');
}