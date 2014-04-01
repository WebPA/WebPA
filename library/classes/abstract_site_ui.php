<?php
/**
 * 
 * Class UI - Site user interface
 *
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
class Site_UI {
/**
 * Constructor for the site user interface
 */
	function Site_UI() { }// /CONSTRUCTOR ->Site_UI()

/**
 * Function to dictate the header expire
 * @param string $expire_date
 * @param string $modified_date
 */
	function headers_expire($expire_date = null, $modified_date = null) {
		// If no expiry date, expire at 00:00:01 today
		if (!$expire_date) { $expire_date = mktime(0,0,1,date('m'),date('d'),date('Y')); }

		// If no modified date, modified today
		if (!$modified_date) { $modified_date = mktime(); }

		header('Expires: '. gmdate('D, d M Y H:i:s', $expire_date ) .' GMT');
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', $modified_date) .' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');		// HTTP/1.1
		header('Cache-Control: post-check=0, pre-check=0', false);		// HTTP/1.1
		header("Cache-control: private", false);
		header('Pragma: no-cache');		// HTTP/1.0
	} // /-headers_expire()

/**
 * Function to write the header information to screen
 */
  function head () {
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en" xml:lang="en">
<head>
	<meta http-equiv="content-language" content="EN" />
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
  <?php
  } // /->head()

/**
 * Function to close the header and open the body area of the screen
 * @param string $extra_attributes
 */
	function body($extra_attributes = '') {
		echo("\n</head>\n<body $extra_attributes>\n\n");
	} // /->body()

}

?>
