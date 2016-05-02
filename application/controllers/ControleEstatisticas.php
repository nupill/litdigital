<?php
require_once(dirname(__FILE__) . '/../include/DB.php');
require_once(dirname(__FILE__) . '/../include/googleanalytics.class.php');
require_once(dirname(__FILE__) . '/../include/Logger.php');
require_once(dirname(__FILE__) . '/../include/Auth.php');
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');

//Data Feed Query Explorer: http://code.google.com/apis/analytics/docs/gdata/gdataExplorer.html

class ControleEstatisticas {
	
	protected $DB;
	protected $ga;
	private static $instance;
	
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
	    global $config;
		$this->DB = DB::getInstance();
		$this->DB->connect();
    	try {
        	//Reports start date (one month ago)
        	$date_start = date('Y-m-d', mktime(0, 0, 0, date('m')-1, date('d'), date('Y')));
        	//Resports end date (today)
            $date_end = date('Y-m-d');
        	
            //Create an instance of the GoogleAnalytics class using your own Google {email} and {password}
            $this->ga = new GoogleAnalytics($config['ga_email'], $config['ga_password']);
               
            //Set the Google Analytics profile you want to access - format is 'ga:123456';
            $this->ga->setProfile($config['ga_profile']);
            
            //Set the date range we want for the report - format is YYYY-MM-DD
            $this->ga->setDateRange($date_start, $date_end);
        }
        catch (Exception $e) { 
            Logger::log($e->getMessage(), __FILE__);
            throw new Exception($e->getMessage());
        }
	}
	
	private function __clone() { }
	
	public function get_site_usage() {
	     try {
            $report = $this->ga->getReport(array('metrics'=>urlencode('ga:visits,ga:newVisits,ga:pageviews,ga:timeOnSite,ga:visitors')));
            $report = $report[''];
            return $report;
        } catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__);
            return false;
        }
	}
	
	public function get_traffic_source() {
	   try {
            $report = $this->ga->getReport(array('dimensions'=>urlencode('ga:source'),
                                           	     'metrics'=>urlencode('ga:visits'),
                								 'sort'=>urlencode('-ga:visits')));
            
            $trafficsource_results = '';
            $total_visits = 0;
            $total_visits_results = 0;
            $count = 0;
            
            foreach ($report as $results) {
                $total_visits+= $results['ga:visits'];
            }
            
            foreach ($report as $source=>$results) {
                if ($count > 4) {
                    break;
                }
                $total_visits_results+= $results['ga:visits'];
                if ($source == '(direct)') {
                    $source = '(direta)';
                }
                $trafficsource_results.= "['" . $source . "'," . round(($results['ga:visits']/$total_visits)*100, 2) . '],';
                $count++;
            }
            
            if ($total_visits_results != $total_visits) {
                $trafficsource_results.= "['(outras)'," . round(($total_visits_results/$total_visits)*100, 2) . '],';
            }
            
            $trafficsource_results = substr($trafficsource_results, 0, -1);
            return $trafficsource_results;
                
        } catch (Exception $e) {
            Logger::log($e->getMessage(), __FILE__); 
            return false;
        }
	}
	
	public function get_visits_by_browser() {
	   try {
            $report = $this->ga->getReport(array('dimensions'=>urlencode('ga:browser,ga:operatingSystem'),
                                           		 'metrics'=>urlencode('ga:visits'),
            									  'sort'=>urlencode('-ga:visits')));
            
            $browser_results = '';
            $total_visits = 0;
            $total_visits_results = 0;
            $count = 0;
            
            foreach ($report as $results) {
                $total_visits+= $results['ga:visits'];
            }
            
            foreach ($report as $browser=>$results) {
                if ($count > 4) {
                    break;
                }
                $total_visits_results+= $results['ga:visits'];
                
                $browser = explode('~~', $browser);
                $browser = $browser[0] . ' / ' . $browser[1];
                $browser_results.= "['" . $browser . "'," . round(($results['ga:visits']/$total_visits)*100, 2) . '],';
                $count++;
            }
            
	        if ($total_visits_results != $total_visits) {
	            $browser_results.= "['(outros)'," . round(($results['ga:visits']/$total_visits)*100, 2) . '],';
            }
            
            $browser_results = substr($browser_results, 0, -1);
            return $browser_results;
        } catch (Exception $e) { 
            Logger::log($e->getMessage(), __FILE__); 
            return false;
        }
	}
	
	public function get_visits_pageviews() {
	   try {
            $report = $this->ga->getReport(array('dimensions'=>urlencode('ga:date'),
                                                 'metrics'=>urlencode('ga:visits,ga:pageviews')));
            
            $visits = '';
            $pageviews = '';
            foreach ($report as $date=>$results) {
                $visits.= $results['ga:visits'] . ',';
                $pageviews.= $results['ga:pageviews'] . ',';
            }
            $visits = substr($visits, 0, -1);
            $pageviews = substr($pageviews, 0, -1);
            return array('visits'=>$visits, 'pageviews'=>$pageviews);
        } catch (Exception $e) { 
            Logger::log($e->getMessage(), __FILE__); 
            return false;
        }
	}
	
	public function get_top10_pageviews() {
	    try {
            $report = $this->ga->getReport(array('dimensions'=>urlencode('ga:pagePath'),
                                                 'metrics'=>urlencode('ga:pageviews'),
                                                 'sort'=>urlencode('-ga:pageviews'),
                                                 'max-results'=>urlencode('10')));
            $table_output = '';
            $table_output.= '<table id="top10_table">';
            $table_output.= '<thead>';
            $table_output.= '<tr>';
            $table_output.= '<th>Página</th>';
            $table_output.= '<th>Visualizações</th>';
            $table_output.= '</tr>';
            $table_output.= '</thead>';
            $table_output.= '<tbody>';
            
            foreach ($report as $pagepath=>$results) {
                $table_output.= '<tr>';
                $table_output.= '<td>' . substr($pagepath, 0, 50) . '</td>';
                $table_output.= "<td>{$results['ga:pageviews']}</td>";
                $table_output.= '</tr>';
            }
            
            $table_output.= '</tbody>';
            $table_output.= '</table>';
            return $table_output;
        } catch (Exception $e) { 
            Logger::log($e->getMessage(), __FILE__); 
            return false;
        }
	}
	
	public function get_visitors_new_vs_returning() {
	   try {
            $report = $this->ga->getReport(array('dimensions'=>urlencode('ga:visitorType'),
                                                 'metrics'=>urlencode('ga:visits')));
            
            $visits_new = $report['New Visitor']['ga:visits'];
            $visits_returning = $report['Returning Visitor']['ga:visits'];
            $total_visits = $visits_new + $visits_returning;
            $visits_new_returning = round(($visits_new/$total_visits)*100,2) . ',' . round(($visits_returning/$total_visits)*100,2);
            return $visits_new_returning;
        } catch (Exception $e) { 
            Logger::log($e->getMessage(), __FILE__); 
            return false;
        }
	}
}