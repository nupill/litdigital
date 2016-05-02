<?php
require_once(APPLICATION_PATH . '/include/DB.php');
require_once(APPLICATION_PATH . '/controllers/ControleEstatisticas.php');

if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

$controller = ControleEstatisticas::getInstance();
?>
<div id="content">
    <h2><a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo; <?php echo __('Estatísticas');?></h2>
    <br />
	<div id="date_range">
    	<?php echo __('Período dos relatórios');?>:
    	<strong><?php echo date('d/m/Y' , mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))); ?></strong> até
    	<strong><?php echo date('d/m/Y'); ?></strong>
    </div>
    <div id="dashboard_left">
    	<div id="site_usage">
        	<?php 
        	$report = $controller->get_site_usage();
        	if ($report) {
        	?>
			<h2><?php echo __('Uso do site');?></h2>
			<p><?php echo __('Visitas');?><span><?php echo $report['ga:visits']; ?></span></p>
	    	<p><?php echo __('Novas visitas');?><span><?php echo $report['ga:newVisits']; ?></span></p>
			<p><?php echo __('Páginas exibidas');?><span><?php echo $report['ga:pageviews']; ?></span></p>
			<p><?php echo __('Visitantes');?><span><?php echo $report['ga:visitors']; ?></span></p>
	    	<p><?php echo __('Visitas/Visitantes');?><span><?php echo round($report['ga:visits']/$report['ga:visitors'], 2); ?></span></p>
			<p><?php echo __('Páginas/Visita');?><span><?php echo round($report['ga:pageviews']/$report['ga:visits'], 2); ?></span></p>
			<p><?php echo __('Tempo médio no site');?><span><?php echo gmdate("H:i:s", ($report['ga:timeOnSite']/$report['ga:visits'])); ?></span></p>
            <?php
            }
            else {
                echo '<div class="warning_status">'.__('Não foi possivel obter os dados').'</div>';
            }
            ?>
    	</div>
    	<div id="graph_trafficsource">
        	<div class="graph_loading"></div>
        </div>
    	<div id="graph_visits_browser">
        	<div class="graph_loading"></div>
    	</div>
	</div>
	<div id="container_visits_pageviews">
		<h2><?php echo __('Visitas vs. Exibições de páginas');?></h2>
		<div id="graph_visits_pageviews">
		    <div class="graph_loading"></div>
		</div>
	</div>
	<div id="container_top10_pageviews">
		<h2><?php echo __('10 páginas mais visualizadas');?></h2>
		<div id="top10_pageviews">
    		<div class="graph_loading"></div>
    	</div>
	</div>
	<div id="container_visitors_new_returning">
		<h2><?php echo __('Visitantes novos vs. retornando');?></h2>
		<div id="graph_new_returning">
		    <div class="graph_loading"></div>
		</div>
	</div>
	<div class="clear"></div>
	<div id="details">
		
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){  
	$('#graph_trafficsource').load('stats/traffic_source.php');
	$('#graph_visits_browser').load('stats/browser_usage.php');
	$('#graph_visits_pageviews').load('stats/visits_pageviews.php');
	$('#graph_new_returning').load('stats/visitors_new_returning.php');
	$('#top10_pageviews').load('stats/top10_pageviews.php');
});  
</script>