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
        $group_names = $algorithm->get_group_names();
        $group_members = $algorithm->get_group_members();

        $intermediate_grades = $algorithm->get_intermediate_grades();
        $grades = $algorithm->get_grades();

        $penalties = $algorithm->get_penalties();
        if (!$penalties) {
            $penalties = [];
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

  <div class="content_box">

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

  <h2 style="font-size: 150%;">Student Grades (by Group)</h2>

<?php
  foreach ($group_members as $group_id => $g_members) {
      ?>
    <div style="margin-top: 40px;">
      <h3><?php echo $group_names[$group_id]; ?></h3>
      <p>Overall group mark: <?php echo $groups_and_marks[$group_id]; ?>%.</p>
      <table class="grid" cellpadding="2" cellspacing="1">
      <tr>
        <th>name</th>
        <th align="center">WebPA<br />score</th>
        <th align="center">Intermediate<br />Grade</th>
        <th align="center"><span style="font-size: 0.9em;">Non-Submission</span><br />Penalty</th>
        <th align="center">Final<br />Grade</th>
      </tr>
<?php
    foreach ($g_members as $i => $member_id) {
        $score = (array_key_exists($member_id, $webpa_scores)) ? $webpa_scores[$member_id] : '-' ;
        $score = sprintf('%01.2f', $score);

        $intermediate_grade = (array_key_exists($member_id, $intermediate_grades)) ? $intermediate_grades[$member_id] : '-' ;
        $grade = (array_key_exists($member_id, $grades)) ? $grades[$member_id] : '-' ;
        $grade = sprintf(APP__REPORT_DECIMALS, $grade);

        // If this user was penalised
        if (array_key_exists($member_id, $penalties)) {
            $penalty_str = ($penalties[$member_id]==0) ? 'no penalty' : $penalties[$member_id] ;
        } else {
            $penalty_str = '&nbsp;';
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
        echo "<td>$score</td>";
        echo "<td>$intermediate_grade</td>";
        echo "<td>$penalty_str</td>";
        echo "<td class=\"important\">$grade</td>";
        echo '</tr>';
    } ?>
      </table>
    </div>
<?php
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
    header('Content-Disposition: attachment; filename="webpa_grades_by_group.csv"');
    header('Content-Type: text/csv');

    echo '"Student Grades (by group)"'."\n\n";
    echo "\"{$assessment->name}\"\n\n";

    foreach ($group_members as $group_id => $g_members) {
        echo "\"Group\",\"{$group_names[$group_id]}\"\n";
        echo "\"Overall group mark\",\"{$groups_and_marks[$group_id]}\"\n";

        echo '"Name","WebPA score","Intermediate Grade","Non-Submission Penalty","Final Grade"'."\n";

        foreach ($g_members as $i => $member_id) {
            $score = (array_key_exists($member_id, $webpa_scores)) ? $webpa_scores[$member_id] : '-' ;
            $score = sprintf('%01.2f', $score);

            $intermediate_grade = (array_key_exists($member_id, $intermediate_grades)) ? $intermediate_grades[$member_id] : '-' ;
            $grade = (array_key_exists($member_id, $grades)) ? $grades[$member_id] : '-' ;

            // If this user was penalised
            if (array_key_exists($member_id, $penalties)) {
                $penalty_str = ($penalties[$member_id]==0) ? 'no penalty' : $penalties[$member_id] ;
            } else {
                $penalty_str = '';
            }

            $individ = $CIS->get_user($member_id);

            echo "\"{$individ['lastname']}, {$individ['forename']} (";
            if (!empty($individ['id_number'])) {
                echo $individ['id_number'];
            } else {
                echo $individ['username'];
            }
            echo ')",';
            echo "\"$score\",";
            echo "\"$intermediate_grade\",";
            echo "\"$penalty_str\",";
            echo "\"$grade\"\n";
        }
        echo "\n\n";
    }
}

/*
 * --------------------------------------------------------------------------------
 * If report type is download XML
 * --------------------------------------------------------------------------------
 */

if ($type == 'download-xml') {
    header('Content-Disposition: attachment; filename="webpa_grades_by_group.xml"');
    header('Content-Type: application/xml');

    echo "<?xml version=\"1.0\" ?>\n";

    echo "<assessment>\n";
    echo "\t<assessment_title>{$assessment->name}</assessment_title>\n";
    echo "\t<weighting>{$marking_params['weighting'] }</weighting>\n";
    echo "\t<penalty>{$marking_params['penalty']}</penalty>\n";

    foreach ($group_members as $group_id => $g_members) {
        echo "\t<group>\n";
        echo "\t\t<group_name>{$group_names[$group_id]}</group_name>\n";
        echo "\t\t<group_mark>{$groups_and_marks[$group_id]}</group_mark>\n";
        echo "\t\t<group_members>\n";

        foreach ($g_members as $i => $member_id) {
            $score = (array_key_exists($member_id, $webpa_scores)) ? $webpa_scores[$member_id] : '-' ;
            $score = sprintf('%01.2f', $score);

            $intermediate_grade = (array_key_exists($member_id, $intermediate_grades)) ? $intermediate_grades[$member_id] : '-' ;
            $grade = (array_key_exists($member_id, $grades)) ? $grades[$member_id] : '-' ;

            // If this user was penalised
            if (array_key_exists($member_id, $penalties)) {
                $penalty_str = ($penalties[$member_id]==0) ? 'no penalty' : $penalties[$member_id] ;
            } else {
                $penalty_str = '';
            }

            $individ = $CIS->get_user($member_id);

            echo "\t\t\t<student>\n";
            echo "\t\t\t\t<name>\n";
            echo "\t\t\t\t\t<forename>{$individ['forename']}</forename>\n";
            echo "\t\t\t\t\t<lastname>{$individ['lastname']}</lastname>\n";
            echo "\t\t\t\t</name>\n";
            echo "\t\t\t\t<institutional_student_number>";
            if (!empty($individ['id_number'])) {
                echo $individ['id_number'];
            } else {
                echo $individ['username'];
            }
            echo "</institutional_student_number>\n";
            echo "\t\t\t\t<webpa_score>$score</webpa_score>\n";
            echo "\t\t\t\t<intermediate_grade>$intermediate_grade</intermediate_grade>\n";
            echo "\t\t\t\t<penalty>$penalty_str</penalty>\n";
            echo "\t\t\t\t<final_grade>$grade</final_grade>\n";
            echo "\t\t\t</student>\n";
        }
        echo "\t\t</group_members>\n";
        echo "\t</group>\n";
    }
    echo "</assessment>\n";
}

/*
 * --------------------------------------------------------------------------------
 * If report type is download XML
 * --------------------------------------------------------------------------------
 */

if ($type == 'download-rtf') {
    header('Content-Disposition: attachment; filename="webpa_grades_by_group.rtf"');
    header("Content-Type: text/enriched\n");

    echo "Assessment Title: \t{$assessment->name}";
    echo "\nWeighting: \t{$marking_params['weighting'] }";
    echo "\nPenalty: \t{$marking_params['penalty']}";

    foreach ($group_members as $group_id => $g_members) {
        echo "\n\n\nGroup name: \t{$group_names[$group_id]}";
        echo "\nGroup mark: \t{$groups_and_marks[$group_id]}";

        foreach ($g_members as $i => $member_id) {
            $score = (array_key_exists($member_id, $webpa_scores)) ? $webpa_scores[$member_id] : '-' ;
            $score = sprintf('%01.2f', $score);

            $intermediate_grade = (array_key_exists($member_id, $intermediate_grades)) ? $intermediate_grades[$member_id] : '-' ;
            $grade = (array_key_exists($member_id, $grades)) ? $grades[$member_id] : '-' ;

            // If this user was penalised
            if (array_key_exists($member_id, $penalties)) {
                $penalty_str = ($penalties[$member_id]==0) ? 'no penalty' : $penalties[$member_id] ;
            } else {
                $penalty_str = '';
            }

            $individ = $CIS->get_user($member_id);

            echo "\n\n\tName:\t{$individ['forename']} {$individ['lastname']}";
            echo "\n\tInstitutional student number:\t";
            if (!empty($individ['id_number'])) {
                echo $individ['id_number'];
            } else {
                echo $individ['username'];
            }
            echo "\n\tWebpa score:\t{$score}";
            echo "\n\tIntermediate grade:\t{$intermediate_grade}";
            echo "\n\tPenalty:\t{$penalty_str}";
            echo "\n\tFinal grade:\t{$grade}";
        }
    }
}
?>
