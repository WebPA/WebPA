<?php
/**
 *  INC: List Closed Assessments
 *
 * To be used from the assessments index page
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\classes\SimpleObjectIterator;

?>

<h2>Closed assessments for <?php echo $academic_year; ?></h2>

<p>These assessments were scheduled for some time in the past, and are now closed. No further student submissions can be made to closed assessments, but no marks have yet been generated.</p>

<hr />

<?php

// --------------------------------------------------------------------------------
// Get and organise assessments

// get the assessment that are closed (but not marked)
$now = date(MYSQL_DATETIME_FORMAT);

$assessmentsQuery =
    'SELECT a.* ' .
    'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
    'LEFT JOIN ' . APP__DB_TABLE_PREFIX . 'assessment_marking am ' .
    'ON a.assessment_id = am.assessment_id ' .
    'WHERE a.module_id = ? ' .
    'AND a.open_date >= ? ' .
    'AND a.open_date < ? ' .
    'AND a.close_date < ? ' .
    'AND am.assessment_id IS NULL ' .
    'ORDER BY a.open_date, a.close_date, a.assessment_name';

$assessments = $DB->getConnection()->fetchAllAssociative(
    $assessmentsQuery,
    [
            $_module['module_id'],
            $this_year,
            $next_year,
            $now,
        ],
    [
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
        ]
);

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
        $assessment_iterator = new SimpleObjectIterator($assessments, 'Assessment', $DB);

        for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
            $assessment =& $assessment_iterator->current();

            $num_responses = (array_key_exists($assessment->id, $responses)) ? $responses[$assessment->id] : 0 ;
            $num_members =  (array_key_exists($assessment->id, $members)) ? $members[$assessment->id] : 0 ;
            $completed_msg = ($num_responses==$num_members) ? '- <strong>COMPLETED</strong>' : '';

            $edit_url = "edit/edit_assessment.php?a={$assessment->id}&{$qs}";
            $email_url = "email/index.php?a={$assessment->id}&{$qs}";
            $responded_url = "students_who_responded.php?a={$assessment->id}&{$qs}";
            $groupmark_url = "marks/set_group_marks.php?a={$assessment->id}&{$qs}";
            $mark_url = "marks/mark_assessment.php?a={$assessment->id}&{$qs}"; ?>
    <div class="obj">
      <table class="obj" cellpadding="2" cellspacing="2">
      <tr>
        <td class="icon" width="24"><img src="../../images/icons/closed_icon.gif" alt="Closed" title="Closed" height="24" width="24" /></td>
        <td class="obj_info">
          <div class="obj_name"><?php echo $assessment->name; ?></div>
          <div class="obj_info_text">scheduled: <?php echo $assessment->get_date_string('open_date'); ?> &nbsp;-&nbsp; <?php echo $assessment->get_date_string('close_date'); ?></div>
          <div class="obj_info_text">student responses: <?php echo "$num_responses / $num_members $completed_msg"; ?></div>
        </td>
        <td class="buttons">
          <a href="<?php echo $edit_url; ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="Edit" title="Edit assessment" /></a>
          <a href="<?php echo $email_url; ?>"><img src="../../images/buttons/email.gif" width="16" height="16" alt="Email" title="Email students" /></a>
          <a href="<?php echo $responded_url; ?>"><img src="../../images/buttons/students_responded.gif" width="16" height="16" alt="Students responded" title="Check which students have responded" /></a>
          <a href="<?php echo $groupmark_url; ?>"><img src="../../images/buttons/group_marks.gif" width="16" height="16" alt="Group Marks" title="Set group marks" /></a>
          <a href="<?php echo $mark_url; ?>"><img src="../../images/buttons/mark_sheet.gif" width="16" height="16" alt="Mark Sheet" title="New mark sheet" /></a>
        </td>
      </tr>
      </table>
    </div>
<?php
        }
        echo "  </div>\n";
    }
?>
