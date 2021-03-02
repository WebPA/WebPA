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

use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\NewAlgorithm;
use WebPA\includes\classes\XMLParser;
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

$command = Common::fetch_POST('command');

$qs = "a={$assessment_id}&md={$md}&tab={$tab}&y={$year}";

$list_url = "?{$qs}";

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $xml_parser = new XMLParser();

    // ----------------------------------------
    // Get the marking parameters used for the marksheet this report will display

    $marking_params['weighting']= 100;
    $marking_params['penalty'] = 0;

    // ----------------------------------------
    // Get a list of the groups, and their marks, used in this assessment

    $groups_and_marks['G1'] = 80;

    // ----------------------------------------
    // Get a list of the members who took this assessment (grouped by 'group')

    $group_members['G1'] = ['S1',
                   'S2',
                   'S3',
                   'S4', ];

    // ----------------------------------------
    // Get the questions used in this assessment

    $questions = range(0, 4);

    // ----------------------------------------
    // Get the student submissions for this assessment

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S1', 'question_id' => 0, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S1', 'question_id' => 1, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S1', 'question_id' => 2, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S1', 'question_id' => 3, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S1', 'question_id' => 4, 'score' => 4];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S2', 'question_id' => 0, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S2', 'question_id' => 1, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S2', 'question_id' => 2, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S2', 'question_id' => 3, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S2', 'question_id' => 4, 'score' => 3];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S3', 'question_id' => 0, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S3', 'question_id' => 1, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S3', 'question_id' => 2, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S3', 'question_id' => 3, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S3', 'question_id' => 4, 'score' => 2];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S4', 'question_id' => 0, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S4', 'question_id' => 1, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S4', 'question_id' => 2, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S4', 'question_id' => 3, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S1', 'marked_user_id' => 'S4', 'question_id' => 4, 'score' => 1];
    // ==========
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S1', 'question_id' => 0, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S1', 'question_id' => 1, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S1', 'question_id' => 2, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S1', 'question_id' => 3, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S1', 'question_id' => 4, 'score' => 5];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S2', 'question_id' => 0, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S2', 'question_id' => 1, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S2', 'question_id' => 2, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S2', 'question_id' => 3, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S2', 'question_id' => 4, 'score' => 4];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S3', 'question_id' => 0, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S3', 'question_id' => 1, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S3', 'question_id' => 2, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S3', 'question_id' => 3, 'score' => 2];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S3', 'question_id' => 4, 'score' => 3];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S4', 'question_id' => 0, 'score' => 3];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S4', 'question_id' => 1, 'score' => 4];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S4', 'question_id' => 2, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S4', 'question_id' => 3, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S2', 'marked_user_id' => 'S4', 'question_id' => 4, 'score' => 2];
    // ==========
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S1', 'question_id' => 0, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S1', 'question_id' => 1, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S1', 'question_id' => 2, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S1', 'question_id' => 3, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S1', 'question_id' => 4, 'score' => 1];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S2', 'question_id' => 0, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S2', 'question_id' => 1, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S2', 'question_id' => 2, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S2', 'question_id' => 3, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S2', 'question_id' => 4, 'score' => 1];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S3', 'question_id' => 0, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S3', 'question_id' => 1, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S3', 'question_id' => 2, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S3', 'question_id' => 3, 'score' => 1];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S3', 'question_id' => 4, 'score' => 1];

    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S4', 'question_id' => 0, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S4', 'question_id' => 1, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S4', 'question_id' => 2, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S4', 'question_id' => 3, 'score' => 5];
    $responses[] = ['group_id' => 'G1', 'user_id' => 'S4', 'marked_user_id' => 'S4', 'question_id' => 4, 'score' => 5];

    // ----------------------------------------
    $algorithm = new NewAlgorithm();
    $algorithm->set_marking_params($marking_params);
    $algorithm->set_groups($groups_and_marks);
    $algorithm->set_group_members($group_members);
    $algorithm->set_questions($questions);
    $algorithm->set_responses($responses);

    $algorithm->calculate();

    $webpa_scores = $algorithm->get_webpa_scores();
    $grades = $algorithm->get_grades();
    $submissions = $algorithm->get_members_submitting();

    $member_ids = array_keys($webpa_scores);

    $member_names['S1'] = ['user_id' => 'S1', 'lastname' => 'Student 1', 'forename' => '', 'id_number' => 'S1'];
    $member_names['S2'] = ['user_id' => 'S2', 'lastname' => 'Student 2', 'forename' => '', 'id_number' => 'S2'];
    $member_names['S3'] = ['user_id' => 'S3', 'lastname' => 'Student 3', 'forename' => '', 'id_number' => 'S3'];
    $member_names['S4'] = ['user_id' => 'S4', 'lastname' => 'Student 4', 'forename' => '', 'id_number' => 'S4'];
} else {
    $assessment = null;
}

// --------------------------------------------------------------------------------
// Begin Page

$page_title = ($assessment) ? "{$assessment->name}" : 'report';

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my assessments';
$UI->breadcrumbs = ['home'       => '../../../',
               'my assessments' => '../../',
               $page_title    => null, ];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');
//$UI->set_page_bar_button('Clone Assessment', 'button_assessment_clone.gif', '../clone/');

$UI->head();
?>
<style type="text/css">
<!--



-->
</style>
<?php
$UI->body();
$UI->content_start();
?>


<p>On this page you can select the different reports to view for this assessment.</p>

<div class="content_box">

<div class="nav_button_bar">
  <a href="<?php echo $list_url ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to reports list</a>
</div>

<h2 style="font-size: 150%;">Student Grades</h2>

  <table class="grid" cellpadding="2" cellspacing="1">
  <tr>
    <th>name</th>
    <th align="center">WebPA<br />score</th>
    <th align="center">Grade</th>
    <th align="center">Did not<br />submit</th>
  </tr>
<?php
foreach ($member_names as $i => $member) {
    $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
    $score = sprintf('%01.2f', $score);
    $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;
    $grade = sprintf('%01.2f', $grade);

    if ($marking_params['penalty']==0) {
        $penalty_str = (array_key_exists($member['user_id'], $submissions)) ? '&nbsp;' : 'no penalty' ;
    } else {
        $penalty_str = (array_key_exists($member['user_id'], $submissions)) ? '&nbsp;' : "-{$marking_params['penalty']}%";
    }

    echo '<tr>';
    echo "<td style=\"text-align: left\"> {$member['lastname']}, {$member['forename']} ({$member['id_number']})</td>";
    echo "<td>$score</td>";
    echo "<td>{$grade}%</td>";
    echo "<td>$penalty_str</td>";
    echo '</tr>';
}
?>
  </table>

</div>


<?php

$UI->content_end();

?>
