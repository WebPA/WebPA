<?php
/**
 * Report: Marks Awarded For Each Question (anonymous)
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
use WebPA\includes\classes\NewAlgorithm;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\classes\XMLParser;
use WebPA\includes\functions\ArrayFunctions;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = Common::fetch_GET('a');

$type = Common::fetch_GET('t', 'view');

$tab = Common::fetch_GET('tab');
$year = Common::fetch_GET('y', date('Y'));

$marking_date = (int) Common::fetch_GET('md');

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $xml_parser = new XMLParser();

    // ----------------------------------------
    // Get the marking parameters used for the marksheet this report will display

    $md_mysql_date = date(MYSQL_DATETIME_FORMAT, $marking_date);

    $getMarkingParametersQuery =
      'SELECT marking_params ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'assessment_marking ' .
      'WHERE assessment_id = ? ' .
      'AND date_created = ? ' .
      'LIMIT 1';

    $params_xml = $DB->getConnection()->fetchOne($getMarkingParametersQuery, [$assessment->id, $md_mysql_date], [ParameterType::STRING, ParameterType::STRING]);

    $params = $xml_parser->parse($params_xml);

    if (array_key_exists('parameters', $params)) {
        $marking_params['weighting'] = $params['parameters']['weighting']['_attributes']['value'];
        $marking_params['penalty'] = $params['parameters']['penalty']['_attributes']['value'];
    } else {
        $marking_params['weighting']= 100;
        $marking_params['penalty'] = 0;
    }

    // ----------------------------------------
    // Get a list of the members who took this assessment (grouped by 'group')
    $groups_and_marks = null;
    $group_members = null;
    $group_names = null;

    $group_handler = new GroupHandler();
    $collection = $group_handler->get_collection($assessment->get_collection_id());
    $groups_iterator = $collection->get_groups_iterator();
    if ($groups_iterator->size()>0) {
        for ($groups_iterator->reset(); $groups_iterator->is_valid(); $groups_iterator->next()) {
            $group =& $groups_iterator->current();
            $group_members["{$group->id}"] = $group->get_member_ids();
            $group_names["{$group->id}"] = $group->name;
            $groups_and_marks["{$group->id}"] = null;
        }
    }

    // ----------------------------------------
    // Get the questions used in this assessment
    $form = new Form($DB);
    $form_xml = $assessment->get_form_xml();
    $form->load_from_xml($form_xml);
    $question_count = (int) $form->get_question_count();

    // Create the actual array (question_ids are 0-based)
    if ($question_count>0) {
        $questions = range(0, $question_count-1);
    } else {
        $questions = [];
    }

    // ----------------------------------------
    // Get the student submissions for this assessment
    $result_handler = new ResultHandler($DB);
    $result_handler->set_assessment($assessment);

    $responses = $result_handler->get_responses();

    // ----------------------------------------
    $algorithm = new NewAlgorithm();
    $algorithm->set_marking_params($marking_params);
    $algorithm->set_groups($groups_and_marks);
    $algorithm->set_group_members($group_members);
    $algorithm->set_questions($questions);
    $algorithm->set_responses($responses);

    $algorithm->calculate();

    $webpa_scores = $algorithm->get_webpa_scores();

    $member_ids = array_keys($webpa_scores);

    $members_raw = $CIS->get_user($member_ids);

    $members = ArrayFunctions::array_get_assoc($members_raw, 'user_id');
} else {
    $assessment = null;
    echo 'Error: The assessment could not be loaded.';
    exit;
}

/*
* --------------------------------------------------------------------------------
* If report type is HTML view
* --------------------------------------------------------------------------------
*/
if ($type == 'view') {
    // Begin Page

    $page_title = ($assessment) ? "{$assessment->name}" : 'report';

    $UI->page_title = APP__NAME . ' ' . $page_title;
    $UI->head(); ?>
  <style type="text/css">
  <!--

  #side_bar { display: none; }
  #main { margin: 0px; }

  table.grid th { padding: 8px; }
  table.grid td { padding: 8px; text-align: center; }

  table.grid tr.q_total th { text-align: center; }

  -->
  </style>
<?php
  $UI->body();
    $UI->content_start(); ?>

  <div class="content_box">

  <h2 style="font-size: 150%;">Marks Awarded For Each Question (anonymous)</h2>

<?php
  if (($assessment) && ($groups_iterator->size()>0)) {
      foreach ($group_members as $group_id => $g_members) {
          $g_member_count = count($group_members[$group_id]); ?>
      <div style="margin-top: 40px; page-break-after: always;">
        <h3><?php echo $group_names[$group_id]; ?></h3>

<?php
      foreach ($questions as $question_id) {
          $q_index = $question_id+1;
          $question = $form->get_question($question_id);
          echo "<p>Q{$q_index} : {$question['text']['_data']}"; ?>
          <?= array_key_exists('range', $question) ? "(range: {$question['range']['_data']})" : '' ?>
          </p>
          <table class="grid" cellpadding="2" cellspacing="1" style="font-size: 0.8em">
          <tr>
            <th>&nbsp;</th>
<?php
        foreach ($g_members as $i => $member_id) {
            $char = chr(65+$i);
            $members[$member_id]['lastname'] = "Student $char";
            echo "<th class=\"top_names\"> {$members[$member_id]['lastname']}</th>";
        } ?>
          </tr>
<?php
        $q_total = [];
          foreach ($g_members as $i => $member_id) {
              $q_total[$member_id] = 0;
          }

          foreach ($g_members as $i => $member_id) {
              echo '<tr>';
              echo "<th>{$members[$member_id]['lastname']}</th>";

              foreach ($g_members as $j => $target_member_id) {
                  if ($assessment->assessment_type == '0') {
                      if ($member_id == $target_member_id) {
                          $score = 'n/a';
                      } else {
                          $score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
                      }
                  } else {
                      $score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
                  }

                  $q_total[$target_member_id] += (int) $score;
                  if (is_null($score)) {
                      $score = '-';
                  }
                  echo "<td>$score</td>";
              }
              echo '</tr>';
          } ?>
          <tr class="q_total">
            <th>Score Received</th>
<?php
        foreach ($g_members as $i => $member_id) {
            echo "<th>{$q_total[$member_id]}</th>";
        } ?>
          </tr>
          </table>
<?php
      } ?>
      </div>
<?php
      }
  } ?>

  </div>

<?php
  $UI->content_end(false, false, false);
}

/*
* --------------------------------------------------------------------------------
* If report type is download CSV
* --------------------------------------------------------------------------------
*/
if ($type == 'download-csv') {
    header('Content-Disposition: attachment; filename="webpa_marks_awarded_byquestion.csv"');
    header('Content-Type: text/csv');

    echo '"Marks Awarded For Each Question (anonymous)"'."\n\n";
    echo "\"{$assessment->name}\"\n\n";

    if (($assessment) && ($groups_iterator->size()>0)) {
        foreach ($group_members as $group_id => $g_members) {
            $g_member_count = count($group_members[$group_id]);

            echo "\"{$group_names[$group_id]}\"\n";

            foreach ($questions as $question_id) {
                $q_index = $question_id+1;
                $question = $form->get_question($question_id);

                echo "\n";
                echo "\"Q{$q_index} : {$question['text']['_data']}";

                if (array_key_exists('range', $question)) {
                    echo "range: {$question['range']['_data']})\"\n";
                } else {
                    echo "\"\n";
                }

                echo '"",';

                foreach ($g_members as $i => $member_id) {
                    $char = chr(65+$i);
                    $members[$member_id]['lastname'] = "Student $char";

                    echo "\"{$members[$member_id]['lastname']}\"";
                    if ($i<$g_member_count) {
                        echo ',';
                    }
                }

                echo "\n";

                foreach ($g_members as $i => $member_id) {
                    echo "\"{$members[$member_id]['lastname']}\",";

                    foreach ($g_members as $j => $target_member_id) {
                        if ($assessment->assessment_type == '0') {
                            if ($member_id == $target_member_id) {
                                $score = 'n/a';
                            } else {
                                $score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
                            }
                        } else {
                            $score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
                        }

                        // there is a chance that q total won't have been set if a student has not answered any questions
                        $q_total = $q_total ?? [];

                        if (count($q_total) > 0) {
                            $q_total[$target_member_id] += (int) $score;
                        }

                        if (is_null($score)) {
                            $score = '-';
                        }

                        echo "\"$score\"";
                        if ($j<$g_member_count) {
                            echo ',';
                        }
                    }
                    echo "\n";
                }
            }
            echo "\n\n";
        }
    }
}
?>
