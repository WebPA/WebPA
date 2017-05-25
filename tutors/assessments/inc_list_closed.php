<?php
/**
 *
 *  INC: List Closed Assessments
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 * To be used from the assessments index page
 *
 * @param int $year e.g. 2005
 * @param mixed $academic_year e.g. 2005/06
 * @param string $tab eg pending
 * @param string $qs ="tab={$tab}&y={$year}";
 * @param string $page_url "/tutors/assessment/";
 *
 */
?>

<h2><?php echo sprintf(gettext('Closed assessments for %s'),$academic_year); ?></h2>

<p><?php echo gettext('These assessments were scheduled for some time in the past, and are now closed. No further student submissions can be made to closed assessments, but no marks have yet been generated.');?></p>

<hr />

<?php

// --------------------------------------------------------------------------------
// Get and organise assessments

// get the assessment that are closed (but not marked)
$now = date(MYSQL_DATETIME_FORMAT);
$assessments = $DB->fetch("SELECT a.*
              FROM " . APP__DB_TABLE_PREFIX . "assessment a
                LEFT JOIN " . APP__DB_TABLE_PREFIX . "assessment_marking am ON a.assessment_id = am.assessment_id
              WHERE a.module_id = {$_module['module_id']}
                AND a.open_date >= '{$this_year}'
                AND a.open_date < '{$next_year}'
                AND a.close_date < '{$now}'
                AND am.assessment_id IS NULL
              ORDER BY a.open_date, a.close_date, a.assessment_name");

if (!$assessments) {
?>
  <p>You do not have any assessments in this category.</p>
  <p>Please choose another category from the tabs above.</p>
<?php
} else {
?>
  <div class="obj_list">
<?php
    // prefetch response counts for each assessment
  $result_handler = new ResultHandler($DB);
  $responses = $result_handler->get_responses_count_for_user($_user->id, $year);
  $members = $result_handler->get_members_count_for_user($_user->id, $year);

  // loop through and display all the assessments
  $assessment_iterator = new SimpleObjectIterator($assessments,'Assessment','$DB');
  for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
    $assessment =& $assessment_iterator->current();

    $num_responses = (array_key_exists($assessment->id, $responses)) ? $responses[$assessment->id] : 0 ;
    $num_members =  (array_key_exists($assessment->id, $members)) ? $members[$assessment->id] : 0 ;
    $completed_msg = ($num_responses==$num_members) ? '- <strong>'.gettext('COMPLETED').'</strong>' : '';

    $edit_url = "edit/edit_assessment.php?a={$assessment->id}&{$qs}";
    $email_url = "email/index.php?a={$assessment->id}&{$qs}";
    $responded_url = "students_who_responded.php?a={$assessment->id}&{$qs}";
    $groupmark_url = "marks/set_group_marks.php?a={$assessment->id}&{$qs}";
    $mark_url = "marks/mark_assessment.php?a={$assessment->id}&{$qs}";
    $student_inputs_url = "reports/report_student_inputs.php?a={$assessment->id}&{$qs}";
?>
    <div class="obj">
      <table class="obj" cellpadding="2" cellspacing="2">
      <tr>
        <td class="icon" width="24"><img src="../../images/icons/closed_icon.gif" alt="<?php echo gettext('Closed');?>" title="<?php echo gettext('Closed');?>" height="24" width="24" /></td>
        <td class="obj_info">
          <div class="obj_name"><?php echo($assessment->name); ?></div>
          <div class="obj_info_text"><?php echo gettext('scheduled:');?> <?php echo($assessment->get_date_string('open_date')); ?> &nbsp;-&nbsp; <?php echo($assessment->get_date_string('close_date')); ?></div>
          <div class="obj_info_text"><?php echo gettext('student responses:');?> <?php echo("$num_responses / $num_members $completed_msg"); ?></div>
        </td>
        <td class="buttons">
          <a href="<?php echo($edit_url); ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="<?php echo gettext('Edit');?>" title="<?php echo gettext('Edit assessment');?>" /></a>
          <a href="<?php echo($email_url); ?>"><img src="../../images/buttons/email.gif" width="16" height="16" alt="<?php echo gettext('Email');?>" title="<?php echo gettext('Email students');?>" /></a>
          <a href="<?php echo($responded_url); ?>"><img src="../../images/buttons/students_responded.gif" width="16" height="16" alt="<?php echo gettext('Students responded');?>" title="<?php echo gettext('Check which students have responded');?>" /></a>
          <a href="<?php echo($groupmark_url); ?>"><img src="../../images/buttons/group_marks.gif" width="16" height="16" alt="<?php echo gettext('Group Marks');?>" title="<?php echo gettext('Set group marks');?>" /></a>
          <a href="<?php echo($mark_url); ?>"><img src="../../images/buttons/mark_sheet.gif" width="16" height="16" alt="<?php echo gettext('Mark Sheet');?>" title="<?php echo gettext('New mark sheet');?>" /></a>
          <a href="<?php echo($student_inputs_url); ?>"><img src="../../images/icons/view_data.gif" width="16" height="16" alt="<?php echo gettext('View student inputs');?>" title="<?php echo gettext('View student inputs');?>" /></a>
        </td>
      </tr>
      </table>
    </div>
<?php
  }
  echo("  </div>\n");
}
?>
