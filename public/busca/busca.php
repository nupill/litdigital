<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<?php echo __('Busca');?>
</div>
<ul id="tabs">
	<li class="selected"><a href="#simples"><?php echo __('Simples');?></a></li>
	<li><a href="#documento"><?php echo __('Documento');?></a></li>
	<li><a href="#autor"><?php echo __('Autor');?></a></li>
	<li><a href="#conteudo"><?php echo __('Conteúdo');?></a></li>
</ul>
<div id="tabs_content">
	<div id="tabs_inner_content">
		<?php include('simples/simples.php'); ?>
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

//$('ul#tabs a').each(function() {
//	var href = '#' + location.hash.split("#")[1];
//    if (href == $(this).attr('href')) {
//        $(this).click();
//        return false;
//    }
//});
	
});
</script>