<?php
if (file_exists('../../application/config/general.php')) {
	header('Location: install/');
	die;
}
?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/index style.css">
	<title>Installing Digital Library</title>
</head>
<body>
	<div>
		<img src="images/logo.png" class="imagem-logo">
		<h3>Requirements:</h3>
		<p>
			This software is primarily developed using Apache, PostgreSQL/MySQL and PHP (also sometimes known as the LAMP/WAMP platform). If in <br>
			doubt, this is the safest combination (if for no other reason than being the most common). There are other options - see the Software section that<br>
			follows: 
		</p>
		<p>
			If you are installing Moodle in a Windows server, note that from php5.5 onwards, you will also need to have the Visual C++ Redistributable for Visual <br>
			Studio 2012 installed from: http://www.microsoft.com/en-us/download/details.aspx?id=30679 Visual C++] ( x86 or x64)<br><br>
			The basic requirements for Moodle are as follows:
		</p>

		<h3>Hardware:</h3>
		<ul>
			<li>Disk space: 160MB free (min) plus as much as you need to store your materials. 5GB is probably a realistic minimum. </li>
			<li>Processor: 1GHz (min), 2GHz dual core recommended. These settings may vary according to the resources used.</li>
			<li>Backups: at least the same again (at a remote location preferably) as above to keep backups of your site.</li>
			<li>Memory: 256MB (min), 1GB or more is strongly recommended. The general rule of thumb is that Moodle can support 10 to 20 concurrent users for every 1GB of RAM, but this will vary depending on your specific hardware and software combination and the type of use. 'Concurrent' really means web server processes in memory at the same time (i.e. users interacting with the system within a window of a few seconds). It does NOT mean people 'logged in'.</li>
		</ul>

		<h3>Software:</h3>
		<ul>
			<li>Apache 2.4.9</li>
			<li>PHP 5.4.4 or higher (PHP 7 is NOT supported)</li>
			<li>MySQL 5.5.31 or higher</li>
		</ul>
		<h3>Web Browsers:</h3>
		<ul>
			<li>Google Chrome</li>
			<li>Mozila Firefox</li>
			<li>Microsoft Internet Explorer</li>
			<li>Microsoft Edge</li>
			<li>Apple Safari</li>
		</ul>
		<a href="instructions2.php">Next</a>
	</div>
</body>
</html>