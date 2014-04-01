<?php
/**
 * 
 * University Functions
 *  
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */


/**
 *  Get the academic year for a given date
 * 
 * @param int $date (optional) datetime to check. (default: current date/time)
 * @param int $start_month (optional) month-component of date the academic year starts in a year (default: 9)
 * @param int $start_day (optional) day-component of date the academic year starts in a year (default: 1)
 * 
 * @return int academic year (format: YYYY)
*/
function get_academic_year($date = null, $start_month = 9, $start_day = 1 ) {
	if (is_null($date)) { $date = mktime(); }
	$year = (int) date('Y',$date);
	$academic_start_date = mktime(0,0,0, $start_month, $start_day, $year );	// the start date for academic year in the given year
 	
	return (int) ( ($date>=$academic_start_date) ? $year : $year-1 );
}// /get_academic_year()


?>