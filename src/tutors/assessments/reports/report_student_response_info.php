<?php
/**
 * Report: Student Grades
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
use WebPA\includes\functions\TimeDiff;

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

    $getMarksheetParameterQuery =
      'SELECT marking_params ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'assessment_marking ' .
      'WHERE assessment_id = ? ' .
      'AND date_created = ? ' .
      'LIMIT 1';

    $params_xml = $DB->getConnection()->fetchOne($getMarksheetParameterQuery, [$assessment->id, $md_mysql_date], [ParameterType::STRING, ParameterType::STRING]);

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

    // Get Student Response Information
    $responseInfoQuery =
      'SELECT user_id, ip_address, comp_name, date_responded, date_opened ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'user_response ' .
      'WHERE assessment_id = ?';

    $response_info = $DB->getConnection()->fetchAllAssociativeIndexed($responseInfoQuery, [$assessment->id], [ParameterType::STRING]);
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

  -->
  </style>
<?php
  $UI->body();
    $UI->content_start(); ?>

  <div class="content_box">

  <h2 style="font-size: 150%;">Student Response Information</h2>

<?php
  if (($assessment) && ($groups_iterator->size()>0)) {
      foreach ($group_members as $group_id => $g_members) {
          ?>
      <div style="margin-top: 40px;">
        <h3><?php echo $group_names[$group_id]; ?></h3>
        <table class="grid" cellpadding="2" cellspacing="1">
        <tr>
          <th>name</th>
          <th align="center">Started</th>
          <th align="center">Finished</th>
          <th align="center">Time Taken</th>
          <th align="center">IP Address</th>
          <th align="center">Computer Name</th>
        </tr>
<?php
      foreach ($g_members as $i => $member_id) {
          if ((is_array($response_info)) && (array_key_exists($member_id, $response_info))) {
              $info = $response_info[$member_id];

              $started_dt = strtotime($info['date_opened']);
              $started = date('d M, Y \<\b\r \/\>H:i:s', $started_dt);
              $finished_dt = strtotime($info['date_responded']);
              $finished = date('d M, Y \<\b\r \/\>H:i:s', $finished_dt);

              $time_taken = TimeDiff::calculate($started_dt, $finished_dt);

              $ip_address = $info['ip_address'];
              $computer_name = $info['comp_name'];
          } else {
              $started = '';
              $finished = '';
              $time_taken = '';
              $ip_address = '';
              $computer_name = '';
          }

          $individ = $CIS->get_user($member_id);

          echo '<tr>';
          echo "<td style=\"text-align: left\"> {$individ['lastname']}, {$individ['forename']} (";
          if (!empty($individ['id_number'])) {
              echo $individ['id_number'];
          } else {
              echo $individ['username'];
          }
          echo ')</td>';
          echo "<td>$started</td>";
          echo "<td>$finished</td>";
          echo "<td>$time_taken</td>";
          echo "<td>$ip_address</td>";
          echo "<td>$computer_name</td>";
          echo '</tr>';
      } ?>
        </table>
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
    header('Content-Disposition: attachment; filename="webpa_student_response_info.csv"');
    header('Content-Type: text/csv');

    echo '"Student Response Information"'."\n\n";
    echo "\"{$assessment->name}\"\n\n";

    if (($assessment) && ($groups_iterator->size()>0)) {
        foreach ($group_members as $group_id => $g_members) {
            echo "\"Group\",\"{$group_names[$group_id]}\"\n";
            echo "\"Overall group mark\",\"{$groups_and_marks[$group_id]}\"\n";

            echo '"Name","Started","Finished","Time Taken","IP Address","Computer Name"'."\n";

            foreach ($g_members as $i => $member_id) {
                if (array_key_exists($member_id, $response_info)) {
                    $info = $response_info[$member_id];

                    $started_dt = strtotime($info['date_opened']);
                    $started = date('d M, Y H:i:s', $started_dt);
                    $finished_dt = strtotime($info['date_responded']);
                    $finished = date('d M, Y H:i:s', $finished_dt);

                    $time_taken = TimeDiff::calculate($started_dt, $finished_dt);

                    $ip_address = $info['ip_address'];
                    $computer_name = $info['comp_name'];
                } else {
                    $started = '';
                    $finished = '';
                    $time_taken = '';
                    $ip_address = '';
                    $computer_name = '';
                }
                $individ = $CIS->get_user($member_id);
                echo "\"{$individ['lastname']}, {$individ['forename']} (";
                if (!empty($individ['id_number'])) {
                    echo $individ['id_number'];
                } else {
                    echo $individ['username'];
                }
                echo ')",';
                echo "\"$started\",";
                echo "\"$finished\",";
                echo "\"$time_taken\",";
                echo "\"$ip_address\",";
                echo "\"$computer_name\"\n";
            }
            echo "\n\n";
        }
    }
}
?>
