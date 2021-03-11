<?php
/**
 * WIZARD : Create Mark Sheet For Assessment
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Assessment;
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

$command = Common::fetch_POST('command');

$list_url = "../index.php?tab={$tab}&y={$year}";
$done_list_url = "../index.php?tab=marked&y={$year}";

$prev_url = $list_url;

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $assessment_qs = "a={$assessment->id}&tab={$tab}&y={$year}";

    $group_handler = new GroupHandler();
    $collection = $group_handler->get_collection($assessment->get_collection_id());

    $result_handler = new ResultHandler($DB);
    $result_handler->set_assessment($assessment);
} else {
    $assessment = null;
}

$min_penalty = 0;
$max_penalty = 100;

$min_tolerance = 1;
$max_tolerance = 20;

// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if (($command) && ($assessment)) {
    switch ($command) {
    case 'save':
      // Create Mark Sheet
      $weighting = Common::fetch_POST('pa_weighting', null);
      $weighting = (is_numeric($weighting)) ? (int) $weighting : null;
      if ((is_null($weighting)) || ($weighting<=0) || ($weighting>100)) {
          $errors[] = 'The PA Weighting must be a number between 1 - 100';
      }

      $penalty = Common::fetch_POST('pa_penalty', null);
      $penalty = (is_numeric($penalty)) ? (int) $penalty : null;
      if ((is_null($penalty)) || ($penalty<$min_penalty) || ($penalty>$max_penalty)) {
          $errors[] = "The Non-completion Penalty must be a number between $min_penalty - $max_penalty";
      }

      $penalty_type = Common::fetch_POST('pa_penalty_type', null);
      if ($penalty_type!='pp') {
          $penalty_type = '%';
      }

      /*
       * // @todo : implement tolerances and show to users clearly.
       * The world is not ready for tolerance filtering.
       * It changes the final grades in subtle ways.
       * Until we have a way of clearly showing the effects to end users, tolerances are not used in the algorithms.
       */
      //$tolerance = (int) Common::fetch_POST('pa_tolerance', null);
      //$tolerance = (is_numeric($tolerance)) ? (int) $tolerance : null;

      $grading = Common::fetch_POST('pa_grading', null);
      $grading = ($grading=='grade_af') ? $grading : 'numeric';

      $algorithm = Common::fetch_POST('pa_algorithm', null);
      $valid_algs = ['pets', 'webpa'];
      if (!in_array($algorithm, $valid_algs)) {
          $algorithm = 'webpa';
      }

      // If there were no errors, save the changes
      if (!$errors) {
          $xml_parser = new XMLParser();

          $xml_array['parameters']['weighting']['_attributes'] = ['value'  => $weighting];
          $xml_array['parameters']['penalty']['_attributes'] = ['value'  => $penalty];
          $xml_array['parameters']['penalty_type']['_attributes'] = ['value' => $penalty_type];

          //$xml_array['parameters']['tolerance']['_attributes'] = array ( 'value'  => $tolerance );
          $xml_array['parameters']['grading']['_attributes'] = ['value'  => $grading];
          $xml_array['parameters']['algorithm']['_attributes'] = ['value'  => $algorithm];

          $mysql_now = date(MYSQL_DATETIME_FORMAT, time());

          $queryBuilder = $DB->getConnection()->createQueryBuilder();

          $queryBuilder
            ->insert(APP__DB_TABLE_PREFIX . 'assessment_marking')
            ->values([
                'assessment_id'     => '?',
                'date_created'      => '?',
                'date_last_marked'  => '?',
                'marking_params'    => '?',
            ])
            ->setParameter(0, $assessment_id)
            ->setParameter(1, $mysql_now)
            ->setParameter(2, $mysql_now)
            ->setParameter(3, $xml_parser->generate_xml($xml_array));

          $queryBuilder->execute();

          header("Location: $list_url");
          exit;
      }
      break;
    // --------------------
  }// /switch
}

// --------------------------------------------------------------------------------
// Begin Page

$page_title = 'create mark sheet';

$UI->page_title = $page_title;
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = [
  'home'        => '../../',
  'my assessments'  => '../',
  'create mark sheet' => null,
];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
?>
<style type="text/css">
<!--



-->
</style>
<script language="JavaScript" type="text/javascript">
<!--

  function do_command(com) {
    switch (com) {
      default :
            document.mark_form.command.value = com;
            document.mark_form.submit();
    }
  }// /do_command()

  function open_close(id) {
    id = document.getElementById(id);

      if (id.style.display == 'block' || id.style.display == '')
          id.style.display = 'none';
      else
          id.style.display = 'block';

      return;
  }
//-->
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'No changes have been saved. Please check the details in the form, and try again.');

?>

<p>On this page you can setup the parameters for the WebPA scoring algorithm.</p>
<p>You can repeat this process many times, creating several mark sheets for the same assessment but using different parameters.</p>

<div class="content_box">

<?php
if (!$assessment) {
    ?>
  <div class="nav_button_bar">
    <a href="<?php echo $list_url ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a>
  </div>

  <p>The assessment you selected could not be loaded for some reason - please go back and try again.</p>
<?php
} else {
        ?>

  <form action="mark_assessment.php?<?php echo $assessment_qs; ?>" method="post" name="mark_form">
  <input type="hidden" name="command" value="none" />

  <div class="nav_button_bar">
    <a href="<?php echo $list_url; ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessment list</a>
  </div>


  <h2>Algorithm Parameters</h2>
  <div class="form_section">
    <p>Enter the parameters to use below. When you've made your changes, click the <em>create mark sheet</em> button.</p>

    <br />

    <p>What percentage of the group's total mark should be peer-assessed?</p>
    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="pa_weighting">PA Weighting</label></th>
      <td>
        <select name="pa_weighting" id="pa_weighting">
<?php
  for ($i=5; $i<=100; $i=$i+5) {
      $selected = ($i==50) ? 'selected="selected"' : '' ;
      echo "<option value=\"$i\" $selected> {$i}% </option>\n";
  } ?>
        </select>
      </td>
    </tr>
    </table>

    <br />
    <p>If a student has not submitted any marks for the assessment, how much should they be penalised?</p>
    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="pa_penalty">Penalty for non-completion</label></th>
      <td>
        <select name="pa_penalty" id="pa_penalty">
<?php
  for ($i=$min_penalty; $i<=$max_penalty; $i++) {
      $selected = ($i==0) ? 'selected="selected"' : '' ;
      echo "<option value=\"$i\" $selected> {$i}% </option>\n";
  } ?>
        </select>
      </td>
      <td>of their final grade.</td>
    </tr>
    </table>
    <br />

    <div style="float:right"><b>Advanced Options</b> <a href="#" onclick="open_close('advanced')"><img src="../../../images/icons/advanced_options.gif" alt="view / hide advanced options"></a></div>

    <div id="advanced" style="display:none;" class="advanced_options">

      <br />
      <br />
      <p>What type of penalty do you want to apply?</p>
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="pa_penalty_type">Penalty Type</label></th>
        <td>
          <select name="pa_penalty_type" id="pa_penalty_type">
            <option value="%" selected="selected"> Percentage (%) </option>
            <option value="pp"> Percentage Points (pp) </option>
          </select>
        </td>
      </tr>
      </table>

<?php
      /*
       * // @todo : implement tolerances and show to users clearly.
       * The world is not ready for tolerance filtering.
       * It changes the final grades in subtle ways.
       * Until we have a way of clearly showing the effects to end users, tolerances are not used in the algorithms.
       */
      /*
      <br />
      <p>To mitigate the effects of individual students in a group marking others too harshly, or too generously, WebPA can filter out student responses that fall too far from the group average.</p>
      <p>To clarify, if Student A marks Student C more harshly than the average for the group, then Student A's marks to Student C would be removed, and the other marks received by the others in the group would be adjusted upwards accordingly.</p>

      <p>What tolerance level do you wish to use to filter and reject out-lying scores?</p>
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="pa_tolerance">Tolerance Level (+/-)</label></th>
        <td>
          <select name="pa_tolerance" id="pa_tolerance">
            <option value="none"> None - Include all scores </option>
            <?php
              for($i=$min_tolerance; $i<=$max_tolerance; $i++) {
                $selected = ($i==0) ? 'selected="selected"' : '' ;
                echo("<option value=\"$i\" $selected> {$i}% </option>\n");
              }
            ?>
          </select>
        </td>
      </tr>
      </table>
      */
?>

      <br />
      <p>How do you want student grades to be displayed?</p>
      <p>A-F Grades will be calculated using the levels defined by your WebPA administrator.</p>
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="pa_grading">Grading Type</label></th>
        <td>
          <select name="pa_grading" id="pa_grading">
            <option value="numeric"> Percentage Grades </option>
            <option value="grade_af"> Alphabetic Grades (A-F) </option>
          </select>
        </td>
      </tr>
      </table>


      <br />
      <p>Which algorithm do you wish to use to generate the student's grades?</p>
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <td><input type="radio" name="pa_algorithm" id="alg_webpa" value="webpa" checked="checked" /></td>
        <td style="padding-bottom: 8px;"><label for="alg_webpa">WebPA Algorithm (default)
          <br /><span style="font-weight: normal;">This is the usual, WebPA way of calculating grades.
          <br />The marks awarded to each student are normalised based on the generosity of the student doing the marking.</span></label>
          <br />
        </td>
      </tr>
      <?php
      // @todo : There appear to be grading problems with the PETS algorithm in peer-only mode.
      // Until we have conducted a full investigation and nailed down what's happening
      // this algorithm is disabled.
      if (false) {
          ?>
	      <tr>
	        <td><input type="radio" name="pa_algorithm" id="alg_pets" value="pets" /></td>
	        <td style="padding-bottom: 8px;"><label for="alg_pets">PETS Algorithm
	          <br /><span style="font-weight: normal;">The marks awarded to each student are not normalised.
	          <br />This algorithm, together with the Split100 style criteria, are recommended by the Pro-actively Ensuring Team Success (PETS) process, produced by Lydia Kavanagh at the University of Queensland, Australia.</span></label>
	        </td>
	      </tr>
	      <?php
      } ?>
      </table>

    </div>

    <br /><br />

    <div style="text-align: center">
      <input type="button" name="savebutton1" id="savebutton1" value="create mark sheet" onclick="do_command('save');" />
    </div>
  </div>

  </form>
<?php
    }
?>
</div>

<?php

$UI->content_end();

?>
