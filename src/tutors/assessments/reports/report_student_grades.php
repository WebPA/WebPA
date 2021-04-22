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

use WebPA\includes\classes\AlgorithmFactory;
use WebPA\includes\classes\Assessment;
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
    // Get a list of the groups, and their marks, used in this assessment
    $groups_and_marks = $assessment->get_group_marks();

    // ----------------------------------------
    // Get the appropriate algorithm and calculate the grades
    $algorithm = AlgorithmFactory::get_algorithm($marking_params['algorithm']);

    if (!$algorithm) {
        echo 'Error: The requested algorithm could not be loaded.';
        exit;
    }
        $algorithm->set_grade_ordinals($ordinal_scale);
        $algorithm->set_assessment($assessment);
        $algorithm->set_marking_params($marking_params);
        $algorithm->calculate();

        $submissions = $algorithm->get_submitters();
        $webpa_scores = $algorithm->get_webpa_scores();

        $intermediate_grades = $algorithm->get_intermediate_grades();
        $grades = $algorithm->get_grades();

        $penalties = $algorithm->get_penalties();
        if (!$penalties) {
            $penalties = [];
        }

        $group_members = $algorithm->get_group_members();
        $member_ids = array_keys($webpa_scores);

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

<?php
  if ($marking_params) {
      $penalty_type = ($marking_params['penalty_type']=='pp') ? ' pp' : '%' ;   // Add a space to the 'pp'.?>
    <p style="margin-bottom: 2em; padding-left: 1em; font-size: 0.8em;">
      (
      Algorithm: <?php echo $marking_params['algorithm']; ?>. &nbsp;

      Weighting: <?php echo $marking_params['weighting']; ?>%. &nbsp;

      Penalty: <?php echo $marking_params['penalty'].$penalty_type; ?>. &nbsp;

      Grading: <?php
        if ($marking_params['grading']=='grade_af') {
            echo 'A-F.';
        } else {
            echo 'Numeric (%).';
        } ?>
      )
    </p>
<?php
  } ?>

  <h2 style="font-size: 150%;">Student Grades</h2>

  <table class="grid" cellpadding="2" cellspacing="1">
  <tr>
    <th>name</th>
    <th align="center">WebPA<br />score</th>
    <th align="center">Intermediate<br />Grade</th>
    <th align="center"><span style="font-size: 0.9em;">Non-Submission</span><br />Penalty</th>
    <th align="center">Final<br />Grade</th>
  </tr>
<?php
  foreach ($member_names as $i => $member) {
      $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
      $score = sprintf('%01.2f', $score);

      $intermediate_grade = (array_key_exists($member['user_id'], $intermediate_grades)) ? $intermediate_grades["{$member['user_id']}"] : '-' ;
      $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;
      $grade = sprintf(APP__REPORT_DECIMALS, $grade);

      // If this user was penalised
      if (array_key_exists($member['user_id'], $penalties)) {
          $penalty_str = ($penalties[$member['user_id']]==0) ? 'no penalty' : $penalties[$member['user_id']] ;
      } else {
          $penalty_str = '&nbsp;';
      }

      echo '<tr>';
      echo "<td style=\"text-align: left\"> {$member['lastname']}, {$member['forename']} (";
      if (!empty($member['id_number'])) {
          echo $member['id_number'];
      } else {
          echo $member['username'];
      }
      echo ')</td>';
      echo "<td>$score</td>";
      echo "<td>$intermediate_grade</td>";
      echo "<td>$penalty_str</td>";
      echo "<td class=\"important\">$grade</td>";
      echo '</tr>';
  } ?>
  </table>

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
    header('Content-Disposition: attachment; filename="webpa_student_grades.csv"');
    header('Content-Type: text/csv');

    echo '"Student Grades (by student)"'."\n\n";
    echo "\"{$assessment->name}\"\n\n";

    echo '"name","WebPA score","Intermediate Grade","Non-Submission Penalty","Final grade"'."\n";

    foreach ($member_names as $i => $member) {
        $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
        $score = sprintf('%01.2f', $score);

        $intermediate_grade = (array_key_exists($member['user_id'], $intermediate_grades)) ? $intermediate_grades["{$member['user_id']}"] : '-' ;
        $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;

        // If this user was penalised
        if (array_key_exists($member['user_id'], $penalties)) {
            $penalty_str = ($penalties[$member['user_id']]==0) ? 'no penalty' : $penalties[$member['user_id']] ;
        } else {
            $penalty_str = '';
        }

        echo "\"{$member['lastname']}, {$member['forename']} (";
        if (!empty($member['id_number'])) {
            echo $member['id_number'];
        } else {
            echo $member['username'];
        }
        echo ")\",\"$score\",\"$intermediate_grade\",\"$penalty_str\",\"$grade\"\n";
    }
}

/*
 * --------------------------------------------------------------------------------
 * If report type is download XML
 * --------------------------------------------------------------------------------
 */

if ($type == 'download-xml') {
    header('Content-Disposition: attachment; filename="webpa_student_grades.xml"');
    header('Content-Type: application/xml');

    echo "<?xml version=\"1.0\" ?>\n";

    echo "<assessment>\n";
    echo "\t<assessment_title>{$assessment->name}</assessment_title>\n";
    echo "\t<weighting>{$marking_params['weighting'] }</weighting>\n";
    echo "\t<penalty>{$marking_params['penalty']}</penalty>\n";

    foreach ($member_names as $i => $member) {
        $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
        $score = sprintf('%01.2f', $score);

        $intermediate_grade = (array_key_exists($member['user_id'], $intermediate_grades)) ? $intermediate_grades["{$member['user_id']}"] : '-' ;
        $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;

        // If this user was penalised
        if (array_key_exists($member['user_id'], $penalties)) {
            $penalty_str = ($penalties[$member['user_id']]==0) ? 'no penalty' : $penalties[$member['user_id']] ;
        } else {
            $penalty_str = '';
        }

        echo "\t<student>\n";
        echo "\t\t<name>\n";
        echo "\t\t\t<forename>{$member['forename']}</forename>\n";
        echo "\t\t\t<lastname>{$member['lastname']}</lastname>\n";
        echo "\t\t</name>\n";
        echo "\t\t<institutional_student_number>";
        if (!empty($member['id_number'])) {
            echo $member['id_number'];
        } else {
            echo $member['username'];
        }
        echo "</institutional_student_number>\n";
        echo "\t\t<webpa_score>{$score}</webpa_score>\n";
        echo "\t\t<intermediate_grade>{$intermediate_grade}</intermediate_grade>\n";
        echo "\t\t<penalty>{$penalty_str}</penalty>\n";
        echo "\t\t<final_grade>{$grade}</final_grade>\n";
        echo "\t</student>\n";
    }

    echo "</assessment>\n";
}

/*
 * --------------------------------------------------------------------------------
 * If report type is download XML for a moodle grade book import
 *
 * Further information on the Moodle grade book can be found at
 * http://docs.moodle.org/en/Development:Grades
 * --------------------------------------------------------------------------------
 */
if ($type == 'download-moodle-xml') {
    header('Content-Disposition: attachment; filename="webpa_student_grades.xml"');
    header('Content-Type: text/xml');

    //create an id number which is the assessment and date for the unique upload number required
    $tempAssessmentId = $assessment->id;
    $tempAssessmentId = $tempAssessmentId . date('Ymd');
    ;

    echo '<?xml version="1.0" ?> ';

    echo "<results batch=\"[{$tempAssessmentId}]\">";

    foreach ($member_names as $i => $member) {
        $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
        $score = sprintf('%01.2f', $score);

        $intermediate_grade = (array_key_exists($member['user_id'], $intermediate_grades)) ? $intermediate_grades["{$member['user_id']}"] : '-' ;
        $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;

        // If this user was penalised
        if (array_key_exists($member['user_id'], $penalties)) {
            $penalty_str = ($penalties[$member['user_id']]==0) ? 'no penalty' : $penalties[$member['user_id']] ;
        } else {
            $penalty_str = '';
        }

        echo '<result>';
        echo "<state>['new']</state>";
        echo "<assignment>{$assessment->id}</assignment>";
        ;
        echo '<student>';
        if (!empty($member['id_number'])) {
            echo $member['id_number'];
        } else {
            echo $member['username'];
        }
        echo '</student>';
        echo "<score>{$grade}</score>";
        echo '</result>';
    }

    echo '</results>';
}

/*
 * --------------------------------------------------------------------------------
 * If report type is download RTF (Rich Text Files)
 * --------------------------------------------------------------------------------
 */

if ($type == 'download-rtf') {
    header('Content-Disposition: attachment; filename=student_grades.rtf');
    header("Content-Type: text/enriched\n");

    echo "Assessment Name:\t{$assessment->name}\nWeighting:\t{$marking_params['weighting']}";
    echo "\nPenalty:\t{$marking_params['penalty']}\n\n";


    foreach ($member_names as $i => $member) {
        $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
        $score = sprintf('%01.2f', $score);

        $intermediate_grade = (array_key_exists($member['user_id'], $intermediate_grades)) ? $intermediate_grades["{$member['user_id']}"] : '-' ;
        $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;

        // If this user was penalised
        if (array_key_exists($member['user_id'], $penalties)) {
            $penalty_str = ($penalties[$member['user_id']]==0) ? 'no penalty' : $penalties[$member['user_id']] ;
        } else {
            $penalty_str = '';
        }

        echo "\nName:\t{$member['forename']} {$member['lastname']}";
        echo "\nStudent Number:\t";
        if (!empty($member['id_number'])) {
            echo $member['id_number'];
        } else {
            echo $member['username'];
        }
        echo "\nWebpa Score:\t{$score}";
        echo "\nIntermediate grade:\t{$intermediate_grade}";
        echo "\nPenalty:\t{$penalty_str}";
        echo "\nFinal Grade:\t{$grade}\n";
    }
}

?>
