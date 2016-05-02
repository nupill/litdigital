<?php
 
require_once(dirname(__FILE__) . "/../../config/general.php");
//$locale = LANG;
$locale = getDefaultLanguage();
$textdomain = "language";
$locales_dir = dirname(__FILE__) . '/../../language/i18n';

if(isset($_COOKIE['locale'])){
	$locale = $_COOKIE['locale'];
}

if(isset($_GET['locale']) && !empty($_GET['locale'])){
	if(isset($_COOKIE['locale'])){
		$locale = $_GET['locale'];
		setcookie('locale',$locale,0,'/');
	}else{
		$locale = $_GET['locale'];
		setcookie('locale',$locale,0,'/');
	}
	
}

putenv('LANGUAGE=' . $locale);
putenv('LANG=' . $locale);
putenv('LC_ALL=' . $locale);
putenv('LC_MESSAGES=' . $locale);
 
require_once('gettext.inc');
 
_setlocale(LC_ALL, $locale);
_setlocale(LC_CTYPE, $locale);
 
_bindtextdomain($textdomain, $locales_dir);
_bind_textdomain_codeset($textdomain, 'UTF-8');
_textdomain($textdomain);
 
function _e($string) {
  echo __($string);
}

function getDefaultLanguage() {
   if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
      return parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
   else
      return parseDefaultLanguage(NULL);
   }

function parseDefaultLanguage($http_accept, $deflang = "en") {
   if(isset($http_accept) && strlen($http_accept) > 1)  {
      # Split possible languages into array
      $x = explode(",",$http_accept);
      foreach ($x as $val) {
         #check for q-value and create associative array. No q-value means 1 by rule
         if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$val,$matches))
            $lang[$matches[1]] = (float)$matches[2];
         else
            $lang[$val] = 1.0;
      }

      #return default language (highest q-value)
      $qval = 0.0;
      foreach ($lang as $key => $value) {
         if ($value > $qval) {
            $qval = (float)$value;
            $deflang = $key;
         }
      }
   }
   return strtolower($deflang);
}
?>