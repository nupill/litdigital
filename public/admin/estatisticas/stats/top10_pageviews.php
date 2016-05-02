<?php 
require_once(dirname(__FILE__) . '/../../../../application/controllers/ControleEstatisticas.php');
try {
	$controller = ControleEstatisticas::getInstance();
	$top_10 = $controller->get_top10_pageviews();
}
catch (Exception $e) {
    exit('<div class="warning_status">'.__('Não foi possivel obter os dados').'</div>');
}

if (!$top_10) {
    echo '<div class="warning_status">'.__('Dados indisponíveis no momento').'</div>';
}
else {
	echo $top_10;
?>
<script type="text/javascript">
//$(document).ready(function(){  
    $('#top10_table').dataTable({
        sDom: 't',
        aaSorting: [[1,'desc']],
    	fnInitComplete: function() {
			$('#top10_table').show();
			$('.dataTables_wrapper').fadeIn();
        }
    });
//});
</script>
<?php 
}
?>