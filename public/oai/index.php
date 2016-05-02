<?php
require_once('oaidp-config.php');
$pos = strrpos($MY_URI, '/');
$MY_URI = substr($MY_URI, 0, $pos). '/oai2.php';

?>
<html>
<head>
<title>www.literaturabrasileira.ufsc.br Data Provider</title>
</head>
<body bgcolor="#ffffff">
<blockquote>
<h3>www.literaturabrasileira.ufsc.br Data Provider</h3>
<p>This is the OAI-PMH 2.0 Data Provider.</p>
<dt><a name="tests" />Query and check our Data-Provider</dt>
  <dd><a href="<?=$MY_URI ?>?verb=Identify">Identify</a></dd>
  <dd><a href="<?=$MY_URI?>?verb=ListMetadataFormats">ListMetadataFormats</a></dd>
  <dd><a href="<?=$MY_URI?>?verb=ListSets">ListSets</a></dd>
  <dd><a href="<?=$MY_URI?>?verb=ListIdentifiers&amp;metadataPrefix=oai_dc">ListIdentifiers</a></dd>
  <dd><a href="<?=$MY_URI?>?verb=ListRecords&amp;metadataPrefix=oai_dc">ListRecords</a></dd>
</dt>
<p>
For detailed tests use the <a href="http://re.cs.uct.ac.za/">Repository Explorer</a>.
<p>
Any comments or questions are welcome.
<p />	
Roberto Willrich<br />
Universidade Federal de Santa Catarina<br />
roberto.willrich#ATufsc.br<br />
</blockquote>
</body>
</html>



