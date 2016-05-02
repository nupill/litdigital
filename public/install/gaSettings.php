
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="css/google analytics style.css">
</head>
<body>
	<img src="images/logo.png" class="imagem-logo">

	<h3>Google Analytics configuration:</h3>
	<p>
		Click <a href="www.google.com/analytics">HERE</a> to enter the G.A. web page. <br>
		Follow the instructions on the website, than save your <br>"tracker" code (Ex: UA-XXXXXXX-X) and your ID number.<br>
		You must use your gmail adress to access the Google Analytics options.<br>
		<h4 class = "ps">Ps: We won't have access to your Gmail account.</h4>
	</p>
	
	<form action="gaVariables.php" method="post" name="install" onSubmit="return input_check()">
		<div class="inputs-texts">	
			<h4>Your ID Number:</h4><h4>Your Gmail adress:</h4><h4>Your Gmail Password:</h4><h4>Analytics Tracker:</h4><h4>Url:</h4>
		</div>
		<div class="inputs">
			<input type = "text" name = "ga_profile"><br>
			<input type = "text" name = "ga_email"><br>
			<input type = "text" name = "ga_password"><br>
			<input type = "text" name = "ga_tracker"><br>
			<input type = "text" name = "url">
		</div> <br> 
		<input type= "button" class="back-button" value="Back" onclick="history.back(-1)">
		<input type = "submit" class="submit-button" value="Next">
	</form>
	<script src="js/ga functions.js"></script>
</body>
</html>