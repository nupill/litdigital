<?php 
require_once(dirname(__FILE__) . '/../../../../application/controllers/ControleEstatisticas.php');
try {
	$controller = ControleEstatisticas::getInstance();
	$visits_pageviews_results = $controller->get_visits_pageviews();
}
catch (Exception $e) {
    exit('<div class="warning_status">'.__('Não foi possivel obter os dados').'</div>');
}
if (!$visits_pageviews_results) {
    echo '<div class="warning_status">'.__('Dados indisponíveis no momento').'</div>';
}
else {
?>
<script type="text/javascript">
$(document).ready(function(){  
	 var chart = new Highcharts.Chart({
       chart: {
          renderTo: 'graph_visits_pageviews',
          defaultSeriesType: 'line',
          zoomType: 'x',
          margin: [30,60,60,60]
       },
       title: {
          text: ''
       },
       xAxis: {
           type: 'datetime',
           maxZoom: 7 * 24 * 3600000 // 7 days
       },
       yAxis: [
           {
              min: 0,
              title: {
                 text: "<?php echo __('Visitas');?>",
                 style: {
                     color: '#555',
                     font: '12px Tahoma, "Lucida Sans Unicode", Verdana, Arial'
                 }
              }
          },{
              opposite: true,
              min: 0,
              title: {
                 text: "<?php echo __('Exibições de páginas');?>",
                 style: {
                     color: '#555',
                     font: '12px Tahoma, "Lucida Sans Unicode", Verdana, Arial'
                 }
              }
          }
       ],
       credits: {
           enabled: false
       },
       tooltip: {
           formatter: function() {
               return '<b>' + this.series.name + '</b><br />' + Highcharts.dateFormat('%d/%m/%Y', this.x) + ':<b> ' + this.y + '</b>';
           }
       },
       legend: {
           itemStyle: {
               color: '#666',
               font: '12px Tahoma, "Lucida Sans Unicode", Verdana, Arial'
           }
       },
       series: [{
           pointInterval: 24 * 3600 * 1000,
           pointStart: Date.UTC(<?php echo date('Y'); ?>, <?php echo date('n')-2; ?>, <?php echo date('j'); ?>),
           name: "<?php echo __('Visitas');?>",
           data: [<?php echo $visits_pageviews_results['visits']; ?>],
           yAxis: 0
       }, {
           pointInterval: 24 * 3600 * 1000,
           pointStart: Date.UTC(<?php echo date('Y'); ?>, <?php echo date('n')-2; ?>, <?php echo date('j'); ?>),
           name: "<?php echo __('Exibições de páginas');?>",
           data: [<?php echo $visits_pageviews_results['pageviews']; ?>],
           yAxis: 1
       }]
    });
});
</script>
<?php 
}
?>