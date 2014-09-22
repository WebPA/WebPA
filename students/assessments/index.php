<?php
/**
 *
 * INDEX - Student index
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../includes/inc_global.php");
require_once("../../includes/classes/class_group_handler.php");
require_once("../../includes/classes/class_simple_object_iterator.php");
require_once("../../includes/functions/lib_university_functions.php");
require_once("../../includes/classes/class_assessment.php");

if (!check_user($_user, APP__USER_TYPE_STUDENT)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------

global $group_handler;
$group_handler = new GroupHandler();

// Get a list of collections that the user is a member of

$collections = $group_handler->get_member_collections($_user->id, APP__ID, 'assessment');

$collection_ids = array_extract_column($collections, 'collection_id');
$collection_clause = $DB->build_set($collection_ids);

// Get a list of assessments that match the user's collections (for this year)

$academic_year = get_academic_year();

$start_date = mktime(0, 0, 0, APP__ACADEMIC_YEAR_START_MONTH, 1, $academic_year - 1);
$end_date = mktime(0, 0, 0, APP__ACADEMIC_YEAR_START_MONTH, 1, $academic_year + 1);

$sql_start_date = date(MYSQL_DATETIME_FORMAT, $start_date);
$sql_end_date = date(MYSQL_DATETIME_FORMAT, $end_date);

$assessments = $DB->fetch('SELECT a.* FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
                          "WHERE (a.module_id = {$_module_id}) AND " .
                                "(a.collection_id IN $collection_clause) AND " .
                                "(a.open_date >= '{$sql_start_date}') AND " .
                                "(a.open_date < '{$sql_end_date}') " .
                          'ORDER BY a.open_date, a.close_date, a.assessment_name');

// Get a list of those assessments that the user has already taken

$assessment_ids = array_extract_column($assessments, 'assessment_id');
$assessment_clause = $DB->build_set($assessment_ids);

$assessments_with_response = $DB->fetch_col("
  SELECT DISTINCT assessment_id
  FROM " . APP__DB_TABLE_PREFIX . "user_mark
  WHERE (assessment_id IN {$assessment_clause})
    AND (user_id = {$_user->id})
  ORDER BY assessment_id
");

// Split the assessments into pending, open and finished
$pending_assessments = null;
$open_assessments = null;
$finished_assessments = null;

if ($assessments) {

  foreach($assessments as $i => $assessment) {
    if ( (is_array($assessments_with_response)) && (in_array($assessment['assessment_id'], $assessments_with_response)) ) {
      $finished_assessments[] = $assessment;
    } else {
      $now = mktime();
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

$UI->page_title = APP__NAME .' '.gettext('my assessments');
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/329';
$UI->breadcrumbs = array  (
                'my assessments'      => null ,
              );


$UI->head();
$UI->body();

$UI->content_start();
?>

<p><?php echo gettext('This page lists all the assessments you are registered on in this module.'); ?></p>

<div class="content_box">

<?php
if ( (!$open_assessments) && (!$pending_assessments) && (!$finished_assessments) ) {
  echo('<p>'.gettext('You are not registered with any peer assessments in this module at the moment.').'</p>');
} else {
  echo('<p>'.gettext('You are registered on the following peer assessments in this module:').'</p>');

  if ($open_assessments) {
?>
      <h2><?php echo gettext('Open Assessments'); ?></h2>
      <p><?php echo gettext('These assessments are now open for you to take and record your group').' '.APP__MARK_TEXT; ?>.</p>
      <div class="form_section form_line">
<?php
    $status = 'open';
    $status_capitalized = ucfirst($status);

    $assessment_iterator = new SimpleObjectIterator($open_assessments,'Assessment','$DB');
    for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
      $assessment =& $assessment_iterator->current();
      $take_url = "take/index.php?a={$assessment->id}";

      echo("<div class=\"assessment_open\">");
      echo('<table class="assessment_info" cellpadding="0" cellspacing="0">');
      echo('<tr>');
      echo("  <td width=\"24\"><img src=\"../../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>");
      echo('  <td valign="top">');
      echo('    <div class="assessment_info">');
      echo("      <div class=\"assessment_name\">{$assessment->name}</div>");
      echo('      <div class="assessment_schedule">'.gettext('scheduled:'). $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>');
      echo('    </div>');
      echo('  </td>');
      echo('  <td class="buttons" style="line-height: 2em; text-align: right;">');
      echo("    <a class=\"button\" href=\"$take_url\">".gettext('Take Assessment')."</a>");
      echo('  </td>');
      echo('</tr>');
      echo('</table>');
      echo('</div>');
    }
?>
      </div>
<?php
  }
  if ($pending_assessments) {
?>
      <h2><?php echo gettext('Pending Assessments');?></h2>
      <p><?php echo gettext('These assessments scheduled for some point in the future.'); ?></p>
      <div class="form_section form_line">
<?php
    $status = 'pending';
    $status_capitalized = ucfirst($status);

    $assessment_iterator = new SimpleObjectIterator($pending_assessments,'Assessment','$DB');
    for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
      $assessment =& $assessment_iterator->current();
      $take_url = "take/index.php?a={$assessment->id}";

      echo("<div class=\"assessment\">");
      echo('<table class="assessment_info" cellpadding="0" cellspacing="0">');
      echo('<tr>');
      echo("  <td width=\"24\"><img src=\"../../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>");
      echo('  <td valign="top">');
      echo('    <div class="assessment_info">');
      echo("      <div class=\"assessment_name\">{$assessment->name}</div>");
      echo('      <div class="assessment_schedule">scheduled: '. $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>');
      echo('    </div>');
      echo('  </td>');
      echo('</tr>');
      echo('</table>');
      echo('</div>');
    }
?>
      </div>
<?php
  }

  if ($finished_assessments) {
?>
      <h2><?php echo gettext('Finished Assessments'); ?></h2>
      <p><?php echo gettext('These assessments you have already taken, or which have passed their deadline for completion.');?></p>
      <p><?php echo gettext('Some of your assessments may allow you to see feedback on your performance. Click <em>view feedback</em> (if available) for a particular assessment to see the feedback.');?></p>
      <div class="form_section">
<?php
    $status = 'finished';
    $status_capitalized = ucfirst($status);

    $now = mktime();

    $assessment_iterator = new SimpleObjectIterator($finished_assessments, 'Assessment', '$DB');
    for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
      $assessment =& $assessment_iterator->current();
      $take_url = "take/index.php?a={$assessment->id}";

      $completed_msg = ( (is_array($assessments_with_response)) && (in_array($assessment->id, $assessments_with_response)) ) ? gettext('COMPLETED'): gettext('DID NOT<br />SUBMIT');

      echo("<div class=\"assessment_finished\">");
      echo('<table class="assessment_info" cellpadding="0" cellspacing="0">');
      echo('<tr>');
      echo("  <td width=\"24\"><img src=\"../../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>");
      echo('  <td valign="top">');
      echo('    <div class="assessment_info">');
      echo("      <div class=\"assessment_name\">{$assessment->name}</div>");
      echo('      <div class="assessment_schedule">scheduled: '. $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>');
      echo('    </div>');
      echo('  </td>');
      echo('  <td style="font-weight: bold; text-align: center;">');
      echo("    $completed_msg");
      if ( ($assessment->allow_feedback) && ($assessment->close_date<$now) ) {
        echo("<div style=\"margin-top: 0.5em;\"><a href=\"assessment_feedback.php?a={$assessment->id}\" target=\"_blank\">view feedback</a></div>");
      }
      echo('  </td>');
      echo('</tr>');
      echo('</table>');
      echo('</div>');
    }
    echo("    </div>\n");
  }
}
?>

</div>

<?php

$UI->content_end();

?>
