<?php ob_start('ob_gzhandler'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->get_title(); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="<?php echo ROOT_URI ?>favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="<?php echo CSS_URI ?>global.php" />
<link rel="stylesheet" type="text/css" href="<?php echo CSS_URI ?>jquery.qtip.min.css" />
<?php $this->import_css(); ?>
<script type="text/javascript" src="<?php echo JS_URI ?>jquery.min.js"></script>
<script type="text/javascript">
    var string1='<?php echo __("Tem certeza que deseja excluir o(s) registro(s) selecionados?");?>';
    var string2='<?php echo __("A tabela deve estar dentro de um formulário");?>';
    var string3='<?php echo __("Carregando");?>...';
    var string4='<?php echo __("Exibir _MENU_ resultados");?>';
    var string5='<?php echo __("Nenhum resultado encontrado");?>';
    var string6='<?php echo __("Exibindo _START_ a _END_ de _TOTAL_ resultados");?>';
    var string7='<?php echo __("Nenhum resultado");?>';
    var string8='<?php echo __("(filtrados de _MAX_ resultados)");?>';
    var string9='<?php echo __("Procurar");?>:';
    var string10='<?php echo __("Primeira");?>';
    var string11='<?php echo __("Anterior");?>';
    var string12='<?php echo __("Próxima");?>';
    var string13='<?php echo __("Última");?>';
    var string14='<?php echo __("Excluir selecionados");?>';
    var string15='<?php echo __("Cadastrar");?>';

jQuery.fn.defuscate = function() {
   return this.each(function(){
     var email = String($(this).html()).replace(/\s*\(.+\)\s*/, "@");
     $(this).before('<a href="mailto:' + email + '">' + email + "</a>").remove();
   });
};
$(function() {
	$('.email').defuscate();
	$('input[title], textarea[title]').each(function() {
        if ($(this).val() === '') {
            $(this).val($(this).attr('title')).addClass('placeholder');
        }
        if ($(this).val() === $(this).attr('title')) {
            $(this).addClass('placeholder');
        }
        $(this).focus(function() {
            if ($(this).val() === $(this).attr('title')) {
                $(this).val('').removeClass('placeholder');
            }
        });
        $(this).blur(function() {
            if ($(this).val() === '') {
                $(this).val($(this).attr('title')).addClass('placeholder');
            }
        });
    });
	$('input[type=submit]').removeClass('disabled').attr('disabled', false);
});
</script>
</head>
<body>

<div id="main">
	<div id="header">
		<div id="header_content">
			<?php $this->show_header(); ?>
		</div>
	</div>
	<div id="body">
	    <?php $this->show_body(); ?>
	</div>
</div>
<div id="footer">
	<div id="footer_content">
		<?php $this->show_footer(); ?>
    </div>
</div>

<script type="text/javascript" src="<?php echo JS_URI ?>jquery.form.js"></script>
<script type="text/javascript" src="<?php echo JS_URI ?>jquery.md5.js"></script>
<script type="text/javascript" src="<?php echo JS_URI ?>jquery.qtip.min.js"></script>
<?php $this->import_js(); ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
	var pageTracker = _gat._getTracker("<?php global $public_config; echo $public_config['ga_tracker']; ?>");
	pageTracker._trackPageview();
} catch(err) {}
</script>
</body>
</html>
<?php ob_end_flush(); ?>