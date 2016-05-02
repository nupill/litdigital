<?php 
require_once(dirname(__FILE__) . '/../../../../application/controllers/ControleEstatisticas.php');
try {
	$controller = ControleEstatisticas::getInstance();
	$visits_new_returning = $controller->get_visitors_new_vs_returning();
}
catch (Exception $e) {
    exit('<div class="warning_status">'.__('Não foi possivel obter os dados').'</div>');
}
if (!$visits_new_returning) {
    echo '<div class="warning_status">'.__('Dados indisponíveis no momento').'</div>';
}
else {
?>
<script type="text/javascript">
$(document).ready(function(){  
	 var chart4 = new Highcharts.Chart({
	    chart: {
	       renderTo: 'graph_new_returning',
	       defaultSeriesType: 'column'
	    },
	    title: {
	       text: ''
	    },
	    credits: {
	        enabled: false
	    },
	    xAxis: {
	        categories: [
	           "<?php echo __('Novos');?>", 
	           "<?php echo __('Retornando');?>", 
	        ]
	    },
	    yAxis: {
	        min: 0,
	        max: 100,
	        title: {
	           text: "<?php echo __('Percentual');?>"
	        }
	     },
	     legend: {
	        enabled: false
	     },
	     tooltip: {
	        formatter: function() {
	           return '<b>' + this.y + '%</b>';
	        }
	     },
	     plotOptions: {
	        column: {
	           pointPadding: 0.2,
	           borderWidth: 0
	        }
	     },
	     series: [{
	        data: [<?php echo $visits_new_returning; ?>]
	     }]
	 });
});
</script>
<?php 
}
?>