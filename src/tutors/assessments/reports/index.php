<?php
/**
 * View Assessment Reports
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\Form;
use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\classes\XMLParser;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = Common::fetch_GET('a');

$tab = Common::fetch_GET('tab');
$year = Common::fetch_GET('y', date('Y'));

$marking_date = (int) Common::fetch_GET('md');

$command = Common::fetch_POST('command');

$qs = "a={$assessment_id}&md={$marking_date}&tab={$tab}&y={$year}";
$list_url = "../index.php?tab={$tab}&y={$year}";

// --------------------------------------------------------------------------------

$do_reports = false;

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $do_reports = true;
    $assessment_qs = "a={$assessment->id}&tab={$tab}&y={$year}";

    $form = new Form($DB);
    $form_xml = $assessment->get_form_xml();
    $form->load_from_xml($form_xml);
    $question_count = (int) $form->get_question_count();

    $group_handler = new GroupHandler();
    $collection = $group_handler->get_collection($assessment->get_collection_id());
    $groups = $collection->get_groups_array();
    $groups_count = count($groups);

    $result_handler = new ResultHandler($DB);
    $result_handler->set_assessment($assessment);

    // check if there are group grades
    $groupMarksQuery =
      'SELECT group_mark_xml ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'assessment_group_marks ' .
      'WHERE assessment_id = ?';

    $group_marks_xml = $DB->getConnection()->fetchOne($groupMarksQuery, [$assessment->id], [ParameterType::STRING]);

    $xml_parser = null;

    $do_all_reports = true;

    if ($group_marks_xml) {
        $xml_parser = new XMLParser();
        $xml_array = $xml_parser->parse($group_marks_xml);

        // If there's more than 1 group that's fine, else make it a 0-based array of 1 group
        if (array_key_exists(0, $xml_array['groups']['group'])) {
            $groups = $xml_array['groups']['group'];
        } else {
            $groups[0] = $xml_array['groups']['group'];
        }
        foreach ($groups as $i => $group) {
            $group_marks["{$group['_attributes']['id']}"] = $group['_attributes']['mark'];
        }
    } else {
        $do_reports = true;
        $do_all_reports = false;
        $no_reports_reason = 'You have not recorded the group marks for this assessment so some reports are not available.<br />Please <a href="../marks/set_group_marks.php?'. $qs .'">enter the overall group marks</a> before viewing your reports.';
    }

    // check if there are student responses
    $result_handler = new ResultHandler($DB);
    $result_handler->set_assessment($assessment);
    $responses = $result_handler->get_responses();
    if (!$responses) {
        $do_reports = false;
        $no_reports_reason = 'There have been no student responses to this assessment. Please select another assessment, or <a href="../edit/edit_assessment.php?'. $qs .'">re-schedule this one</a>.';
    }

    // Get the marking parameters
    $marking_params = $assessment->get_marking_params($marking_date);
} else {
    $do_reports = false;
    $no_reports_reason = 'The assessment you selected could not be loaded for some reason. Please go back and try again.';

    $assessment = null;
    $question_count = 0;
    $groups_count = 0;
}

// --------------------------------------------------------------------------------
// Begin Page

$page_title = ($assessment) ? "reports: {$assessment->name}" : 'reports';

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my assessments';
$UI->breadcrumbs = [
  'home'          => '../../',
  'my assessments'    => '../',
  $page_title       => null,
];
$UI->help_link = '?q=node/235';
$UI->set_page_bar_button('List Assessments', '../../../../images/buttons//button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../../create/');

$UI->head();
?>
<style type="text/css">
<!--

div.report { margin-bottom: 16px; padding: 4px; background: #c9ffd6 url(../../../images/backgrounds/gradient_light_green-white_l-r.png) repeat-y right; border: 1px solid #ccc; border-right: 0px; }
div.report div.title { margin-bottom: 4px; font-weight: bold; }
div.report div.info {  }
div.report table td.buttons { width: 90px; padding-right: 20px; text-align: right; }
div.report table td.downloads { width: 80px; padding-right: 10px; }
div.report table td.downloads div { margin-bottom: 4px; }

-->
</style>
<?php
$UI->content_start();
?>

<p>On this page you can select the different reports to view for this assessment.</p>

<div class="content_box">

<div class="nav_button_bar">
  <a href="<?php echo $list_url ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a>
</div>

<p>Reports available for: <em><?php echo $assessment->name?></em>.</p>

<?php
if ($marking_params) {
    $penalty_type = ($marking_params['penalty_type']=='pp') ? ' pp' : '%' ;   // Add a space to the 'pp'.?>
  <p style="padding-left: 1em; font-size: 0.8em;">
    (
    Algorithm: <?php echo $marking_params['algorithm']; ?>. &nbsp;

    Weighting: <?php echo $marking_params['weighting']; ?>%. &nbsp;

    Penalty: <?php echo $marking_params['penalty'].$penalty_type; ?>. &nbsp;

    Grading: <?php
      if ($marking_params['grading']=='grade_af') {
          echo 'A-F.';
      } else {
          echo 'Numeric (%).';
      } ?>
    )
  </p>
<?php
}
?>

<?php
if (!$do_reports) {
    ?>
  <div class="warning_box">
    <p><strong>Unable to display reports</strong></p>
    <p><?php echo $no_reports_reason; ?></p>
  </div>
<?php
} else {
        ?>
<h2>Choose a report</h2>
<div class="form_section">
  <p>Please select a report to display or download from the list below.</p>

  <div class="report">
    <table cellpadding="2" cellspacing="2" width="100%">
    <tr>
      <td valign="top">
        <div class="title">Marks Awarded For Each Question</div>
        <div class="info">A breakdown of the marks given by each student for every question in the assessment.</div>
      </td>
      <td class="downloads" nowrap="nowrap" valign="top">
        <div>View Report:</div>
        <a href="report_marks_awarded_byquestion_named.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
      </td>
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_marks_awarded_byquestion_named.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
      </td>
    </tr>
    </table>
  </div>

  <div class="report">
    <table cellpadding="2" cellspacing="2" width="100%">
    <tr>
      <td valign="top">
        <div class="title">Marks Awarded For Each Question (anonymous)</div>
        <div class="info">A breakdown of the marks given by each student for every question in the assessment.<br />Student names/numbers are <strong>NOT</strong> displayed.</div>
      </td>
      <td class="downloads" nowrap="nowrap" valign="top">
        <div>View Report:</div>
        <a  href="report_marks_awarded_byquestion_anonymous.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
      </td>
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_marks_awarded_byquestion_anonymous.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
      </td>
    </tr>
    </table>
  </div>

  <div class="report">
    <table cellpadding="2" cellspacing="2" width="100%">
    <tr>
      <td valign="top">
        <div class="title">Student Response Information</div>
        <div class="info">Shows the date, time and location that each student took the assessment.</div>
      </td>
      <td class="downloads" nowrap="nowrap" valign="top">
        <div>View Report</div>
        <a href="report_student_response_info.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
      </td>
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_student_response_info.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
      </td>
    </tr>
    </table>
  </div>

<?php
  if (!$do_all_reports) {
      ?>
    <div class="warning_box">
      <p><strong>Unable to display the other reports</strong></p>
      <p><?php echo $no_reports_reason; ?></p>
    </div>
<?php
  } else {
      ?>

    <div class="report">
      <table cellpadding="2" cellspacing="2" width="100%">
      <tr>
        <td valign="top">
          <div class="title">Student Grades</div>
          <div class="info">A list of students (by lastname) and their final WebPA scores and grades.</div>
        </td>
        <td class="downloads"  nowrap="nowrap"  valign="top">
          <div>View Report:</div>
          <a href="report_student_grades.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades.php?t=download-xml&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/xml.gif" width="32" height="32" alt="XML -  XML File" /></a>
        </td>
        <?php
        if (APP__MOODLE_GRADEBOOK) {
            ?>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades.php?t=download-moodle-xml&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/moodle.gif" width="32" height="32" alt="Moodle - Moodle Gradebook Import XML" /></a>
        </td>
        <?php
        } ?>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades.php?t=download-rtf&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/page_white_word.png" width="32" height="32" alt="RTF -  Rich Text File / MS Word" /></a>
        </td>
      </tr>
      </table>
    </div>

    <div class="report">
      <table cellpadding="2" cellspacing="2" width="100%">
      <tr>
        <td valign="top">
          <div class="title">Student Grades (by group)</div>
          <div class="info">A list of students (by group) and their final WebPA scores and grades.</div>
        </td>
        <td class="downloads" nowrap="nowrap" valign="top">
          <div>View Report:</div>
          <a href="report_student_grades_bygroup.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades_bygroup.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades_bygroup.php?t=download-xml&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/xml.gif" width="32" height="32" alt="XML -  XML File" /></a>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades_bygroup.php?t=download-rtf&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/page_white_word.png" width="32" height="32" alt="RTF -  Rich Text File / MS Word" /></a>
        </td>
      </tr>
      </table>
    </div>
<?php
    if (APP__ALLOW_TEXT_INPUT) {
        if ($assessment->allow_assessment_feedback) {
            ?>
    <div class="report">
      <table cellpadding="2" cellspacing="2" width="100%">
      <tr>
        <td valign="top">
          <div class="title">Student Feedback and Justification (by group)</div>
          <div class="info">A list of students (by group) and their feedback about the assessment and justification of the WebPA scores and grades.</div>
        </td>
        <td class="downloads" nowrap="nowrap" valign="top">
          <div>View Report:</div>
          <a href="report_student_feedback_bygroup.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_feedback_bygroup.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
        </td>
      </tr>
      </table>
    </div>
    <div class="report">
      <table cellpadding="2" cellspacing="2" width="100%">
      <tr>
        <td valign="top">
          <div class="title">Student Grades and Feedback</div>
          <div class="info">This report can be used to upload WebPA assessment marks into the grade book.  Comments are not currently supported for upload.  If you would like the comments emailed to your students please contact your WebPA administrator.</div>
        </td>
        <td class="downloads" valign="top">
          <div>Download:</div>
          <a href="report_student_grades_comments.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
        </td>
      </tr>
      </table>
    </div>
<?php
        }
    }
  }

        // output the reports as requested from the UEA, UK -- Source forge request id 2042746?>
<hr>
  <div class="report">
    <table cellpadding="2" cellspacing="2" width="100%">
    <tr>
      <td valign="top">
        <div class="title">Responses per student</div>
        <div class="info">This report was requested by UEA and provides the reponses given for each individual student per group.</div>
      </td>
      <td class="downloads" nowrap="nowrap" valign="top">
        <div>View Report:</div>
        <a href="report_uea.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
      </td>
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_uea.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
      </td>
      <!--
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_student_grades_bygroup.php?t=download-xml&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/xml.gif" width="32" height="32" alt="XML -  XML File" /></a>
      </td>
      -->
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_uea.php?t=download-rtf&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/page_white_word.png" width="32" height="32" alt="RTF -  Rich Text File / MS Word" /></a>
      </td>

    </tr>
    </table>
  </div>
  <div class="report">
    <table cellpadding="2" cellspacing="2" width="100%">
    <tr>
      <td valign="top">
        <div class="title">Responses per student (anonymous)</div>
        <div class="info">This report was requested by UEA and provides the reponses given for each individual student per group. The data on who gave the response has been anonymised</div>
      </td>
      <td class="downloads" nowrap="nowrap" valign="top">
        <div>View Report:</div>
        <a href="report_uea_anonymous.php?t=view&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/report.png" width="32" height="32" alt="Report - View the report" /></a>
      </td>
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_uea_anonymous.php?t=download-csv&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/csv.gif" width="32" height="32" alt="CSV - Excel Spreadsheet" /></a>
      </td>
      <!--
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_student_grades_bygroup.php?t=download-xml&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/xml.gif" width="32" height="32" alt="XML -  XML File" /></a>
      </td>
      -->
      <td class="downloads" valign="top">
        <div>Download:</div>
        <a href="report_uea_anonymous.php?t=download-rtf&<?php echo $qs; ?>" target="_blank"><img src="../../../images/file_icons/page_white_word.png" width="32" height="32" alt="RTF -  Rich Text File / MS Word" /></a>
      </td>

    </tr>
    </table>
  </div>
<?php
    }
?>
</div>

</div>

<?php

$UI->content_end();

?>
