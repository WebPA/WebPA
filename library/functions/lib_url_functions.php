<?php
/**
 * 
 * URL functions
 *
 * 			 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
 
 
/**
 * Open an HTTP session to the target URL and 'GET' the page
 * If given a username + password, opens a basic authentication session to the target
 * 
 * @param string $url
 * @param int $timeout
 * @param string $username
 * @param string $password
 * 
 * @return bool
*/
function fetch_url($url, $timeout = 5, $username = null, $password = null) {
	$url_bits = parse_url($url);

	$port = (isset($url_bits['port'])) ? $url_bits['port'] : 80 ;
	
	$handle = fsockopen($url_bits['host'], $port, $err_num, $err_string, $timeout);

	if ($handle) {
		$path = (isset($url_bits['path'])) ? $url_bits['path'] : '/' ;

		// start HTTP header
		$header = '';
		$header .= "GET $path HTTP/1.0\r\n";
		$header .= "Host: {$url_bits['host']}\r\n";
		

		// If username given.. attempt login using username/password
		if ($username) {
			$header .= 'Authorization: Basic '. base64_encode("$username:$password") . "\r\n";
		}

		$header .= "Connection: close\r\n\r\n";
		fputs($handle,$header);
		
		// Get contents of page
		$contents = '';
		while (!feof($handle)) {
  		$contents .= fread($handle, 8192);
		}
		fclose($handle);
		return $contents;
	} else {
		return false;
	}
}


/** 
 * Get the params as part of the URL
 * 
 * params must be in URI of the form:
 * www.someweb.com/param[0]/param[1]/param[2]/index.html				(params[0-2])
 * www.someweb.com/param[0]/param[1]/param[2]/param[3].html		(params[0-3])
 * 
 * any empty param 'param[1]//param[3]' is ignored
 * any param starting 'index.html' is ignored
 * any text after and including '.html' in a string is removed
 * any text after and including '?' in a string is removed
 * 
 * @return string
*/
function fetch_uri_params() {
	$params = array();
  $params_count = 0;
  
  $temp = explode("/",substr($_SERVER["REQUEST_URI"],1));
  $temp_count = count($temp);
  for ($i=0; $i<$temp_count; ++$i) {
   	$use_param = true;
  	if ( (empty($temp[$i])) || (strpos($temp[$i],'index.html')===0) ) { $use_param = false; }
		else {
			$cut_to = strpos($temp[$i],'.html');
			if ($cut_to) { $temp[$i] = substr($temp[$i],0, $cut_to); }

			$cut_to = strpos($temp[$i],'?');
			if ($cut_to) { $temp[$i] = substr($temp[$i],0, $cut_to); }
		}
    if ($use_param) { $params[] = strtolower($temp[$i]); }
  }
	return $params;
}


?>
