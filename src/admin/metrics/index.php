<?php
/**
 * landing page for the metrics section of the admin area
 *
 * From here after selecting the type of information that the user wants from
 * the metrics choices they will be shown their choices.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../includes/inc_global.php';

use WebPA\includes\functions\AcademicYear;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

$intro_text = '<p>This page allows you to build a report on commonly requested metrics about the WebPA systems usage.</p>';
$task_text = '<p>Please tick all the boxes for the information that you would like to have in your report. Then click the generate button to view the report.</p>';

//set the page information
$UI->page_title = APP__NAME . ' metrics of use';
$UI->menu_selected = 'metrics';
$UI->breadcrumbs = ['home' => '../../'];
$UI->help_link = '?q=node/237';
$UI->head();
?>
<style type="text/css">
<!--
  div.report { margin-bottom: 16px; padding: 4px; background: #c9ffd6 url(../../images/backgrounds/gradient_light_green-white_l-r.png) repeat-y right; border: 1px solid #ccc; border-right: 0px; }
-->
</style>
<?php
$years = $CIS->get_user_academic_years();
$todays_year = AcademicYear::get_academic_year();
$year = (int) Common::fetch_SESSION('year', $todays_year);

$UI->body();
$UI->content_start();

echo $intro_text;
echo '<div class="content_box">';
echo $task_text;

//write a list of the elements that can be selected for the report

?>

  <form action="report.php" method="post" name="SelectReports">
  <p>
  <label for="academic_year">Academic year to report on</label>
  <select name="academic_year" id="academic_year">
<?php
for ($i = $years[0]; $i <= $years[1]; $i++) {
    $selected_str = ($i == $year) ? 'selected="selected"' : '';
    echo "<option value=\"$i\" $selected_str>". $i;
    if (APP__ACADEMIC_YEAR_START_MONTH > 1) {
        echo '/' . substr($i + 1, 2, 2);
    }
    echo '</option>';
}
?>
  </select>
  </p>
   <div class="report">
  <p><input type="checkbox" name="assessments_run" id="assessments_run" value="assessments_run"> <label for="assessments_run">Assessments run in WebPA</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists the assessments name and the period for which the assessment was available to the students before</p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_groups" id="assessment_groups" value="assessment_groups"> <label for="assessment_groups">Number of groups per assessment</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists the assessment name and the number of groups created for each assessment</p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_students" id="assessment_students" value="assessment_students"> <label for="assessment_students">Number of students per assessment</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists the assessments and the number of students who where assigned to that assessment</p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_feedback" id="assessment_feedback" value="assessment_feedback"> <label for="assessment_feedback">Assessments where feedback has been used</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists the assessments where feedback has been used with the details of the tutors who are responsible for the assessment</p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_respondents" id="assessment_respondents" value="assessment_respondents"> <label for="assessment_respondents">Number of respondents per assessment</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists the assessments and the number of students who have responded for each assessment.</p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_modules" id="assessment_modules" value="assessment_modules"> <label for="assessment_modules">Modules which have run an assessment</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists the modules which have run assessments in the academic year
  </p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_students_thisyear" id="assessment_students_thisyear" value="assessment_students_thisyear"> <label for="assessment_students_thisyear">Number of students who have carried out an assessment this year</label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total number of students who have been involved in one or more assessments for this academic year.</p>
  </div>

  <p>Select the format in which you want to see the data:<br />
  <br />
  <input type="radio" name="format" id="html" value="html" checked="checked"> <label for="html"><img src="../../images/file_icons/report.png" alt="Report - View the report" height="32" width="32"></label>&nbsp;&nbsp;&nbsp;
  <input type="radio" name="format" id="csv" value="csv"> <label for="csv"> <img src="../../images/file_icons/csv.gif" alt="CSV - Excel Spreadsheet" height="32" width="32"></label>&nbsp;&nbsp;&nbsp;
  <input type="radio" name="format" id="rtf" value="rtf"> <label for="rtf"><img src="../../images/file_icons/page_white_word.png" alt="RTF -  Rich Text File / MS Word" height="32" width="32"></label>&nbsp;&nbsp;&nbsp;
  <input type="radio" name="format" id="xml" value="xml"> <label for="xml"><img src="../../images/file_icons/xml.gif" alt="XML -  XML File" height="32" width="32"></label>
  </p>

  <p>
  <input type="submit" name="Generate report" value="Generate"/>
  </p>

  </form>
</div>
<?php

$UI->content_end();

?>
