<?php
require_once(dirname(__FILE__) . '/../../application/controllers/ControleConfig.php');
$controller = ControleConfig::getInstance();

$border_color = $controller->get("border-color");
$btn_color = $controller->get("btn_color");
$btn_hover_color = $controller->get("btn_hover_color");
$btn_hover_border_color = $controller->get("btn_hover_border_color");
$menu_main_color = $controller->get("menu_main_color");
$dropdown_color = $controller->get("dropdown_colors");

header("Content-type: text/css");
?>

/* General
***************************************************************************/

html, body, div, form, fieldset, legend, label {
    margin: 0;
    padding: 0; 
}

html, body {
    font-size: 0.9em;
    letter-spacing: 0.02em;
    line-height: 1.4em;
    font-family: "Lucida Sans", "Trebuchet MS", Verdana, "MS Sans Serif", Arial;
    /*background: #F0F0F0 url(../_images/background_body.png) top left repeat;*/
    background-color: #F0F0F0;
    color: #333;
    height: 100%;
}

img { border: 0; }

hr {
	border: none 0;
	border-top: 1px dotted #777;
	height: 1px;
}

/* Headers 
***************************************************************************/

h1, h2, h3, h4, h5, h6 {
    margin: 0;
    font-weight: bold;
}

h1 {
    font-size: 2em;
    line-height: 1.6em;
    font-weight: normal;
}

h2 {
    font-size: 1.45em;
    line-height: 1.5em;
    font-weight: normal;
}

h3 {
    font-size: 1.2em;
    line-height: 1.4em;
}

h4 {
    font-size: 1em;
    line-height: 1.4em;
}

h5 {
    font-size: 0.9em;
    line-height: 1.3em;
}

h6 {
    font-size: 0.8em;
    line-height: 1em;
}

/* Texts
***************************************************************************/

p {
    margin: 0;
    padding: 0.6em 0;
    text-align: justify;
}

em {
    font-style: italic;
    font-size: 1em;
    color: #808080;
}

blockquote {
	margin: 0px;
	padding: 10px 5px;
	quotes: "\201C" "\201D";
}

blockquote:before {
	content: open-quote;
	font-weight: bold;
	font-size: 170%;
	color: #cda399;
	padding-right: 5px;
}

blockquote:after {
	content: close-quote;
	font-weight: bold;
	font-size: 170%;
	color: #cda399;
	padding-left: 5px;
}

a {
    color: #000;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Forms
***************************************************************************/

label { 
    font-weight: bold;
    display: block;
}

label.normal {
	font-weight: normal;
	display: inline;
}

label span {
	font-weight: normal;
	font-size: 80%;
	color: #666;
	float: right;	
}

fieldset {
    padding: 0 1.4em 1.4em 1.4em;
    margin: 0 0 .7em 0;
    border: 1px solid #ccc;
    -moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

legend {
    font-weight: bold;
    font-size: 1.1em;
    margin-bottom: 5px;
    padding-bottom: 5px;
}

input[type=text], input[type=password],
textarea, select { 
  background-color: #fff; 
  border: 1px solid #aaa;
  color: #444;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  border-radius: 5px;
  padding: 4px 5px;
  width: 240px;
  font: inherit;
}

select {
	width: 252px;
	/*height: 27px;*/
	padding: 3px 5px;
}

input[type=text]:focus, input[type=password]:focus,
input.text:focus, input.title:focus, 
textarea:focus, select:focus { 
  border-color: #222; 
}

label {
  margin: 0.15em 0;
}

textarea {
    width: 232px;
    height: 100px;
}

input[type=checkbox], input[type=radio], 
input.checkbox, input.radio { 
  position: relative;
  top: .25em; 
}

input[type=button], input[type=submit], button { 
    background: <?php echo $btn_color?>;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5F5341', endColorstr='#463b2b');
    color: white;
    padding: 3px 10px;
    /*margin-left: 10px;*/
    border: 1px solid <?php echo $btn_color?>;
    cursor: pointer;
    min-width: 40px;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    border-radius: 6px;
    font: inherit;
    -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2);
	-moz-box-shadow: 0 1px 2px rgba(0,0,0,.2);
	box-shadow: 0 1px 2px rgba(0,0,0,.2);
}

input[type=button]:hover, input[type=submit]:hover, button:hover { 
	background: <?php echo $btn_hover_color?>;
	border: 1px solid <?php echo $btn_hover_border_color; ?>;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#6F614C', endColorstr='#4F4536');
}

input[type=submit].disabled, input[type=text].disabled, button.disabled, select.disabled {
    color: #ddd;
    opacity: 0.6;
    filter:alpha(opacity=60);
    -khtml-opacity: 0.6;
    -moz-opacity: 0.6;
    cursor: default;
}

input[type=button].disabled:hover, input[type=submit].disabled:hover, button.disabled:hover { 
    background: #463b2b;
	background: -webkit-gradient(linear, left top, left bottom, from(#5F5341), to(#463b2b));
	background: -moz-linear-gradient(top, #5F5341, #463b2b);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5F5341', endColorstr='#463b2b');
}

/*input[type=text].disabled {
    background-color: #eee; 
}*/

input.placeholder, textarea.placeholder {
	font-style: italic;
	color: #777;
}

form.inline {  }
form.inline p { 
	margin: 0;
	padding: 4px 0;
	float: left;
	margin-right: 20px;
	min-height: 40px;
}
form.inline fieldset {
	padding-right: 0;
}

.form_table td {
    padding: 2px;
}

.form_table td:first-child, .form_table td.label {
  text-align: right;
  vertical-align: top;
  padding-top: 5px;
}

/* Containers
***************************************************************************/

#main {
    width: 990px;
    margin: 0 auto -85px;
    min-height: 100%;
    height: auto !important;
    height: 100%;
}

#header {
	width: 100%;
	height: 120px;
	/*background-color: #F5F5F5;
	background: -webkit-gradient(linear, left top, left bottom, from(#F7F7F7), to(#E3E3E3));
	background: -moz-linear-gradient(top,  #F7F7F7,  #E3E3E3);
	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#F7F7F7', endColorstr='#E3E3E3');*/	
	background-color: <?php echo $border_color;?>;
	/*color from database \/ */
	border-top: 8px solid <?php echo $border_color;?>;
	border-color: <?php echo $border_color;?>;
	position: absolute;
	top: 0;
	left: 0;
}

#header_content {
	background-color:#f0;
	width: 990px;
	margin: 0 auto;
	padding: 4px 0;
	color: #e5e5e5;
}

#header_content a {
	color: #f0f0f0;
}

#body {
    padding: 130px 0 120px 0;
}

#content {
	clear: both;
	margin-top: 25px;
}

#footer {
    padding: 15px 0;
    width: 100%;
    height: 50px;
    clear: both;
}

#footer_content {
    width: 990px;
    margin: 0 auto;
    color: #666;
}

/* Header
***************************************************************************/

#header #logo {
	line-height: 1;
	margin-top: 10px;
	font-size: 0em;
	text-indent: -9999px;
	width: 306px;
	height: 70px;
	cursor: pointer;
	float: left;
}

#header #login, #header #logout {
    float: right;
    margin-top: 3px;
}

#header #login p.links, #header #logout p.links {
	text-align: right;
	line-height: 0.2em;
}

#header #login #usuario {
	background: #fff url(../_images/user.png) 7px center no-repeat;
	
	border: 1px solid #463B2B;
	padding-left: 25px;
	width: 140px;
}

#header #login #senha_fake {
	background: #fff url(../_images/password.png) 7px center no-repeat;
	border: 1px solid <?php echo $border_color;?>;
	padding-left: 25px;
	margin: 0 5px;
	width: 140px;
}

/* Central bar
***************************************************************************/

#bar {
	width: 100%;
	position: absolute;
	top: 120px;
	left: 0;
	background-color: <?php echo $border_color;?>;	<!-- original color: #2c2419 -->
	height: 10px;
}

/* Navigation menu
***************************************************************************/

#navigation_menu {
	list-style: none;
	/*color from database \/*/
	background-color: <?php echo $menu_main_color?>;
	width: 450px;
	float: right;
	margin: 0;
	padding: 0 5px 10px 5px;
	font-size: 120%;
	text-align: center;
	-moz-border-radius: 0 0 6px 6px;
    -webkit-border-radius: 0 0 6px 6px;
    border-radius: 0 0 6px 6px;
}

#navigation_menu li ul {
	background-color: <?php echo $dropdown_color;?>;
}

#navigation_menu li {
	display: inline;
	padding: 5px 15px;
}

#navigation_menu li a {
	color: #e5e5e5;
}

#navigation_menu li a:hover {
	text-decoration: none;
	color: #fff;
}

#navigation_menu .active a {
	color: #fff;
	font-weight: bold;
}

/* Footer
***************************************************************************/

#footer_content h5 {
	font-size: 110%;
	color: #444;
}

#footer_left_up {
	margin-left: 390px;
}

#footer_left {
	text-align: center;
}

#footer-left .footer-contact {
	margin-left: 100px;
}

.line2footer {
	margin-left: 27px;
}

#logos {
	margin-left: 380px;
}

#logos .lit-logo {
	margin-left: 20px;
}

/* Breadcrumbs (navigation)
***************************************************************************/

#breadcrumbs {
	margin: 5px 0;
	color: #666;
}


/* Errors, Success and Warning messages
***************************************************************************/

.error {
    padding: 2px 2px 2px 22px;
    background: url(../_images/error.png) center left no-repeat;
}

.warning {
    padding: 2px 2px 2px 22px;
    background: url(../_images/warning.png) center left no-repeat;
}

.success {
    padding: 2px 2px 2px 22px;
    background: url(../_images/success.png) center left no-repeat;
}

.error_box, .warning_box, .success_box {
	padding: 5px;
    margin: 6px 0 3px 0;
	font-size: 85%;
    -moz-border-radius: 4px;
    -webkit-border-radius:4px;
    border-radius: 4px;
}

.error_box {
    background-color: #FFF4F4;
    color: #8a1f11;
    border: 2px solid #C41300;
}

.warning_box {
    background-color: #FFFFCC;
    color: #000;
    border: 2px solid #FDF053;
}

.success_box {
    background-color: #DDE9CD;
    color: #172F00;
    border: 2px solid #6C8F3D;
}

input.input_error {
    background-color: #FFF4F4;
    color: #8a1f11;
    border: 2px solid #C41300;
}

input.input_error:focus {
    border: 2px solid #900000;
}

/* Pagination control
***************************************************************************/

.pagination-wrapper {
	margin-top: 10px;
	text-align: center;
}

.pagination {
	margin: 0;
	padding: 0;
}

.pagination li {
	border: 0;
	padding: 0;
	list-style: none;
	margin: 0 2px;
	display: inline;
	-moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
}

.pagination a {
	border: solid 1px #69543C;
	margin: 0;
	-moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
}

.pagination .previous-off, .pagination .next-off {
	border: solid 1px #DEDEDE;
	color: #888888;
	font-weight: bold;
	margin-right: 2px;
	padding: 3px 4px;
}

.pagination .next a:link, .pagination .previous a:link {
	font-weight: bold;
	padding: 3px 4px;
}

.pagination .active_page {
	background: #3F3221;
	color: #FFFFFF;
	font-weight: bold;
	padding: 4px 7px;
}

.pagination a:link, .pagination a:visited {
	color: #2C2419;
	padding: 3px 6px;
	text-decoration: none;
}

.pagination a:hover {
	border: solid 1px #000
}

/* Misc 
***************************************************************************/

#status {
	margin-top: 17px;
}

.loading {
    padding: 10px;
    background: url(../_images/loading.gif) center center no-repeat;
}

input.loading_dark {
    padding: 3px 10px;
    background: #463b2b url(../_images/loading_dark.gif) center center no-repeat;
}

.clear {
    clear: both;
    padding: 0px;
    margin: 0px;
    font-size: 1px;
    height: 0px;
}

.left {
	float: left;
}

.right {
	float: right;
}

.info {
    padding: 2px 2px 2px 22px;
    background: url(../_images/info.png) center left no-repeat;
}

a.stealth {
    color: inherit;
}

/* General dropdown styles */       
.dropdown dd, .dropdown dt, .dropdown ul { margin:0px; padding:0px; }
.dropdown dd { position:relative; }
/* DT styles for sliding doors */
.dropdown dt a {background:#463826 url(arrow.png) no-repeat scroll right center;
    display:block; padding-right:20px; border:1px solid #463826 width:150px;}
.dropdown dt a span {cursor:pointer; display:block; padding:5px; }
/* UL styles */
.dropdown dd ul { background:brown none repeat scroll 0 0; display:none ; z-index: 9999;
    list-style:none; padding:5px 0px; position:absolute; 
    left:0px; top:2px; width:auto; min-width:170px;}
.dropdown span.value { display:none;}
.dropdown dd ul li a { padding:5px; display:block;}
