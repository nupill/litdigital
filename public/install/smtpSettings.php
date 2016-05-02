<html>
<head>
	<meta http-equiv='Content-Type' content='text/html'; charset='UTF-8' />
	<link rel='stylesheet' type='text/css' href='css/smtp settings style.css'>
</head>
<body>
	<img src='images/logo.png' class='imagem-logo'>
	<form action='smtpVariables.php' method='post' name='install' onSubmit='return input_check()'>
		<h3>Sendmail settings:</h3>
		<div class='inputs-texts'>	
			<h4>Sendmail e-mail:</h4>
			<h4>Sendmail host url:</h4>
			<h4>Sendmail user:</h4>
			<h4>Sendmail password:</h4>
			<h4>Flag reports e-mail:</h4>
		</div>
		<div class='inputs'>
			<input type = 'text' name='smtp_email'><br>
			<input type = 'text' name='smtp_host'><br>
			<input type = 'text' name='smtp_user'><br>
			<input type = 'text' name='smtp_password'><br>
			<input type = 'text' name='flag_email'><br>
		</div> <br> 
		<input type= 'button' class='back-button' value='Back' onclick='history.back(-1)'>
		<input type = 'submit' class='submit-button' value='Next' >
	</form>
	<script src='js/sendmail functions.js'></script>
</body>
</html>