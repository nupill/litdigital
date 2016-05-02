<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
	<?php echo __('Navegação'); ?>
</div>
<ul id="tabs">
	<li class="selected"><a href="#autor"><?php echo __('Autores'); ?></a></li>
	<li><a href="#documento"><?php echo __('Documentos'); ?></a></li>
	<li><a href="#acervo"><?php echo __('Acervos'); ?></a></li>
	<li><a href="#fatos"><?php echo __('Fatos Históricos'); ?></a></li>
	<li><a href="#editoras"><?php echo __('Editoras'); ?></a></li>
	
</ul>
<div id="tabs_content">
	<div id="tabs_inner_content">
		<?php include('autor/autor.php'); ?>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){

	function loadContent(hash) {
        if (hash) {
            $("ul#tabs li").removeClass("selected");
            var a = $('a[href="#'+hash+'"]');
            a.parent().addClass("selected");
            $('.loading').remove();
            a.append('<span class="loading" style="position: absolute; right: 5px; top: 3px"></span>');
            $.get(hash + '/?action=getForm', function(response) {
                $('.loading').fadeOut(600);
                $('#tabs_inner_content').fadeOut(600, function() {
                    $(this).html(response).fadeIn(600);
                });
            });
        }
    }
	
	$.history.init(loadContent);
	
	$("ul#tabs li a").click(function() {
		var url = $(this).attr('href');
        url = url.replace(/^.*#/, '');
        $.history.load(url);
		return false;
	});
});
</script>