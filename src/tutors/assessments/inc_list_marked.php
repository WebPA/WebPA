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
    'SELECT DISTINCT a.*, pd.publish_date AS comments_publish_date ' .
    'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
    'LEFT JOIN ' . APP__DB_TABLE_PREFIX . 'assessment_marking am ' .
    'ON a.assessment_id = am.assessment_id ' .
    'LEFT JOIN ' . APP__DB_TABLE_PREFIX . 'user_justification_publish_date pd ' .
    'ON a.assessment_id = pd.assessment_id ' .
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

/**
 * Map assessment IDs to comment publication dates. This is neededed because the SimpleObjectIterator used on this page
 * only deals with fields in the assessment table. The comment publication date is stored in a different table so is
 * not included in the Assessment class
 */
$commentsPublicationDateMap = [];

foreach ($assessments as $assessment) {
    $commentsPublicationDateMap[$assessment['assessment_id']] = $assessment['comments_publish_date'];
}


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

            $commentPublicationDate = $commentsPublicationDateMap[$assessment->id];

            $num_responses = (array_key_exists($assessment->id, $responses)) ? $responses[$assessment->id] : 0 ;
            $num_members =  (array_key_exists($assessment->id, $members)) ? $members[$assessment->id] : 0 ;
            $completed_msg = ($num_responses==$num_members) ? '- <strong>COMPLETED</strong>' : '';

            $edit_url = "edit/edit_assessment.php?a={$assessment->id}&{$qs}";
            $email_url = "email/index.php?a={$assessment->id}&{$qs}";
            $groupmark_url = "marks/set_group_marks.php?a={$assessment->id}&{$qs}";
            $responded_url = "students_who_responded.php?a={$assessment->id}&{$qs}";
            $mark_url = "marks/mark_assessment.php?a={$assessment->id}&{$qs}";
            $review_justifications_url = "marks/review_justification.php?a={$assessment->id}&{$qs}";

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
            <a href="<?= $edit_url ?>" title="Edit" aria-label="Edit"><i data-feather="edit-2" aria-hidden="true"></i></a>
            <a href="<?= $email_url ?>" title="Email students" aria-label="Email students"><i data-feather="mail" aria-hidden="true"></i></a>
            <a href="<?= $responded_url ?>" title="Which students responded" aria-label="Which students responded"><i data-feather="user-check" aria-hidden="true"></i></a>
            <a href="<?= $groupmark_url ?>" title="Set group marks" aria-label="Set group marks"><i data-feather="check" aria-hidden="true"></i></a>
            <a href="<?= $mark_url ?>" title="New marksheet" aria-label="New marksheet"><i data-feather="file-text" aria-hidden="true"></i></a>
            <?php if ($assessment->view_feedback === 1 && $commentPublicationDate === null) : ?>
            <a href="<?= $review_justifications_url ?>" title="Review justification comments" aria-label="Review justification comments"><i data-feather="message-circle" aria-hidden="true"></i></a>
            <?php endif; ?>
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
