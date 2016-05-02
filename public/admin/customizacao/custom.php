<?php

require_once(dirname(__FILE__) . '/../../../application/controllers/ControleConfig.php');
if (! Auth::check ( array (
		PAPEL_ADMINISTRADOR_ID,
		PAPEL_CADASTRADOR_ID 
) )) {
	exit ( __ ( 'Acesso negado' ) );
}

$controller = ControleConfig::getInstance();

if (isset($_POST['btn_color'])) {
	$var = $_POST['btn_color'];
	$controller->update("btn_color",$var);
}
if (isset($_POST['dropdown_colors'])) {
	$var = $_POST['dropdown_colors'];
	$controller->update("dropdown_colors",$var);
}
if (isset($_POST['menu_main_color'])) {
	$var = $_POST['menu_main_color'];
	$controller->update("menu_main_color",$var);
}
if (isset($_POST['btn_hover_color'])) {
	$var = $_POST['btn_hover_color'];
	$controller->update("btn_hover_color",$var);
}
if (isset($_POST['btn_hover_border_color'])) {
	$var = $_POST['btn_hover_border_color'];
	$controller->update("btn_hover_border_color",$var);
}

if (isset($_POST['third_div_content'])) {
	$var = $_POST['third_div_content'];
	$controller->update("div_noticias",$var);
}
if (isset($_POST['footer_content'])) {
	$var = $_POST['footer_content'];
	$controller->update("footer_content",$var);
}

if(isset($_POST['main_color'])) {
	$newColor = $_POST['main_color'];
	$controller->update("background-color", $newColor);		
}

if (isset($_POST['secondary_color'])) {
	$newColor = $_POST['secondary_color'];
	$controller->update("bg-secondary-color", $newColor); 
}
if (isset($_POST['borders_color'])) {
	$newColor = $_POST['borders_color'];
	//original border color: #2c2419
	$controller->update("border-color",$newColor);
	$controller->update("border-top", '7px solid' . $newColor);
	$controller->update("border-bottom",'10px solid' . $newColor);
}
if(isset($_POST['phone']) && $_POST['phone'] != "") {
	$newPhone = $_POST['phone'];
	$controller->update("phone", $newPhone);
}
if(isset($_POST['email']) && $_POST['email'] != "") {
	$newEmail = $_POST['email'];
	$controller->update("email", $newEmail);
}
if(isset($_POST['copyright']) && $_POST['copyright'] != "") {
	$newCr = $_POST['copyright'];
	$controller->update("copyright", $newCr);
}
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Conteúdo Terceira Coluna');?></a> &raquo; <?php echo __('Customização');?></h2>
	<div class="foto-ads">
		<fieldset>
			<legend><?php echo _('Alterar Logo do repositório:')?></legend>
			<form method="post" enctype="multipart/form-data">
				must be jpg/jpeg/png/gif <br>
				<input type="file" name="logo_img"><br><br>
				<input type="submit" value="Atualizar">
			</form>
		</fieldset>
		<?php 
			if (isset($_FILES['logo_img'])) {
				$logo_img = $_FILES['logo_img']['name'];
				$ext = strtolower(substr($logo_img, strpos ($logo_img, '.') + 1));
				$temp = $_FILES['logo_img']['tmp_name'];
				if (!empty($logo_img) && ($ext == "jpg" || $ext == "jpeg" || $ext == "png"|| $ext == "gif")) {
					$location = '../../_images/';
					if(move_uploaded_file($temp, $location . 'logo.png')) {
						echo 'Image uploaded.';
					}
				} else {
					echo 'Failed to upload';
				}
			}
		?>
	</div>
	<div class="foto-ads">
		<fieldset>
			<legend><?php echo _('Adicionar imagem da barra da home:')?></legend>
			<form method="post" enctype="multipart/form-data">
				must be jpg/jpeg/png/gif <br>
				<input class="logo-upload  btn btn-primary" name="home_img" type="file"><br><br>
				<input type="submit" value="Definir Imagem"></input>
			</form>
		</fieldset>
		<?php if (isset($_FILES['home_img'])) {
				$logo_img = $_FILES['home_img']['name'];
				$ext = strtolower(substr($logo_img, strpos ($logo_img, '.') + 1));
				$temp = $_FILES['home_img']['tmp_name'];
				if (!empty($logo_img) && ($ext == "jpg" || $ext == "jpeg" || $ext == "png")) {
					$location = '../../_images/';
					if(move_uploaded_file($temp, $location . 'home_img.png')) {
						echo 'Image uploaded.';
					}
				} else {
					echo 'Failed to upload';
				}
			}
		?>
	</div>
	<div id="colorEdition">
		<fieldset>
			<legend><?php echo _('Altere aqui as cores da barra de título:')?></legend>
				<div id="homeBarColor" class="colors">
				</div><br>
				<div>
					<form method="post">
						secondary color: <input id="secColorInput" onchange="updateColors()" class="custom__inputtxt" type="color" value="<?php echo $controller->get("bg-secondary-color"); ?>" name="secondary_color"><br>
						main color: <input id="mainColorInput" onchange="updateColors()" title="teste" class="custom__inputtxt" type="color" value="<?php echo $controller->get("background-color"); ?>" name="main_color"><br>
						borders color:	<input class="custom__inputtxt" onchange="updateColors()" type="color" value="<?php echo $controller->get("border-color"); ?>" name="borders_color"><br>
						<input type="submit" value="Definir"id="test2">
					</form>
				</div>
		</fieldset>
	</div>
	<fieldset>
		<legend><?php echo _('Botões, abas e listas:')?></legend>
		<form method="post">
			Botões:<input type="color" value="<?php echo $controller->get("btn_color"); ?>" name="btn_color"></input><br>
			Abas do sistema:<input type="color" value="<?php echo $controller->get("menu_main_color"); ?>" name="menu_main_color"></input><br>
			Dropdowns: <input type="color" value="<?php echo $controller->get("dropdown_colors"); ?>" name="dropdown_colors"></input><br> <br>
			<input type="submit" value="Definir">
		</form>
	</fieldset>
	<fieldset>
		<legend><?php echo _('Botões selecionados:')?></legend>
			<form method="post">
				<div>
					Button: <input type="color" value="<?php echo $controller->get("btn_hover_color"); ?>" name="btn_hover_color"></input><br>
					Button border: <input type="color" value="<?php echo $controller->get("btn_hover_border_color"); ?>" name="btn_hover_border_color"></input><br><br>
					<input type="submit" value="Definir">
				</div>
			</form>
	</fieldset>
	<fieldset>
		<legend><?php echo _('Conteúdo direito da home:')?></legend>
		<form method="post">
			<textarea class="ckeditor" name="third_div_content" ><?php echo $controller->get("div_noticias");?></textarea><br>
		
			<input type="submit" value="Atualizar">
		</form>
	</fieldset>
	<fieldset>
		<legend><?php echo _('Footer:')?></legend>
		<form method="post">
			<textarea class="ckeditor" name="footer_content" ><?php echo $controller->get("footer_content");?></textarea><br>
			<input type="submit" value="Atualizar">
		</form>
	</fieldset>
</div>
<script type="text/javascript">
		function updateColors() {
			var color1 = document.getElementById('mainColorInput').value;
			var color2 = document.getElementById('secColorInput').value;
			document.getElementById('homeBarColor').style.background =	"linear-gradient("+color2+","+color1+")";
		}
</script>