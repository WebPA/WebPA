<?php
/**
 *
 * landing page for the metrics section of the admin area
 *
 * From here after selecting the type of information that the user wants from
 * the metrics choices they will be shown their choices.
 *
 * @copyright 2008 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 24 Jul 2008
 *
 */

//get the include file required
require_once("../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/functions/lib_university_functions.php');

if (!check_user($_user, APP__USER_TYPE_ADMIN)) {
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

$intro_text = "<p>".gettext('This page allows you to build a report on commonly requested metrics about the WebPA systems usage.')."</p>";
$task_text = "<p>".gettext('Please tick all the boxes for the information that you would like to have in your report. Then click the generate button to view the report.')."</p>";

//set the page information
$UI->page_title = APP__NAME .' '.gettext('metrics of use');
$UI->menu_selected = gettext('metrics');
$UI->breadcrumbs = array ('home' => '../../');
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
$todays_year = get_academic_year();
$year = (int) fetch_SESSION('year', $todays_year);

$UI->body();
$UI->content_start();

echo $intro_text;
echo "<div class=\"content_box\">";
echo $task_text;

//write a list of the elements that can be selected for the report

?>

  <form action="report.php" method="post" name="SelectReports">
  <p>
  <label for="academic_year"><?php echo gettext('Academic year to report on');?></label>
  <select name="academic_year" id="academic_year">
<?php
for ($i = $years[0]; $i <= $years[1]; $i++) {
  $selected_str = ($i == $year) ? 'selected="selected"' : '';
  echo("<option value=\"$i\" $selected_str>". $i);
  if (APP__ACADEMIC_YEAR_START_MONTH > 1) {
    echo('/' . substr($i + 1, 2, 2));
  }
  echo ('</option>');
}
?>
  </select>
  </p>
   <div class="report">
  <p><input type="checkbox" name="assessments_run" id="assessments_run" value="assessments_run"> <label for="assessments_run"><?php echo gettext('Assessments run in WebPA');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Lists the assessments name and the period for which the assessment was available to the students before');?></p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_groups" id="assessment_groups" value="assessment_groups"> <label for="assessment_groups"><?php echo gettext('Number of groups per assessment');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Lists the assessment name and the number of groups created for each assessment');?></p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_students" id="assessment_students" value="assessment_students"> <label for="assessment_students"><?php echo gettext('Number of students per assessment');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Lists the assessments and the number of students who where assigned to that assessment');?></p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_feedback" id="assessment_feedback" value="assessment_feedback"> <label for="assessment_feedback"><?php echo gettext('Assessments where feedback has been used');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Lists the assessments where feedback has been used with the details of the tutors who are responsible for the assessment');?></p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_respondents" id="assessment_respondents" value="assessment_respondents"> <label for="assessment_respondents"><?php echo gettext('Number of respondents per assessment');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Lists the assessments and the number of students who have responded for each assessment.');?></p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_modules" id="assessment_modules" value="assessment_modules"> <label for="assessment_modules"><?php echo gettext('Modules which have run an assessment');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Lists the modules which have run assessments in the academic year');?>
  </p>
  </div>
  <div class="report">
  <p><input type="checkbox" name="assessment_students_thisyear" id="assessment_students_thisyear" value="assessment_students_thisyear"> <label for="assessment_students_thisyear"><?php echo gettext('Number of students who have carried out an assessment this year');?></label><br/>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext('Total number of students who have been involved in one or more assessments for this academic year.');?></p>
  </div>

  <p><?php echo gettext('Select the format in which you want to see the data:');?><br />
  <br />
  <input type="radio" name="format" id="html" value="html" checked="checked"> <label for="html"><img src="../../images/file_icons/report.png" alt="<?php echo gettext('Report - View the report');?>" height="32" width="32"></label>&nbsp;&nbsp;&nbsp;
  <input type="radio" name="format" id="csv" value="csv"> <label for="csv"> <img src="../../images/file_icons/csv.gif" alt="<?php echo gettext('CSV - Excel Spreadsheet');?>" height="32" width="32"></label>&nbsp;&nbsp;&nbsp;
  <input type="radio" name="format" id="rtf" value="rtf"> <label for="rtf"><img src="../../images/file_icons/page_white_word.png" alt="<?php echo gettext('RTF -  Rich Text File / MS Word');?>" height="32" width="32"></label>&nbsp;&nbsp;&nbsp;
  <input type="radio" name="format" id="xml" value="xml"> <label for="xml"><img src="../../images/file_icons/xml.gif" alt="<?php echo gettext('XML -  XML File');?>" height="32" width="32"></label>
  </p>

  <p>
  <input type="submit" name="Generate report" value="<?php echo gettext('Generate');?>"/>
  </p>

  </form>
</div>
<?php

$UI->content_end();

?>
