<?php
// Define que o arquivo terá a codificação de saída no formato CSS
require_once(dirname(__FILE__) . '/../../application/controllers/ControleConfig.php');
$controller = ControleConfig::getInstance(); 

$borderTop = $controller->get("border-top");  
$borderBottom = $controller->get("border-bottom");
$background_color = $controller->get("background-color");
$secondary_color = $controller->get("bg-secondary-color");

header("Content-type: text/css");

?>

#body {
    padding: 306px 0 110px 0;
}

#header {
	background: -webkit-gradient(linear, left top, left bottom, from(#F7F7F7), to(#E4E4E4));
	background: -moz-linear-gradient(top,  #F7F7F7,  #E4E4E4);
	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#F7F7F7', endColorstr='#E4E4E4');	
}

#header #logo {
		
}

#header_content {
	width: 990px;
	margin: 0 auto;
	padding: 4px 0;
	color: #666;
}

#header_content a {
	color: #000;
}

#header_content .sublang {
	color: #F0F0F0;
}

#header #login #usuario, #header #login #senha_fake {
	border: 1px solid #aaa;
}

/* Central bar   
***************************************************************************/

#bar {
	width: 100%;
	background: linear-gradient(<?php echo $secondary_color;?>,<?php echo $secondary_color;?>,<?php echo $background_color;?>,<?php echo $background_color;?>,<?php echo $background_color;?>);
	border-top: <?php echo $borderTop;?>;
	border-bottom: <?php echo $borderBottom;?>;
	height: 169px;
}

#bar_content {
	width: 990px;
	margin: 0 auto;
	padding: 0;
	color: #eee;	
}

#bar_content img {
	float: left;
}

#bar_right {
	float: right;
	width: 670px;
}

#bar h2 {
	color: #f5f5f5;
	font-size: 3em;	
	margin-top: 10px;
	text-shadow: 2px 2px 5px #2c2419;
}

#bar p {
	font-size: 1.6em;
	line-height: 1.4em;
}

/* Navigation menu
***************************************************************************/

#navigation_menu {
	float: none;
	margin: 0 auto;
}

/* Home
***************************************************************************/
.home-image {
	height : 168px;
	width: 279px;
}

#mais_acessados, #ultimas_obras, #noticias, #obras_recomendadas {
	float: left;
	width: 300px;
	margin: 25px 0;
}

#mais_acessados h3, #ultimas_obras h3, #noticias h3, #obras_recomendadas h3 {
	font-weight: normal;
	font-size: 1.3em;
	text-shadow: 1px 1px 4px #bbb;
}

#mais_acessados ul, #ultimas_obras ul, #obras_recomendadas ul {
	list-style: none;	
	margin: 5px 0 0 0;
	padding: 0;
}

#mais_acessados ul li, #ultimas_obras ul li, #obras_recomendadas ul li {
	margin: 0;
	padding: 5px;
}

#mais_acessados ul li {
	border-bottom: 1px dotted #ccc;
}

#mais_acessados ul li:first-child {
	border-top: 1px dotted #ccc;
}

#ultimas_obras ul li:nth-child(odd) {
	background-color: #e5e5e5;
}

#obras_recomendadas ul li:nth-child(odd) {
	background-color: #e5e5e5;
}

#mais_acessados ul li a, #mais_acessados ul li em,
#ultimas_obras ul li a, #ultimas_obras ul li em, 
#obras_recomendadas ul li a, #obras_recomendadas ul li em {
	display: block;
	text-decoration: none;
}

#mais_acessados ul li a:hover, #ultimas_obras ul li a:hover, #obras_recomendadas ul li a:hover {
	color: #000;
}

#ultimas_obras, #obras_recomendadas {
	margin: 25px 44px;	
}
?>