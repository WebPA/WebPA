<?php
/**
 * INDEX - Student index
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\SimpleObjectIterator;
use WebPA\includes\functions\AcademicYear;
use WebPA\includes\functions\ArrayFunctions;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_STUDENT)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------

$group_handler = new GroupHandler();

// Get a list of collections that the user is a member of

$collections = $group_handler->get_member_collections($_user->id, 'assessment');

$collection_ids = ArrayFunctions::array_extract_column($collections, 'collection_id');

// Get a list of assessments that match the user's collections (for this year)

$academic_year = AcademicYear::get_academic_year();

$start_date = mktime(0, 0, 0, APP__ACADEMIC_YEAR_START_MONTH, 1, $academic_year - 1);
$end_date = mktime(0, 0, 0, APP__ACADEMIC_YEAR_START_MONTH, 1, $academic_year + 1);

$sql_start_date = date(MYSQL_DATETIME_FORMAT, $start_date);
$sql_end_date = date(MYSQL_DATETIME_FORMAT, $end_date);

$assessmentsQuery =
    'SELECT a.* FROM ' .
    APP__DB_TABLE_PREFIX . 'assessment a ' .
    'WHERE a.module_id = ? ' .
    'AND a.collection_id IN (?) ' .
    'AND a.open_date >= ? ' .
    'AND a.open_date < ? ' .
    'ORDER BY a.open_date, a.close_date, a.assessment_name';

$assessments = $DB->getConnection()->fetchAllAssociative(
        $assessmentsQuery,
        [
            $_module_id,
            $collection_ids,
            $sql_start_date,
            $sql_end_date,
        ],
        [
            ParameterType::INTEGER,
            $DB->getConnection()::PARAM_STR_ARRAY,
            ParameterType::STRING,
            ParameterType::STRING,
        ]
);

// Get a list of those assessments that the user has already taken

$assessment_ids = array_column($assessments, 'assessment_id');

$assessmentsWithResponseQuery =
    'SELECT DISTINCT assessment_id ' .
    'FROM ' . APP__DB_TABLE_PREFIX . 'user_mark ' .
    'WHERE assessment_id IN (?) ' .
    'AND user_id = ? ' .
    'ORDER BY assessment_id';

$assessments_with_response = $DB->getConnection()->fetchFirstColumn($assessmentsWithResponseQuery, [$assessment_ids, $_user->id], [$DB->getConnection()::PARAM_STR_ARRAY, ParameterType::INTEGER]);

// Split the assessments into pending, open and finished
$pending_assessments = null;
$open_assessments = null;
$finished_assessments = null;

if ($assessments) {
    foreach ($assessments as $i => $assessment) {
        if ((is_array($assessments_with_response)) && (in_array($assessment['assessment_id'], $assessments_with_response))) {
            $finished_assessments[] = $assessment;
        } else {
            $now = time();
            $open_date = strtotime($assessment['open_date']);
            $close_date = strtotime($assessment['close_date']);

            if ($close_date<=$now) {
                $finished_assessments[] = $assessment;
            } else {
                if ($open_date>$now) {
                    $pending_assessments[] = $assessment;
                } else {
                    $open_assessments[] = $assessment;
                }
            }
        }
    }
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' my assessments';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/329';
$UI->breadcrumbs = [
                'my assessments'      => null,
              ];


$UI->head();
$UI->body();

$UI->content_start();
?>

<p>This page lists all the assessments you are registered on in this module.</p>

<div class="content_box">

<?php
if ((!$open_assessments) && (!$pending_assessments) && (!$finished_assessments)) {
    echo '<p>You are not registered with any peer assessments in this module at the moment.</p>';
} else {
    echo '<p>You are registered on the following peer assessments in this module:</p>';

    if ($open_assessments) {
        ?>
      <h2>Open Assessments</h2>
      <p>These assessments are now open for you to take and record your group <?php echo APP__MARK_TEXT; ?>.</p>
      <div class="form_section form_line">
<?php
    $status = 'open';
        $status_capitalized = ucfirst($status);

        $assessment_iterator = new SimpleObjectIterator($open_assessments, 'Assessment', $DB);
        for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
            $assessment =& $assessment_iterator->current();
            $take_url = "take/index.php?a={$assessment->id}";

            echo '<div class="assessment_open">';
            echo '<table class="assessment_info" cellpadding="0" cellspacing="0">';
            echo '<tr>';
            echo "  <td width=\"24\"><img src=\"../../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>";
            echo '  <td valign="top">';
            echo '    <div class="assessment_info">';
            echo "      <div class=\"assessment_name\">{$assessment->name}</div>";
            echo '      <div class="assessment_schedule">scheduled: '. $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>';
            echo '    </div>';
            echo '  </td>';
            echo '  <td class="buttons" style="line-height: 2em; text-align: right;">';
            echo "    <a class=\"button\" href=\"$take_url\">Take Assessment</a>";
            echo '  </td>';
            echo '</tr>';
            echo '</table>';
            echo '</div>';
        } ?>
      </div>
<?php
    }
    if ($pending_assessments) {
        ?>
      <h2>Pending Assessments</h2>
      <p>These assessments scheduled for some point in the future.</p>
      <div class="form_section form_line">
<?php
    $status = 'pending';
        $status_capitalized = ucfirst($status);

        $assessment_iterator = new SimpleObjectIterator($pending_assessments, 'Assessment', $DB);
        for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
            $assessment =& $assessment_iterator->current();
            $take_url = "take/index.php?a={$assessment->id}";

            echo '<div class="assessment">';
            echo '<table class="assessment_info" cellpadding="0" cellspacing="0">';
            echo '<tr>';
            echo "  <td width=\"24\"><img src=\"../../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>";
            echo '  <td valign="top">';
            echo '    <div class="assessment_info">';
            echo "      <div class=\"assessment_name\">{$assessment->name}</div>";
            echo '      <div class="assessment_schedule">scheduled: '. $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>';
            echo '    </div>';
            echo '  </td>';
            echo '</tr>';
            echo '</table>';
            echo '</div>';
        } ?>
      </div>
<?php
    }

    if ($finished_assessments) {
        ?>
      <h2>Finished Assessments</h2>
      <p>These assessments you have already taken, or which have passed their deadline for completion.</p>
      <p>Some of your assessments may allow you to see feedback on your performance. Click <em>view feedback</em> (if available) for a particular assessment to see the feedback.</p>
      <div class="form_section">
<?php
    $status = 'finished';
        $status_capitalized = ucfirst($status);

        $now = time();

        $assessment_iterator = new SimpleObjectIterator($finished_assessments, 'Assessment', $DB);
        for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
            $assessment =& $assessment_iterator->current();
            $take_url = "take/index.php?a={$assessment->id}";

            $completed_msg = ((is_array($assessments_with_response)) && (in_array($assessment->id, $assessments_with_response))) ? 'COMPLETED': 'DID NOT<br />SUBMIT';

            echo '<div class="assessment_finished">';
            echo '<table class="assessment_info" cellpadding="0" cellspacing="0">';
            echo '<tr>';
            echo "  <td width=\"24\"><img src=\"../../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>";
            echo '  <td valign="top">';
            echo '    <div class="assessment_info">';
            echo "      <div class=\"assessment_name\">{$assessment->name}</div>";
            echo '      <div class="assessment_schedule">scheduled: '. $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>';
            echo '    </div>';
            echo '  </td>';
            echo '  <td style="font-weight: bold; text-align: center;">';
            echo "    $completed_msg";
            if (($assessment->allow_feedback) && ($assessment->close_date<$now)) {
                echo "<div style=\"margin-top: 0.5em;\"><a href=\"assessment_feedback.php?a={$assessment->id}\" target=\"_blank\">view feedback</a></div>";
            }
            echo '  </td>';
            echo '</tr>';
            echo '</table>';
            echo '</div>';
        }
        echo "    </div>\n";
    }
}
?>

</div>

<?php

$UI->content_end();

?>
