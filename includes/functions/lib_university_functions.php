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
function get_academic_year($date = null) {
  if (is_null($date)) {
    $date = mktime();
  }
  $year = (int) date('Y',$date);
  $month = (int) date('n',$date);
  if ($month < APP__ACADEMIC_YEAR_START_MONTH) {
    $year = $year - 1;
  }

  return $year;

}// /get_academic_year()

function dateToYear($date) {

  $year = (int) date('Y',$date);
  $month = (int) date('n',$date);
  if ($month < APP__ACADEMIC_YEAR_START_MONTH) {
    $year = $year - 1;
  }

  return $year;

}

?>