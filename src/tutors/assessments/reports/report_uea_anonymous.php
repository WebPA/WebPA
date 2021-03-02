<?php
/**
 * UEA Style report
 *
 * This is the report suggested from the team using WebPA at UEA, UK
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\AlgorithmFactory;
use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\Form;
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

$marking_date = Common::fetch_GET('md');

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if (!$assessment->load($assessment_id)) {
    $assessment = null;
    echo 'Error: The requested assessment could not be loaded.';
    exit;
}

  // ----------------------------------------
    // Get the marking parameters used for the marksheet this report will display
    $marking_params = $assessment->get_marking_params($marking_date);
    if (!$marking_params) {
        echo 'Error: The requested marksheet could not be loaded.';
        exit;
    }

    // ----------------------------------------
    // Get the appropriate algorithm and calculate the grades
    $algorithm = AlgorithmFactory::get_algorithm($marking_params['algorithm']);

    if (!$algorithm) {
        echo 'Error: The requested algorithm could not be loaded.';
        exit;
    }
        $algorithm->set_assessment($assessment);
        $algorithm->set_marking_params($marking_params);
        $algorithm->calculate();

        $group_members = $algorithm->get_group_members();

        $member_names = [];

        for ($i =0; $i<count($group_members); $i++) {
            $array_key = array_keys($group_members);
            $temp = $group_members[$array_key[$i]];
            for ($j=0; $j<count($temp);$j++) {
                array_push($member_names, $CIS->get_user($temp[$j]));
            }
        }
    // /if-else(is algorithm)
// /if-else(is assessment)

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

//get the information in the format required
//get an array of the group names
$group_names = $algorithm->get_group_names();

$score_array = null;
if ($assessment) {
    foreach ($group_members as $group_id => $g_members) {
        $g_member_count = count($group_members[$group_id]);

        foreach ($questions as $question_id) {
            $q_index = $question_id+1;
            $question = $form->get_question($question_id);
            $q_text = "Q{$q_index} : {$question['text']['_data']}";


            foreach ($g_members as $i => $member_id) {
                $individ = $CIS->get_user($member_id);

                $mark_recipient = "{$individ['lastname']}, {$individ['forename']}";
                $GroupMemberNumber = 0;
                foreach ($g_members as $j => $target_member_id) {
                    $individ = $CIS->get_user($target_member_id);

                    $char = chr(65+$GroupMemberNumber);
                    $marker = "Student $char";

                    if ($assessment->assessment_type == '0') {
                        if ($member_id == $target_member_id) {
                            $score_array[$group_names[$group_id]][$mark_recipient][$q_text][$marker] = 'n/a';
                        } else {
                            $score_array[$group_names[$group_id]][$mark_recipient][$q_text][$marker] = $algorithm->get_member_response($group_id, $target_member_id, $question_id, $member_id);
                        }
                    } else {
                        $score_array[$group_names[$group_id]][$mark_recipient][$q_text][$marker] = $algorithm->get_member_response($group_id, $target_member_id, $question_id, $member_id);
                    }
                    if (is_null($score_array[$group_names[$group_id]][$mark_recipient][$q_text][$marker])) {
                        $score_array[$group_names[$group_id]][$mark_recipient][$q_text][$marker] = '-';
                    }

                    $GroupMemberNumber ++;
                }
            }
        }
    }
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

  table.grid td.important { background-color: #eec; }

  -->
  </style>
<?php
  $UI->body();
    $UI->content_start(); ?>

  <div class="content_box">

  <h1 style="font-size: 150%;">Student Responses</h1>

  <table class="grid" cellpadding="2" cellspacing="1">
  <tr>
    <td>

<?php
  $teams = array_keys($score_array);
    foreach ($teams as $i=> $team) {
        echo "<h2>{$team}</h2>";
        $team_members = array_keys($score_array[$team]);
        foreach ($team_members as $team_member) {
            echo "<h3>Results for: {$team_member}</h3>";
            $questions = array_keys($score_array[$team][$team_member]);
            //print_r($questions);
            echo "<table class='grid' cellpadding='2' cellspacing='1' style='font-size: 0.8em'>";
            $q_count = 0;
            foreach ($questions as $question) {
                $markers = array_keys($score_array[$team][$team_member][$question]);

                $markers_row = '';
                $scores_row = '';
                foreach ($markers as $marker) {
                    $markers_row =  $markers_row ."<th>{$marker}</th>";
                    $score = $score_array[$team][$team_member][$question][$marker];
                    $scores_row = $scores_row . "<td>{$score}</td>";
                }
                if ($q_count == 0) {
                    echo '<tr><th>&nbsp;</th>';
                    echo $markers_row;
                }
                echo "</tr><tr><th>{$question}</th>";
                echo $scores_row;
                $q_count++;
            }
            echo '</tr></table><br/><br/>';
        }
    } ?>

    </td>
  </tr>
  </table>
  </div>

<?php
  $UI->content_end(false, false, false);
}
/*
 * --------------------------------------------------------------------------------
 * If report type is download RTF (Rich Text Files)
 * --------------------------------------------------------------------------------
 */

if ($type == 'download-rtf') {
    header('Content-Disposition: attachment; filename=uea.rtf');
    header("Content-Type: text/enriched\n");

    $group_names = $algorithm->get_group_names();

    $teams = array_keys($score_array);
    foreach ($teams as $i=> $team) {
        echo "{$team}\n\n";
        $team_members = array_keys($score_array[$team]);
        foreach ($team_members as $team_member) {
            echo "Results for: {$team_member}\n\n";
            $questions = array_keys($score_array[$team][$team_member]);
            //print_r($questions);
            $q_count = 0;
            foreach ($questions as $question) {
                $markers = array_keys($score_array[$team][$team_member][$question]);

                $markers_row = '';
                $scores_row = '';
                foreach ($markers as $marker) {
                    $markers_row =  $markers_row ."{$marker}\t";

                    $score = $score_array[$team][$team_member][$question][$marker];
                    $scores_row = $scores_row . "{$score}\t";
                }
                if ($q_count == 0) {
                    echo "\n\t\t";
                    echo $markers_row;
                }
                echo "\n{$question}\t";
                echo $scores_row . "\t";
                $q_count++;
            }
            echo "\n\n";
        }
    }
}

if ($type == 'download-csv') {
    header('Content-Disposition: attachment; filename="uea_report_style.csv"');
    header('Content-Type: text/csv');

    $group_names = $algorithm->get_group_names();

    $teams = array_keys($score_array);
    foreach ($teams as $i=> $team) {
        echo "\n{$team}\n";
        $team_members = array_keys($score_array[$team]);
        foreach ($team_members as $team_member) {
            echo "\n\"Results for: {$team_member}\"";
            $questions = array_keys($score_array[$team][$team_member]);

            $q_count = 0;
            foreach ($questions as $question) {
                $markers = array_keys($score_array[$team][$team_member][$question]);

                $markers_row = '';
                $scores_row = '';
                foreach ($markers as $marker) {
                    $markers_row =  $markers_row . ",\"{$marker}\"";

                    $score = $score_array[$team][$team_member][$question][$marker];
                    $scores_row = $scores_row . "\"{$score}\",";
                }
                if ($q_count == 0) {
                    echo "\n";
                    echo $markers_row;
                }
                echo "\n\"{$question}\",";
                echo $scores_row;
                $q_count++;
            }
            echo "\n";
        }
    }
}
?>
