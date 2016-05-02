<?php 
require_once(dirname(__FILE__) . '/../../../../application/controllers/ControleEstatisticas.php');
try {
	$controller = ControleEstatisticas::getInstance();
	$trafficsource_results = $controller->get_traffic_source();
}
catch (Exception $e) {
    exit('<div class="warning_status">'.__('Não foi possivel obter os dados').'</div>');
}
if (!$trafficsource_results) {
	echo '<div class="warning_status">'.__('Dados indisponíveis no momento').'</div>';
}
else {
?>
<script type="text/javascript">
$(document).ready(function(){  
    var chart2 = new Highcharts.Chart({
        chart: {
           renderTo: 'graph_trafficsource',
           defaultSeriesType: 'pie',
           margin: [40,0,100,0]
        },
        title: {
           text: "<?php echo __('Fonte de tráfego');?>",
           style: {
               color: '#333',
               font: '14px Tahoma, "Lucida Sans Unicode", Verdana, Arial'
           }
        },
        credits: {
           enabled: false
        },
        plotArea: {
           shadow: null,
           borderWidth: null,
           backgroundColor: null
        },
        tooltip: {
           formatter: function() {
        	  if (this.point.name.length > 25) {
	        	  return '<b>'+ this.point.name.substring(0, 24) +'.</b>: '+ this.y +'%';
	          }
	          return '<b>'+ this.point.name +'</b>: '+ this.y +'%';
           }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                dataLabels: {
                    enabled: false
                }
            }
         },
         legend: {
            layout: 'vertical',
            style: {
               left: '0px',
               bottom: '10px',
               right: 'auto',
               top: 'auto',
            },
            itemStyle: {
                color: '#555',
                font: '11px Tahoma, "Lucida Sans Unicode", Verdana, Arial'
            }
         },
         series: [{
            type: 'pie',
            data: [<?php echo $trafficsource_results; ?>]
         }]
     });
});
</script>
<?php 
}
?>