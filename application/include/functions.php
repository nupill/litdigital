<?php
/**
 * Handles the exception details according to the log versbosity settings
 * 
 * @param $exception
 */
function handle_exception($exception) {
    global $config;
    if ($config['log_verbosity'] == LOG_VERBOSITY_DEBUG) {
        print '<b>Error:</b> ' . $exception->getMessage();
        $trace = $exception->getTrace();
        if ($trace) {
            $trace = $trace[0];
            print '<br /><b>File:</b> ' . $exception->getFile();
            if (isset($trace['file']) && isset($trace['line'])) {
                print '<br /><b>Called on:</b> ' . $trace['file'] . ' (Line ' . $trace['line'] . ')';
            }
            if (isset($trace['class'])) {
                print '<br /><b>Class:</b> ' . $trace['class'];
            }
            if (isset($trace['function'])) {
                print '<br /><b>Function:</b> ' . $trace['function'];
            }
            if (isset($trace['args'])) {
                print '<br /><b>Arguments:</b> ' . print_r($trace['args'], true);
            }
        }
        print '<br /><b>Stack trace:</b> ' . $exception->getTraceAsString();
    }
    else {
        error_redirect();
    }
    if ($config['log_enabled']) {
        Logger::log($exception->getMessage(), $exception->getFile());
    }
}

/**
 * Generates an exception from an error
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 */
function handle_error($errno, $errstr, $errfile, $errline) {
    throw new Exception($errstr, $errno);
}

/**
 * Sends the output buffer and turn on output buffering again
 */
function flush_buffers() {
    ob_end_flush();
    ob_flush();
    flush();
    ob_start(); 
}

/**
 * Redirects the user to a custom error page
 */
function error_redirect() {
    //header("HTTP/1.0 500 Internal Server Error");
    if (!headers_sent()) {
        header('Status: 500');
        //header("Location: /index.php");
        ob_clean();
        require_once(dirname(__FILE__) . '/TemplateHandler.php');
        $template = new TemplateHandler();
        try {
            $template->set_body_file(DEFAULT_ERROR_FILE);
            $template->set_title('nKey - Erro no servidor');
            $template->show();
        }
        catch (Exception $e) {
            print 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
        }
    }
    else {
        print 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
    }
}

/** 
 * Cut string to n symbols and add delim but do not break words. 
 * 
 * @param $string - String we are operating with 
 * @param $limit - Character count to cut to 
 * @param $break - Where to stop truncating. Default: '.' 
 * @param $pad - Delimiter. Default: '...' 
 **/ 
function text_trim($string, $limit, $break = '.', $pad = '. (...)') { 
    // return with no change if string is shorter than $limit  
    if (strlen($string) <= $limit) {
        return $string;   
    } 
    // is $break present between $limit and the end of the string? 
    if (($breakpoint = strpos($string, $break, $limit)) !== false) { 
        if ($breakpoint < strlen($string) - 1) {
            $string = substr($string, 0, $breakpoint) . $pad; 
        } 
    }
    return restore_tags($string); 
}

/**
 * Attempts to close any tags that have been left open in a HTML string.
 * Please Note: This function will restore only HTML tags, opened and closed in a valid order, with no attributes
 * 
 * @param $input - HMTL String
 */
function restore_tags($input) {
    $opened = array();
    // loop through opened and closed tags in order
    if (preg_match_all("/<(\/?[a-z]+)>?/i", $input, $matches)) {
        foreach ($matches[1] as $tag) {
            if (preg_match("/^[a-z]+$/i", $tag, $regs)) {
                // a tag has been opened
                if (strtolower($regs[0]) != 'br') {
                    $opened[] = $regs[0];
                }
            }
            elseif (preg_match("/^\/([a-z]+)$/i", $tag, $regs)) {
                // a tag has been closed
                $keys = array_keys($opened, $regs[1]);
                unset($opened[array_pop($keys)]);
            }
        }
    }
    // close tags that are still open
    if ($opened) {
        $tagstoclose = array_reverse($opened);
        foreach ($tagstoclose as $tag) {
            $input .= "</$tag>";
        }
    }
    return $input;
}


/**
 * Replaces double (") and sigle (') quotes from a string to their HTML symbol
 * 
 * @param $str - Input string
 * @param $$to_html - Replace to or from HTML
 */
function replace_quotes($str, $to_html = true) {
    if ($to_html) {
        $str = str_replace('"', '&quot;', $str);
        $str = str_replace("'", '&#39;', $str);
    }
    else {
        $str = str_replace('&quot;', '"', $str);
        $str = str_replace('&#39;', "'", $str);
    }
    return $str;    
}

/**
 * Checks if the date is valid
 * 
 * @param $date - The date in format DD-MM-YYYY
 */
function validate_date($date) {
    //match the format of the date
    if (preg_match ("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $date, $parts)) {
        //check if the date is valid of not
        if (checkdate($parts[2],$parts[1],$parts[3])) {
            return true;
        }
    }
    return false;
}

/**
 * Checks if the time is valid
 * 
 * @param $time - The date in format HH:MM or HH:MM:SS
 */
function validate_time($time) {
    if (preg_match ("/^([0-1]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/", $time)) {
        return true;
    }
    return false;
}


/**
 * Validate an email address.
 * Returns true if the email address has the email address format and the domain exists.
 * 
 * @param $email - Email address (raw input)
 */
function validate_email($email) {
   $isValid = true;
   $atIndex = strrpos($email, '@');
   if (is_bool($atIndex) && !$atIndex) {
      $isValid = false;
   } else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
         // local part length exceeded
         $isValid = false;
      } else if ($domainLen < 1 || $domainLen > 255) {
         // domain part length exceeded
         $isValid = false;
      } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         // local part starts or ends with '.'
         $isValid = false;
      } else if (preg_match('/\\.\\./', $local)) {
         // local part has two consecutive dots
         $isValid = false;
      } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         // character not valid in domain part
         $isValid = false;
      } else if (preg_match('/\\.\\./', $domain)) {
         // domain part has two consecutive dots
         $isValid = false;
      } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'))) {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

/**
 * Detects if the user browser is IE
 */
function is_internet_explorer() {
    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Forces the browser to clear the cache
 */
function clear_browser_cache() {
    header("Pragma: no-cache");
    header("Cache: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
}

/**
 * Generates a pagination controller
 * 
 * @param $page - Current page
 * @param $totalitems - Number of items to paginate
 * @param $limit - Items per page
 * @param $adjacents - 
 * @param $file - URL to be called by the javascript function
 */
function create_pagination($page = 1, $totalitems, $limit = 4, $adjacents = 1, $file = '') {        
    //defaults
    if (!$adjacents) $adjacents = 1;
    if (!$limit) $limit = 15;
    if (!$page) $page = 1;
    
    //other vars
    $prev = $page - 1;                                  //previous page is page - 1
    $next = $page + 1;                                  //next page is page + 1
    $lastpage = ceil($totalitems / $limit);             //lastpage is = total items / items per page, rounded up.
    $lpm1 = $lastpage - 1;                              //last page minus 1
    
    /* 
        Now we apply our rules and draw the pagination object. 
        We're actually saving the code to a variable in case we want to draw it more than once.
    */
    $pagination = "";
    if ($lastpage > 1) {    
        $pagination .=  "<div class=\"pagination-wrapper\">";
        $pagination .=  "<ul class=\"pagination\">";

        //previous button
        if ($page > 1) 
            $pagination .= "<li class=\"previous\"><a href=\"$file?page=$prev\">«</a></li>";
        else
            $pagination .= "<li class=\"previous-off\">«</li>";
        
        //pages 
        if ($lastpage < 7 + ($adjacents * 2)) {     //not enough pages to bother breaking it up
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page)
                    $pagination .= "<li class=\"active_page\">$counter</li>";
                else
                    $pagination .= "<li><a href=\"$file?page=$counter\">$counter</a></li>";         
            }
        }
        elseif ($lastpage >= 7 + ($adjacents * 2)) {    //enough pages to hide some
            //close to beginning; only hide later pages
            if ($page < 1 + ($adjacents * 3)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $page)
                        $pagination .= "<li class=\"active_page\">$counter</li>";
                    else
                        $pagination .= "<li><a href=\"$file?page=$counter\">$counter</a></li>";
                }
                $pagination .= "...";
                $pagination .= "<li><a href=\"$file?page=$lpm1\">$lpm1</a></li>";
                $pagination .= "<li><a href=\"$file?page=$lastpage\">$lastpage</a></li>";
            }
            //in middle; hide some front and some back
            elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                $pagination .= "<li><a href=\"$file?page=1\">1</a></li>";
                $pagination .= "<li><a href=\"$file?page=2\">2</a></li>";
                $pagination .= "...";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                    if ($counter == $page)
                        $pagination .= "<li class=\"active_page\">$counter</li>";
                    else
                        $pagination .= "<li><a href=\"$file?page=$counter\">$counter</a></li>";             
                }
                $pagination .= "<li><a href=\"$file?page=$lpm1\">$lpm1</a></li>";
                $pagination .= "<li><a href=\"$file?page=$lastpage\">$lastpage</a></li>";       
            }
            //close to end; only hide early pages
            else {
                $pagination .= "<li><a href=\"$file?page=1\">1</a></li>";
                $pagination .= "<li><a href=\"$file?page=2\">2</a></li>";
                $pagination .= "...";
                for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination .= "<li class=\"active_page\">$counter</li>";
                    else
                        $pagination .= "<li><a href=\"$file?page=$counter\">$counter</a></li>";             
                }
            }
        }
        
        //next button
        if ($page < $counter - 1) 
            $pagination .= "<li class=\"next\"><a href=\"$file?page=$next\">»</a></li>";
        else
            $pagination .= "<li class=\"next-off\">»</li>";
        $pagination .= "</ul></div>\n";
    }
    return $pagination;
}

/**
 * Creates a thumbnail of a image
 * 
 * @param $file - Path to the original image file
 * @param $width - Width in pixels
 * @param $output - Whether output the image or save it
 * @param $targetPath - Path+Filename to save the thumbnail
 */
function create_thumbnail($file, $width = 100, $output = false, $targetPath = '') {
    $imgSize = getimagesize($file);
    $fileParts = pathinfo($file);

    switch(strtolower($fileParts['extension'])){
        case 'jpg':
        case 'jpeg':
           $image = imagecreatefromjpeg($file);    
           break;
        case 'png':
           $image = imagecreatefrompng($file);
           break;
        case 'gif':
           $image = imagecreatefromgif($file);
           break;
        default:
            return false;
    }
    
    $height = $imgSize[1]/$imgSize[0]*$width; //This maintains proportions
    
    $src_w = $imgSize[0];
    $src_h = $imgSize[1];
    
    $picture = imagecreatetruecolor($width, $height);
    imagealphablending($picture, false);
    imagesavealpha($picture, true);

    $bool = imagecopyresampled($picture, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h); 
    
    if ($bool) {
        if (!$targetPath) {
            $targetPath = $fileParts['dirname'] . '/' . $fileParts['filename'] . '_thumb.' . $fileParts['extension'];
        }
        switch(strtolower($fileParts['extension'])){
            case 'jpg':
            case 'jpeg':
                if (!headers_sent() && $output) {
                   header('Content-Type: image/jpeg');
                   imagejpeg($picture);
                }
                else {
                   imagejpeg($picture, $targetPath, 80);
                }
                break;
            case 'png':
                if (!headers_sent() && $output) {
                   header('Content-Type: image/png');
                   imagepng($picture);
                }
                else {
                   imagepng($picture, $targetPath);
                }
                break;
            case 'gif':
                if (!headers_sent() && $output) {
                   header('Content-Type: image/gif');
                   imagegif($picture);
                }
                else {
                   imagegif($picture, $targetPath);
                }
                break;
        }
    } else {
        return false;
    }
    imagedestroy($picture);
    imagedestroy($image);
    return $targetPath;
}

/**
 * Retrieve the mime type of a file according to Apache mime.types
 * 
 * @param string $filename - Name of the file to detect the mime type
 * @param string $mimePath - Path to mime.types file
 */
function get_mime_type($filename, $mimePath = '/etc') {
   $fileext = substr(strrchr($filename, '.'), 1);
   if (empty($fileext)) return (false);
   $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
   $lines = @file("$mimePath/mime.types");
   foreach($lines as $line) {
      if (substr($line, 0, 1) == '#') continue; // skip comments
      $line = rtrim($line) . " ";
      if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
      return ($matches[1]);
   }
   return (false); // no match at all
}

/**
 * Generate a hash from a file
 * 
 * @param $filename - Path (location) of the file
 */
function file_hash($filename) {
    $readsize = 16 * 1024; //16Kb
    if ($filename && $handle = @fopen($filename, 'r')) {
        $contents = fread($handle, $readsize); //Reads 16kb from the beginning of the file
        fseek($handle, -$readsize, SEEK_END); //Sets the position to 16kb before the end-of-file
        $contents.= fread($handle, $readsize); //Reads the last 16kb from the file
        fclose($handle);
        return md5($contents);
    }
    return false;
}

/**
 * Delete a file or recursively delete a directory
 *
 * @param string $str - Path to file or directory
 * @param boolean $remDir - Remove the directories or only the files
 */
function recursiveDelete($str, $remDir = true){
    if (is_file($str)){
        return @unlink($str);
    }
    elseif (is_dir($str)){
        $scan = glob(rtrim($str,'/').'/*');
        foreach ($scan as $index=>$path){
            recursiveDelete($path);
        }
        if ($remDir) {
            return @rmdir($str);
        }
        else {
            return true;
        }
    }
}

/**
 * Replace special characters from a filename
 * 
 * @param string $filename - Input filename
 */
function clean_filename($filename) {
    $filename = strtolower($filename);
    $ext_point = strripos($filename, ".");
    if ($ext_point === false) {
        return false;
    }
    $ext = substr($filename, $ext_point, strlen($filename));
    $filename = substr($filename, 0, $ext_point);
    $filename = preg_replace('/\s/', '_', $filename);
    return clean_string($filename).$ext;
}

/**
 * Replace or remove special characters from a string and convert them to lowercase
 * 
 * @param $string - Input string
 */
function clean_string($string) {
    $string = trim(strtolower(normalize($string)));
    return preg_replace('/[^\sa-z0-9-_]/', '', $string);
}

/**
 * Converts a ISO Date format to DD Month
 * 
 * @param $date - Input date (YYYY-MM-DD [HH:MM:SS])
 */
function clean_date($date) {
    $date = explode(' ', $date);
    $date = $date[0];
    $date = explode('-', $date);
    $month = '';
    switch ($date[1]) {
        case "01": $month = "Jan"; break;
        case "02": $month = "Fev"; break;
        case "03": $month = "Mar"; break;
        case "04": $month = "Abr"; break;
        case "05": $month = "Mai"; break;
        case "06": $month = "Jun"; break;
        case "07": $month = "Jul"; break;
        case "08": $month = "Ago"; break;
        case "09": $month = "Set"; break;
        case "10": $month = "Out"; break;
        case "11": $month = "Nov"; break;
        case "12": $month = "Dez"; break;
    }
    $day = $date[2];
    return $day . ' ' . $month;
}

/**
 * Replace not usual characters of a string
 * 
 * @param $string - Input string
 */
function normalize($string) {
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
    );
    return strtr($string, $table);
}

/**
 * Generate SEO friendly URLs
 * 
 * @param $str - String to be cleaned/normalized
 */
function generate_url($str) {
    $str = str_replace(' ', '-', $str);
    return urlencode(clean_string($str));
}

/**
 * Formats the filesize from a number
 * @param $size - Size in bytes
 */
function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

/**
 * Gets the file extension
 * 
 * @param $filename - The filename / filepath
 */
function file_extension($filename) {
    $path_info = pathinfo($filename);
    return $path_info['extension'];
}

/**
 * Gets the description of an file upload error
 * 
 * @see http://www.php.net/manual/en/features.file-upload.errors.php
 * @param $error_code - Code from $_FILES['file']['error']
 */
function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'O tamanho do arquivo excedeu o limite do servidor';
        case UPLOAD_ERR_FORM_SIZE:
            return 'O tamanho do arquivo excedeu o limite';
        case UPLOAD_ERR_PARTIAL:
            return 'O arquivo foi parcialmente enviado';
        case UPLOAD_ERR_NO_FILE:
            return 'Nenhum arquivo foi enviado';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Diretório temporário não encontrado';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Erro ao salvar o arquivo no servidor';
        case UPLOAD_ERR_EXTENSION:
            return 'Uma extensão do PHP previniu o arquivo de ser salvo no servidor';
        default:
            return 'Erro de envio de arquivo desconhecido';
    }
} 

/**
 * Compares array1 against array2 and returns the difference. 
 * This function handles multi-dimensional arrays or objects through serialization
 * 
 * @param $ar1 - The array to compare from 
 * @param $ar2 - An array to compare against
 */

function array_diff_no_cast(&$ar1, &$ar2) {
   $diff = Array();
   foreach ($ar1 as $key => $val1) {
      if (array_search($val1, $ar2) === false) {
         $diff[$key] = $val1;
      }
   }
   return $diff;
}

/**
 * Generate an alphanumeric random string
 * 
 * @param $length - The length of the result string
 * @return $result - The random string
 */
function generate_random_string($length = 10) {
    $valid_characters = "abcdefghijklmnopqrstuxyvwz123456789";
    $valid_char_number = strlen($valid_characters);
    $result = "";
    for ($i=0; $i<$length; $i++) {
        $index = mt_rand(0, $valid_char_number - 1);
        $result .= $valid_characters[$index];
    }
    return $result;
}

/**
 * Checks if the argument is null or empty
 * 
 * @param $value - The input value
 */
function is_empty($value) {
	if (is_numeric($value))
		return false;
    return ((is_null($value) || $value == '') && $value !== false);
}

/**
 * Checks if the argument is not null or empty
 * 
 * @param $value - The input value
 */
function is_not_empty($value) {
    return !is_empty($value);
}

/**
 * Removes extra spaces from a string
 * 
 * @param $input - The input string
 */
function remove_extra_spaces($input) {
    return preg_replace('/\s+/', ' ', trim($input));
}

/**
 * Removes portuguese common words from a string
 * 
 * @param $input - The input string
 */
function remove_stop_words($input) {
	/*
	$commonWords = array('a', 'as', 'à', 'às', 'o', 'os', 'ao', 'aos', 'da', 'de',
						 'do', 'das', 'dos', 'para', 'em', 'e', 'é', 'um', 'com');
	*/
	
    $commonWords = array('a', 'as', 'à', 'À', 'às', 'Às', 'o', 'os', 'ao', 'aos', 'da', 'de', 
    					 'do', 'das', 'dos', 'para', 'em', 'e', 'é', 'É', 'um', 'com');
   
    /*
     * This line of code below doesn't work properly: i.e. Durão returns Durã
     * $output = preg_replace('/\b('.implode('|',$commonWords).')\b/ui', '', $input);
     */
    
    $output = explode(' ', $input);
    
    foreach ($output as $key => $word) {
    	if (in_array(strtolower($word), $commonWords)) {
    		$output[$key] = '';
    	}
    }
    
    $output = implode(' ', $output);
    $output = remove_extra_spaces($output);
    return $output;
}

/**
 * Makes an valid html request url by parsing the params array
 * @param $params The parameters to be converted into URL with key as name.
 */
function make_request_url($params) {
    $querystring = null;
    foreach ($params as $name => $value) {
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        $querystring = $name.'='.urlencode($value).'&'.$querystring;
    }
    // Cut the last '&'
    $querystring = substr($querystring,0,strlen($querystring)-1);
    return $querystring;
}

/**
 * Gets the first year in a century
 * @param $roman_century - Century in roman numeral format (example: XIX)
 */
function get_first_century_year($roman_century) {
    $centuries = array(0 => 'I', 1 => 'II', 2 => 'III', 3 => 'IV', 4 => 'V', 5 => 'VI',
                       6 => 'VII', 7 => 'VIII', 8 => 'IX', 9 => 'X', 10 => 'XI',
                       11 => 'XII', 12 => 'XIII', 13 => 'XIV', 14 => 'XV', 15 => 'XVI',
                       16 => 'XVII', 17 => 'XVIII', 18 => 'XIX', 19 => 'XX', 20 => 'XXI',
                       21 => 'XXII', 22 => 'XXIII');

    while (strcmp($roman_century, current($centuries)) != 0) {
        next($centuries);
    }

    $year = key($centuries);
    $year = ($year*100)+1;

    return $year;
}

/**
 * Gets the century of a year, in roman numeral format (example: 2005 returns XXI)
 * @param $year - The year
 */
function get_roman_century($year) {
    $centuries = array(0 => 'I', 1 => 'II', 2 => 'III', 3 => 'IV', 4 => 'V', 5 => 'VI',
                       6 => 'VII', 7 => 'VIII', 8 => 'IX', 9 => 'X', 10 => 'XI',
                       11 => 'XII', 12 => 'XIII', 13 => 'XIV', 14 => 'XV', 15 => 'XVI',
                       16 => 'XVII', 17 => 'XVIII', 18 => 'XIX', 19 => 'XX', 20 => 'XXI',
                       21 => 'XXII', 22 => 'XXIII');

    if ($year > 1) {
        $year = $year-1;
    }

    $year = intval($year/100);

    while (key($centuries) != $year) {
        next($centuries);
    }

    return current($centuries);
}

/**
 * Gets the centuries in roman numeral format of a interval of centuries
 * Example: get_roman_centuries('XVIII', 'XXI') returns XVIII, XIX, XX and XXI
 * @param $century_begin - The first century
 * @param $century_end - The last century
 */
function get_roman_centuries($century_begin, $century_end) {
    $centuries = array(0 => 'I', 1 => 'II', 2 => 'III', 3 => 'IV', 4 => 'V', 5 => 'VI',
                       6 => 'VII', 7 => 'VIII', 8 => 'IX', 9 => 'X', 10 => 'XI',
                       11 => 'XII', 12 => 'XIII', 13 => 'XIV', 14 => 'XV', 15 => 'XVI',
                       16 => 'XVII', 17 => 'XVIII', 18 => 'XIX', 19 => 'XX', 20 => 'XXI',
                       21 => 'XXII', 22 => 'XXIII');

    $index_begin = array_search($century_begin, $centuries);
    $index_end = array_search($century_end, $centuries);

    return array_slice($centuries, $index_begin, ($index_end-$index_begin+1));
}

/**
 * Checks if the parameter is a valid roman numeral format of a century
 * @param $year - The year
 */
function is_roman_century($century) {
    $centuries = array(0 => 'I', 1 => 'II', 2 => 'III', 3 => 'IV', 4 => 'V', 5 => 'VI',
                       6 => 'VII', 7 => 'VIII', 8 => 'IX', 9 => 'X', 10 => 'XI',
                       11 => 'XII', 12 => 'XIII', 13 => 'XIV', 14 => 'XV', 15 => 'XVI',
                       16 => 'XVII', 17 => 'XVIII', 18 => 'XIX', 19 => 'XX', 20 => 'XXI',
                       21 => 'XXII', 22 => 'XXIII');

    return in_array($century, $centuries);
}

/**
 * Gets the complete centuries in roman numeral format from a interval of years
 * Example: get_complete_roman_centuries(1650, 2010) returns XVIII, XIX and XX 
 * @param $year_begin - The year
 * @param $year_end - The year
 */
function get_complete_roman_centuries($year_begin, $year_end) {
	$century_begin = get_roman_century($year_begin);
	$century_end = get_roman_century($year_end);
	$centuries = get_roman_centuries($century_begin, $century_end);
	if ($centuries) {
		if (substr($year_begin, 2) != "01") {
			array_shift($centuries);
		}
		if (substr($year_end, 2) != "00") {
			array_pop($centuries);
		}
	}
	return $centuries; 
}
