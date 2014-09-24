<?php
/**
 * Report: Student Grades
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 2.0.0.0
 *
 * Edited 2009-05-18 to also display group and comments
 *
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/functions/lib_array_functions.php');
require_once(DOC__ROOT . 'includes/classes/class_assessment.php');
require_once(DOC__ROOT . 'includes/classes/class_algorithm_factory.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = fetch_GET('a');

$type = fetch_GET('t', 'view');

$tab = fetch_GET('tab');
$year = fetch_GET('y', date('Y'));

$marking_date = (int) fetch_GET('md');

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if (!$assessment->load($assessment_id)) {
  $assessment = null;
  echo(gettext('Error: The requested assessment could not be loaded.'));
  exit;
} else {

  // ----------------------------------------
  // Get the marking parameters used for the marksheet this report will display
  $marking_params = $assessment->get_marking_params($marking_date);

  if (!$marking_params) {
    echo(gettext('Error: The requested marksheet could not be loaded.'));
    exit;
  }

  // ----------------------------------------
  // Get a list of the groups, and their marks, used in this assessment
  $groups_and_marks = $assessment->get_group_marks();

  // ----------------------------------------
  // Get the appropriate algorithm and calculate the grades
  $algorithm = AlgorithmFactory::get_algorithm($marking_params['algorithm']);

  if (!$algorithm) {
    echo(gettext('Error: The requested algorithm could not be loaded.'));
    exit;
  } else {
    $algorithm->set_grade_ordinals($ordinal_scale);
    $algorithm->set_assessment($assessment);
    $algorithm->set_marking_params($marking_params);
    $algorithm->calculate();

    $submissions = $algorithm->get_submitters();
    $webpa_scores = $algorithm->get_webpa_scores();

    $intermediate_grades = $algorithm->get_intermediate_grades();
    $grades = $algorithm->get_grades();

    $penalties = $algorithm->get_penalties();
    if (!$penalties) { $penalties = array(); }

    $group_names = $algorithm->get_group_names();
    $group_members = $algorithm->get_group_members();
    $member_ids = array_keys($webpa_scores);

    $member_names = array();

    for ($i =0; $i<count($group_members); $i++){
      $array_key = array_keys($group_members);
      $temp = $group_members[$array_key[$i]];
      for ($j=0; $j<count($temp);$j++){
        array_push($member_names, $CIS->get_user($temp[$j]));
      }
    }
  }// /if-else(is algorithm)

  //------------------------------------------------------------
  //get the feedback / Justification
  $sql = "SELECT * FROM " . APP__DB_TABLE_PREFIX . "user_justification WHERE assessment_id = '{$assessment->id}'";

  $fetch_comments = $DB->fetch($sql);

  $feedback = null;

  foreach ($fetch_comments as $i => $comment){
    $marked_id = $comment['marked_user_id'];
    $feedback []  = array('marked_id' => $marked_id, 'feedback' => $comment['justification_text']);
  }

}// /if-else(is assessment)

/*
 * --------------------------------------------------------------------------------
 * If report type is download CSV
 * --------------------------------------------------------------------------------
 */

if ($type == 'download-csv') {
  header("Content-Disposition: attachment; filename=\"webpa_student_grades_comments.csv\"");
  header('Content-Type: text/csv');

  echo(gettext('"Student Grades and Feedback (by student)"')."\n\n");
  echo("\"{$assessment->name}\"\n\n");

  echo(gettext('"User Id"'.APP__SEPARATION.'"WebPA score"'.APP__SEPARATION.'"Intermediate Grade"'.APP__SEPARATION.'"Non-Submission Penalty"'.APP__SEPARATION.'"Final grade"'.APP__SEPARATION.'"Group"'.APP__SEPARATION.'"Comments"')."\n");

  foreach ($member_names as $i => $member) {
    $score = (array_key_exists($member['user_id'], $webpa_scores)) ? $webpa_scores["{$member['user_id']}"] : '-' ;
    $score = sprintf('%01.2f', $score);

    $intermediate_grade = (array_key_exists($member['user_id'], $intermediate_grades)) ? $intermediate_grades["{$member['user_id']}"] : '-' ;
    $grade = (array_key_exists($member['user_id'], $grades)) ? $grades["{$member['user_id']}"] : '-' ;

    // If this user was penalised
    if (array_key_exists($member['user_id'], $penalties)) {
      $penalty_str = ($penalties[$member['user_id']]==0) ? gettext('no penalty') : $penalties[$member['user_id']] ;
    } else {
      $penalty_str = '';
    }

    //print user id and grades
    echo('"');
    if (!empty($member['id_number'])) {
      echo($member['id_number']);
    } else {
      echo($member['username']);
    }
    echo("\"".APP__SEPARATION."\"$score\"".APP__SEPARATION."\"$intermediate_grade\"".APP__SEPARATION."\"$penalty_str\"".APP__SEPARATION."\"$grade\"".APP__SEPARATION);

    //print member's group name
    foreach ($group_members as $group_id => $g_members) {
      foreach ($g_members as $k => $member_id) {
        if ($member['user_id'] == $g_members[$k]) {
          echo("\"{$group_names[$group_id]}\"".APP__SEPARATION);
        }
      }
    }

    echo("\"");

    //print out feedback for each user
    foreach ($feedback as $j) {
      if ($j['marked_id'] == $member['user_id']) {
        echo("{$j['feedback']}\n\n");
      }
    }
    echo("\"\n");
  }
}

?>
