<?php
/**
 * INDEX - Student index
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once("../includes/inc_global.php");
require_once('../includes/classes/class_group_handler.php');
require_once('../includes/classes/class_simple_object_iterator.php');
require_once('../includes/functions/lib_university_functions.php');
require_once('../includes/classes/class_assessment.php');

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

$start_date = mktime(0, 0, 0, APP__ACADEMIC_YEAR_START_MONTH, 1, $academic_year);
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

$assessments_with_response = $DB->fetch_col('SELECT DISTINCT um.assessment_id ' .
                                            'FROM ' . APP__DB_TABLE_PREFIX . 'user_mark um ' .
                                            'LEFT JOIN pa2_assessment a ON a.assessment_id = um.assessment_id ' .
                                            "WHERE (a.module_id = {$_module_id}) AND " .
                                                  "(um.assessment_id IN {$assessment_clause}) AND " .
                                                  "(um.user_id = {$_user->id}) " .
                                            'ORDER BY um.assessment_id');

// Split the assessments into pending, open and finished
$pending_assessments = null;
$open_assessments = null;
$finished_assessments = null;

if ($assessments) {

  foreach($assessments as $i => $assessment) {
    if ( (is_array($assessments_with_response)) && (in_array($assessment['assessment_id'], $assessments_with_response)) ) {
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

//------------------------------------------------
//strings to be used in the page
$getting_help = 'You will need to seek help from your tutor.';

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME;
$UI->menu_selected = 'home';
$UI->breadcrumbs = array  ('home'       => null ,
                          );
$UI->help_link = '?q=node/329';

$UI->head();
$UI->body();

$UI->content_start();
?>

<p>Welcome to WebPA, the easiest way for you to complete your peer assessment on the web.</p>

<div class="content_box">
  <?php
  if (!$open_assessments) {
    ?>
    <p>There are no assessments in this module available for you to take at the moment.</p>
    <p>To view all the assessments you are registered on, please check the <a href="assessments/">my assessments</a> section.</p>
    <?php
  } else {
    ?>
    <p>Below is a list of the assessments in this module you can take now.</p>
    <p>To view all the assessments you are registered on in this module, please check the <a href="assessments/">my assessments</a> section.</p>

    <h2>Open Assessments</h2>
    <div class="form_section">
      <?php
      $status = 'open';
      $status_capitalized = ucfirst($status);

      $assessment_iterator = new SimpleObjectIterator($open_assessments,'Assessment','$DB');
      for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
        $assessment =& $assessment_iterator->current();
        $take_url = "assessments/take/index.php?a={$assessment->id}";

        echo("<div class=\"assessment_open\">");
        echo('<table class="assessment_info" cellpadding="0" cellspacing="0">');
        echo('<tr>');
        echo("  <td width=\"24\"><img src=\"../images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>");
        echo('  <td valign="top">');
        echo('    <div class="assessment_info">');
        echo("      <div class=\"assessment_name\">{$assessment->name}</div>");
        echo('      <div class="assessment_schedule">scheduled: '. $assessment->get_date_string('open_date') .' &nbsp;-&nbsp; '. $assessment->get_date_string('close_date') . ' </div>');
        echo('    </div>');
        echo('  </td>');
        echo('  <td class="buttons" style="line-height: 2em; text-align: right;">');
        echo("    <a class=\"button\" href=\"$take_url\">Take Assessment</a>");
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
    <h2>Pending Assessments</h2>
    <p>These assessments scheduled for some point in the future.</p>
    <div class="form_section form_line">
      <?php
      $status = 'pending';
      $status_capitalized = ucfirst($status);

      $assessment_iterator = new SimpleObjectIterator($pending_assessments,'Assessment','$DB');
      for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
        $assessment =& $assessment_iterator->current();
        $take_url = "/assessments/take/index.php?a={$assessment->id}";

        echo("<div class=\"assessment\">");
        echo('<table class="assessment_info" cellpadding="0" cellspacing="0">');
        echo('<tr>');
        echo("  <td width=\"24\"><img src=\"/images/icons/{$status}_icon.gif\" alt=\"$status_capitalized\" title=\"$status_capitalized\" height=\"24\" width=\"24\" /></td>");
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
?>

</div>

<h2>Getting Help</h2>
<p><?php
  echo $getting_help;
?></p>


<?php

$UI->content_end();

?>
