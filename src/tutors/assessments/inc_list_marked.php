<?php

/**
 *
 * INC: List Marked Assessments
 *
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

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\classes\SimpleObjectIterator;
use WebPA\includes\classes\XMLParser;

?>

<h2>Marked assessments for <?php echo $academic_year; ?></h2>

<p>These assessments are both closed and have been marked to produce student grades.</p>

<hr />

<?php

// --------------------------------------------------------------------------------
// Get and organise assessments

// get the assessment that are closed and have been marked
$now = date(MYSQL_DATETIME_FORMAT);

$assessmentsQuery =
    'SELECT DISTINCT a.* ' .
    'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
    'LEFT JOIN ' . APP__DB_TABLE_PREFIX . 'assessment_marking am ' .
    'ON a.assessment_id = am.assessment_id ' .
    'WHERE a.module_id = ? ' .
    'AND a.open_date >= ? ' .
    'AND a.open_date < ? ' .
    'AND a.close_date < ? ' .
    'AND am.assessment_id IS NOT NULL ' .
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

        // Create an XML Parser for showing the mark sheets
        $xml_parser = new XMLParser();

        // loop through and display all the assessments
        $assessment_iterator = new SimpleObjectIterator($assessments, 'Assessment', $DB);

        for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
            $assessment =& $assessment_iterator->current();
            $assessment->set_db($DB);

            $num_responses = (array_key_exists($assessment->id, $responses)) ? $responses[$assessment->id] : 0 ;
            $num_members =  (array_key_exists($assessment->id, $members)) ? $members[$assessment->id] : 0 ;
            $completed_msg = ($num_responses==$num_members) ? '- <strong>COMPLETED</strong>' : '';

            $edit_url = "edit/edit_assessment.php?a={$assessment->id}&{$qs}";
            $email_url = "email/index.php?a={$assessment->id}&{$qs}";
            $groupmark_url = "marks/set_group_marks.php?a={$assessment->id}&{$qs}";
            $responded_url = "students_who_responded.php?a={$assessment->id}&{$qs}";
            $mark_url = "marks/mark_assessment.php?a={$assessment->id}&{$qs}";

            $mark_sheets = $assessment->get_all_marking_params(); ?>
    <div class="obj">
      <table class="obj" cellpadding="2" cellspacing="2">
      <tr>
        <td class="icon" width="24"><img src="../../images/icons/finished_icon.gif" alt="Finished" title="Finished" height="24" width="24" /></td>
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
<?php
    if ($mark_sheets) {
        foreach ($mark_sheets as $date_created => $params) {
            $date_created = strtotime($date_created);
            $reports_url = "reports/index.php?a={$assessment->id}&md={$date_created}&{$qs}";

            $algorithm = $params['algorithm'];
            $penalty_type = ($params['penalty_type']=='pp') ? ' pp' : '%' ;   // Add a space to the 'pp'.
            $tolerance = ($params['tolerance']==0) ? 'N/A' : "+/- {$params['tolerance']}%" ;
            $grading = ($params['grading']=='grade_af') ? 'A-F' : 'Numeric (%)' ;

            echo '    <div class="mark_sheet">';
            echo '      <table class="mark_sheet_info" cellpadding="0" cellspacing="0">';
            echo '      <tr>';
            echo '        <td>';
            echo '          <div class="mark_sheet_title">Mark Sheet</div>';
            echo "          <div class=\"info\" style=\"font-weight: bold;\">Algorithm: {$algorithm}.</div>";
            echo "          <div class=\"info\">PA weighting: {$params['weighting']}%</div>";
            echo "          <div class=\"info\">Non-completion penalty: {$params['penalty']}{$penalty_type}</div>";

            // @todo : implement tolerances and show to users clearly.
            //          echo("          <div class=\"info\">Score Tolerance: {$tolerance}</div>");

            echo "          <div class=\"info\">Grading: {$grading}</div>";
            echo '        </td>';
            echo '        <td class="buttons" style="line-height: 2em;">';
            echo "          <a class=\"button\" href=\"$reports_url\">View&nbsp;Reports</a>";
            echo '        </td>';
            echo '      </tr>';
            echo '      </table>';
            echo '    </div>';
        }
    }// /if(mark sheets)
            echo "    </div>\n";
        }
        $xml_parser->destroy();
        echo "  </div>\n";
    }
?>
