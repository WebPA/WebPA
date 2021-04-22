<?php
/**
 * University Functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\functions;

class AcademicYear
{
    /**
    *  Get the academic year for a given date
    *
    * @param int $date (optional) datetime to check. (default: current date/time)
    * @param int $start_month (optional) month-component of date the academic year starts in a year (default: 9)
    * @param int $start_day (optional) day-component of date the academic year starts in a year (default: 1)
    *
    * @return int academic year (format: YYYY)
    */
    public static function get_academic_year($date = null)
    {
        if (is_null($date)) {
            $date = time();
        }
        $year = (int) date('Y', $date);
        $month = (int) date('n', $date);
        if ($month < APP__ACADEMIC_YEAR_START_MONTH) {
            $year = $year - 1;
        }

        return $year;
    }

    // /get_academic_year()

    public static function dateToYear($date)
    {
        $year = (int) date('Y', $date);
        $month = (int) date('n', $date);
        if ($month < APP__ACADEMIC_YEAR_START_MONTH) {
            $year = $year - 1;
        }

        return $year;
    }
}
