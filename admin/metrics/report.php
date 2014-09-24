<?php
/**
 *
 * General usage report
 *
 * This page will supply a general report that provides information
 * on the usage of WebPA over the istalled period as well as over the current
 * academic year.
 *
 * @copyright 2008 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 24 Jul 2008
 *
 */

//get the include file required
require_once("../../includes/inc_global.php");
require_once("../../includes/functions/lib_university_functions.php");

if (!check_user($_user, APP__USER_TYPE_ADMIN)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

$year = (int) fetch_POST('academic_year', fetch_SESSION('year', get_academic_year()));
$_SESSION['year'] = $year;

$academic_year = strval($year);
if (APP__ACADEMIC_YEAR_START_MONTH > 1) {
  $academic_year .= '/' . substr($year + 1, 2, 2);
}

$this_year = '-';
if (APP__ACADEMIC_YEAR_START_MONTH <= 10) {
  $this_year .= '0';
}
$this_year .= APP__ACADEMIC_YEAR_START_MONTH . '-01 00:00:00';
$next_year = strval($year + 1) . $this_year;
$this_year = strval($year) . $this_year;

//get the format to be used
$format = fetch_post('format');

//get the reports that are to be generated
$assessments_run = fetch_POST('assessments_run');
$assessment_groups = fetch_POST('assessment_groups');
$assessment_students = fetch_POST('assessment_students');

// $assessment_modules = fetch_POST('assessment_modules');
$assessment_feedback = fetch_POST('assessment_feedback');
$assessment_respondents = fetch_POST('assessment_respondents');

$assessment_modules = fetch_POST('assessment_modules');
$assessment_students_thisyear = fetch_POST('assessment_students_thisyear');
// $assessment_tutor_departments = fetch_POST('assessment_tutor_departments');

$this_accademic_year = get_academic_year() . '-';
if (APP__ACADEMIC_YEAR_START_MONTH <= 10) {
  $this_accademic_year .= '0';
}
$this_accademic_year .= APP__ACADEMIC_YEAR_START_MONTH . '-01 00:00:00';

// formulate all the sql for the reports
//assessments which have been run
$run_assessments_SQL = 'SELECT a.assessment_name, m.module_code, m.module_title, a.open_date, a.close_date ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}' " .
   'ORDER BY a.close_date, a.open_date, a.assessment_name, a.assessment_id';

//number of groups per assessment
$run_groups_per_assessment_SQL = 'SELECT a.assessment_name, m.module_code, m.module_title, COUNT(g.group_id) as group_count ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group g ON a.collection_id = g.collection_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}' " .
   'GROUP BY a.assessment_name, m.module_code, m.module_title ' .
   'ORDER BY a.assessment_name';

//number of students per assessment
$run_students_per_assessment_SQL = 'SELECT a.assessment_name, m.module_code, m.module_title, COUNT(ugm.user_id) as student_count ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group g ON a.collection_id = g.collection_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ON g.group_id = ugm.group_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}' " .
   'GROUP BY a.assessment_name, m.module_code, m.module_title ' .
   'ORDER BY a.assessment_name, m.module_code';

//assessments where feedback has been used
$run_feedback_SQL = 'SELECT a.assessment_name, m.module_code, m.module_title ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}' " .
   'AND a.allow_feedback = 1 ' .
   'ORDER BY a.assessment_name, a.assessment_id';

//number of respondents per assessment
$run_respondents = 'SELECT a.assessment_name, m.module_code, m.module_title, COUNT(DISTINCT um.user_id) as response_count ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_mark um ON a.assessment_id=um.assessment_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}' " .
   'GROUP BY a.assessment_name, m.module_code, m.module_title ' .
   'ORDER BY a.assessment_name, m.module_code';

//who has run an assessment in the current academic year
$run_modules_per_assessments_SQL = 'SELECT DISTINCT m.module_code, m.module_title ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}' " .
   'ORDER BY a.assessment_name, m.module_code';

//number of students who has carried out an assessment this year
$run_students_assessed_SQL = 'SELECT COUNT(DISTINCT ugm.user_id) as \'Total unique students assessed\' ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group ug ON a.collection_id = ug.collection_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ON ug.group_id = ugm.group_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   "WHERE m.source_id = '{$_source_id}' AND a.open_date >= '{$this_year}' AND a.open_date < '{$next_year}'";

//-------------------------------------------------------
//view on screen
if($format == 'html') {
  //set the page information
  $UI->page_title = APP__NAME .' '.gettext(" general usage report");
  $UI->menu_selected = gettext('metrics');
  $UI->breadcrumbs = array ('home' => '../../');
  $UI->help_link = '?q=node/237';
  $UI->head();
  $UI->body();
  $UI->content_start();

  echo "<div class=\"content_box\">";

  if (!empty($assessments_run)) {

    $rs_assessments = $DB->fetch($run_assessments_SQL);

    echo "<h2>".gettext('Assessments run in WebPA')." ({$academic_year})</h2>";

    if ($rs_assessments) {
      echo "<table class=\"grid\">";
      $icounter = 0;

      //loop round the initial array
      foreach ($rs_assessments as $assessment) {
        if($icounter==0){
          //get an array of the key to the $assessment array
          $field_names = array_keys($assessment);

          foreach($field_names as $row){
            echo "<th>{$row}</th>";
          }
        }

        echo "<tr>";
        foreach($assessment as $row){
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
        $icounter ++;
      }
      echo "</table>";
    } else {
        echo '<p>'.gettext('None').'</p>';
    }
  }

  if(!empty($assessment_groups)){
    $rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
    echo "<h2>Number of groups per assessment ({$academic_year})</h2>";

    if ($rs_groups) {
      echo "<table class=\"grid\">";

      $icounter = 0;
      //loop round the initial array
      foreach ($rs_groups as $groups) {
        if($icounter==0){
          $field_names = array_keys($groups);

          foreach($field_names as $row){
            echo "<th>{$row}</th>";
          }
        }

        echo "<tr>";
        foreach ($groups as $row) {
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
        $icounter ++;
      }
      echo "</table>";
    } else {
        echo '<p>'.gettext('None').'</p>';
    }
  }

  if(!empty($assessment_students)){
    $rs_students = $DB->fetch($run_students_per_assessment_SQL);
    echo "<h2>".gettext('Number of students per assessment')." ({$academic_year})</h2>";

    if ($rs_students) {
      echo "<table class=\"grid\">";

      $icounter = 0;
      //loop round the initial array
      foreach ($rs_students as $students) {
        if($icounter==0){
          $field_names = array_keys($students);

          foreach ($field_names as $row) {
            echo "<th>{$row}</th>";
          }
        }
        echo "<tr>";
        foreach ($students as $row) {
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
        $icounter++;
      }
      echo "</table>";
    } else {
        echo '<p>'.gettext('None').'</p>';
    }
  }
  if(!empty($assessment_feedback)){
    $rs_feedback = $DB->fetch($run_feedback_SQL);
    echo "<h2>".gettext('Assessments where feedback has been used')." ({$academic_year})</h2>";

    if ($rs_feedback) {
      echo "<table class=\"grid\">";

      $icounter = 0;

      //loop round the initial array
      foreach($rs_feedback as $feedback){

        if($icounter==0){
          $field_names = array_keys($feedback);
          foreach($field_names as $row){
            echo"<th>{$row}</th>";
          }
        }

        echo "<tr>";
        foreach($feedback as $row){
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
        $icounter++;
      }
      echo "</table>";
    } else {
      echo '<p>'.gettext('None').'</p>';
    }
  }

  if(!empty($assessment_respondents)){
    $rs_respondents = $DB->fetch($run_respondents);
    echo "<h2>".gettext('Number of Respondents per assessment')." ({$academic_year})</h2>";

    if ($rs_respondents) {
      echo "<table class=\"grid\">";

      $icounter=0;
      //loop round the initial array
      foreach($rs_respondents as $responses){

        if($icounter == 0){
          $field_names = array_keys($responses);
          foreach($field_names as $row){
            echo"<th>{$row}</th>";
          }
        }

        echo "<tr>";
        foreach($responses as $row){
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
        $icounter++;
      }
      echo "</table>";
    } else {
        echo '<p>'.gettext('None').'</p>';
    }
  }

  if(!empty($assessment_modules)){
    $rs_runners = $DB->fetch($run_modules_per_assessments_SQL);
    echo "<h2>".gettext('Modules which have run an assessment')." ({$academic_year})</h2>";

    if ($rs_runners) {
      echo "<table class=\"grid\">";

      $icounter = 0;
      //loop round the initial array
      foreach($rs_runners as $runner){

        if($icounter==0){
          $field_names = array_keys($runner);
          foreach($field_names as $row){
            echo "<th>{$row}</th>";
          }
        }

        echo "<tr>";
        foreach($runner as $row){
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
        $icounter++;
      }
      echo "</table>";
    } else {
        echo '<p>'.gettext('None').'</p>';
    }
  }

  if(!empty($assessment_students_thisyear)){
    $rs_students = $DB->fetch($run_students_assessed_SQL);
    echo "<h2>".gettext('Number of students who have carried out an assessment')." ({$academic_year})</h2>";

    if ($rs_students) {
      echo "<table class=\"grid\">";
      //loop round the initial array
      foreach($rs_students as $student){
        echo "<tr>";
        foreach($student as $row){
          echo "<td>{$row}</td>";
        }
        echo "</tr>";
      }
      echo "</table>";
    } else {
        echo '<p>'.gettext('None').'</p>';
    }
  }

  echo "</div>";
  $UI->content_end();
}

//-------------------------------------------------------------------------------
//output csv
if ($format == 'csv') {
    header("Content-Disposition: attachment; filename=\"metrics.csv\"");
    header('Content-Type: text/csv');

    echo(gettext('"WebPA - Metrics report"')."\n");


  if (!empty($assessments_run)){
    $rs_assessments = $DB->fetch($run_assessments_SQL);
    echo "\n\"".gettext('Assessments run in WebPA')." ({$academic_year})\"\n";
    if ($rs_assessments) {
      $icounter = 0;
      //loop round the initial array
      foreach ($rs_assessments as $assessment) {
        if ($icounter==0) {
          //get an array of the key to the $assessment array
          echo "\n";
          $field_names = array_keys($assessment);
          foreach ($field_names as $row) {
            echo "\"{$row}\"".APP__SEPARATION;
          }
          echo "\n";
        }
        foreach ($assessment as $row) {
          echo "\"{$row}\"".APP__SEPARATION;
        }
        echo "\n";
        $icounter ++;
      }
    }

  }

  if (!empty($assessment_groups)) {
    $rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
    echo "\n\"".gettext('Number of groups per assessment')." ({$academic_year})\"\n";
    if ($rs_groups) {
      $icounter = 0;
      //loop round the initial array
      foreach ($rs_groups as $groups) {
        if ($icounter==0) {
          echo "\n";
          $field_names = array_keys($groups);
          foreach ($field_names as $row) {
            echo "\"{$row}\"".APP__SEPARATION;
          }
          echo "\n";
        }
        foreach ($groups as $row) {
          echo "\"{$row}\"".APP__SEPARATION;
        }
        echo "\n";
        $icounter ++;
      }
    }
  }

  if (!empty($assessment_students)) {
    $rs_students = $DB->fetch($run_students_per_assessment_SQL);
    echo "\n\"".gettext('Number of students per assessment')." ({$academic_year})\"\n";
    if ($rs_students) {
      $icounter = 0;
      //loop round the initial array
      foreach ($rs_students as $students) {
        if ($icounter==0) {
          echo "\n";
          $field_names = array_keys($students);
          foreach ($field_names as $row) {
            echo "\"{$row}\"".APP__SEPARATION;
          }
          echo "\n";
        }
        foreach ($students as $row) {
          echo "\"{$row}\"".APP__SEPARATION;
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if (!empty($assessment_feedback)) {
    $rs_feedback = $DB->fetch($run_feedback_SQL);
    echo "\n\"".gettext('Assessments where feedback has been used')." ({$academic_year})\"\n";
    if ($rs_feedback) {
      $icounter = 0;
      //loop round the initial array
      foreach ($rs_feedback as $feedback) {
        if ($icounter==0) {
          echo "\n";
          $field_names = array_keys($feedback);
          foreach ($field_names as $row) {
            echo"\"{$row}\"".APP__SEPARATION;
          }
          echo "\n";
        }
        foreach ($feedback as $row) {
          echo "\"{$row}\"".APP__SEPARATION;
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if (!empty($assessment_respondents)) {
    $rs_respondents = $DB->fetch($run_respondents);
    echo "\n\"".gettext('Number of respondents per assessment')." ({$academic_year})\"\n";
    if ($rs_respondents) {
      $icounter=0;
      //loop round the initial array
      foreach ($rs_respondents as $responses) {
        if ($icounter == 0) {
          echo "\n";
          $field_names = array_keys($responses);
          foreach ($field_names as $row) {
            echo"\"{$row}\"".APP__SEPARATION;
          }
          echo "\n";
        }
        foreach ($responses as $row) {
          echo "\"{$row}\"".APP__SEPARATION;
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if (!empty($assessment_modules)) {
    $rs_runners = $DB->fetch($run_modules_per_assessments_SQL);
    echo "\n\"".gettext('Modules which have run an assessment')." ({$academic_year})\"\n";
    if ($rs_runners) {
      $icounter = 0;
      //loop round the initial array
      foreach ($rs_runners as $runner) {
        if ($icounter==0) {
          echo "\n";
          $field_names = array_keys($runner);
          foreach ($field_names as $row) {
            echo "\"{$row}\"".APP__SEPARATION;
          }
          echo "\n";
        }
        foreach ($runner as $row) {
          echo "\"{$row}\"".APP__SEPARATION;
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if(!empty($assessment_students_thisyear)){
    $rs_students = $DB->fetch($run_students_assessed_SQL);
    echo "\n\"".gettext('Number of students who have carried out an assessment')." ({$academic_year})\"\n";
    if ($rs_students) {
      //loop round the initial array
      echo "\n";
      foreach ($rs_students as $student) {
        foreach ($student as $row) {
          echo"\"{$row}\"".APP__SEPARATION;
        }
      }
      echo"\n";
    }
  }
}

//------------------------------------------------------------------------------------
//export as rtf
if ($format == 'rtf'){
  header("Content-Disposition: attachment;filename=student_grades.rtf");
  header("Content-Type: text/enriched\n");

  echo(gettext('WebPA - Metrics report')."\n\n");

  if (!empty($assessments_run)){
    $rs_assessments = $DB->fetch($run_assessments_SQL);
    echo "\n".gettext('Assessments run in WebPA')." ({$academic_year})\n\n";
    $icounter = 0;
    //loop round the initial array
    foreach($rs_assessments as $assessment ){
      if($icounter==0){
        //get an array of the key to the $assessment array
        $field_names = array_keys($assessment);
        foreach($field_names as $row){
          echo " {$row}  ";
        }
      }
      echo "\n";
      foreach($assessment as $row){
        echo " {$row}  ";
      }
      $icounter ++;
    }

  }

  if(!empty($assessment_groups)){
    $rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
    echo "\n".gettext('Number of groups per assessment')." ({$academic_year})\n\n";
    if ($rs_groups) {
      $icounter = 0;
      //loop round the initial array
      foreach($rs_groups as $groups){
        if($icounter==0){
          $field_names = array_keys($groups);
          foreach($field_names as $row){
            echo " {$row}  ";
          }
        }
        echo "\n";
        foreach($groups as $row){
          echo " {$row}  ";
        }
        $icounter ++;
      }
    }
  }

  if(!empty($assessment_students)){
    $rs_students = $DB->fetch($run_students_per_assessment_SQL);
    echo "\n".gettext('Number of students per assessment')." ({$academic_year})\n\n";
    if ($rs_students) {
      $icounter = 0;
      //loop round the initial array
      foreach($rs_students as $students){
        if($icounter==0){
          $field_names = array_keys($students);
          foreach($field_names as $row){
            echo " {$row}  ";
          }
        }
        echo "\n";
        foreach($students as $row){
          echo " {$row}  ";
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if(!empty($assessment_feedback)){
    $rs_feedback = $DB->fetch($run_feedback_SQL);
    echo "\n".gettext('Assessments where feedback has been used')." ({$academic_year})\n\n";
    if ($rs_feedback) {
      $icounter = 0;
      //loop round the initial array
      foreach($rs_feedback as $feedback){
        if($icounter==0){
          $field_names = array_keys($feedback);
          foreach($field_names as $row){
            echo"{$row}  ";
          }
        }
        echo "\n";
        foreach($feedback as $row){
          echo "{$row}  ";
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if(!empty($assessment_respondents)){
    $rs_respondents = $DB->fetch($run_respondents);
    echo "\n".gettext('Number of Respondents per assessment')." ({$academic_year})\n\n";
    if ($rs_respondents) {
      $icounter=0;
      //loop round the initial array
      foreach($rs_respondents as $responses){
        if($icounter == 0){
          $field_names = array_keys($responses);
          foreach($field_names as $row){
            echo"{$row}  ";
          }
        }
        echo "\n";
        foreach($responses as $row){
          echo "{$row}  ";
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if(!empty($assessment_modules)){
    $rs_runners = $DB->fetch($run_modules_per_assessments_SQL);
    echo "\n".gettext('Modules which have run an assessment')." ({$academic_year})\n\n";
    if ($rs_runners) {
      $icounter = 0;
      //loop round the initial array
      foreach($rs_runners as $runner){
        if($icounter==0){
          $field_names = array_keys($runner);
          foreach($field_names as $row){
            echo "{$row}  ";
          }
        }
        echo "\n";
        foreach($runner as $row){
          echo "{$row}  ";
        }
        echo "\n";
        $icounter++;
      }
    }
  }

  if(!empty($assessment_students_thisyear)){
    $rs_students = $DB->fetch($run_students_assessed_SQL);
    echo "\n".gettext('Number of students who have carried out an assessment')." ({$academic_year})\n\n";
    if ($rs_students) {
      //loop round the initial array
      foreach($rs_students as $student){
        foreach($student as $row){
          echo"{$row}  ";
        }
      }
      echo"\n";
    }
  }
}

//------------------------------------------------------------------------------------
//export as xml
if ($format == 'xml'){
  header("Content-Disposition: attachment; file=\"webpa_metrics.xml\"");
  header('Content-Type: text/xml');

  echo("<?xml version=\"1.0\" ?> ");
  echo"<metrics_report>";

  if (!empty($assessments_run)){
    $rs_assessments = $DB->fetch($run_assessments_SQL);
    echo "<metrics>";
    echo "<description>".gettext('Assessments run in WebPA')." ({$academic_year})</description>";
    if ($rs_assessments) {
      //loop round the initial array
      foreach($rs_assessments as $assessment ){
        //get an array of the key to the $assessment array
        $field_names = array_keys($assessment);
        $field_content = array_values($assessment);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";

  }

  if(!empty($assessment_groups)){
    $rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
    echo "<metrics>";
    echo "<description>".gettext('Number of groups per assessment')." ({$academic_year})</description>";
    if ($rs_groups) {
      foreach($rs_groups as $groups ){
        //get an array of the key to the $groups array
        $field_names = array_keys($groups);
        $field_content = array_values($groups);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";
  }

  if(!empty($assessment_students)){
    $rs_students = $DB->fetch($run_students_per_assessment_SQL);
    echo "<metrics>";
    echo "<description>".gettext('Number of students per assessment')." ({$academic_year})</description>";

    if ($rs_students) {
      //loop round the initial array
      foreach($rs_students as $students){
        //get an array of the key to the $students array
        $field_names = array_keys($students);
        $field_content = array_values($students);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";
  }

  if(!empty($assessment_feedback)){
    $rs_feedback = $DB->fetch($run_feedback_SQL);
    echo "<metrics>";
    echo "<description>".gettext('Assessments where feedback has been used')." ({$academic_year})</description>";
    if ($rs_feedback) {
      //loop round the initial array
      foreach($rs_feedback as $feedback){
        //get an array of the key to the $feedback array
        $field_names = array_keys($feedback);
        $field_content = array_values($feedback);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";
  }

  if(!empty($assessment_respondents)){
    $rs_respondents = $DB->fetch($run_respondents);
    echo "<metrics>";
    echo "<description>".gettext('Number of Respondents per assessment')." ({$academic_year})</description>";
    if ($rs_respondents) {
      //loop round the initial array
      foreach($rs_respondents as $responses){
        //get an array of the key to the $responses array
        $field_names = array_keys($responses);
        $field_content = array_values($responses);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";
  }

  if(!empty($assessment_modules)){
    $rs_runners = $DB->fetch($run_modules_per_assessments_SQL);
    echo "<metrics>";
    echo "<description>".gettext('Modules which have run an assessment')." ({$academic_year})</description>";
    if ($rs_runners) {
      //loop round the initial array
      foreach($rs_runners as $runner){
        //get an array of the key to the $runner array
        $field_names = array_keys($runner);
        $field_content = array_values($runner);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";
  }

  if(!empty($assessment_students_thisyear)){
    $rs_students = $DB->fetch($run_students_assessed_SQL);
    echo "<metrics>";
    echo "<description>".gettext('Number of students who have carried out an assessment')." ({$academic_year})</description>";
    if ($rs_students) {
      //loop round the initial array
      foreach($rs_students as $student){
        //get an array of the key to the $student array
        $field_names = array_keys($student);
        $field_content = array_values($student);

        //get the number of elements in the arrays
        $array_count = count($field_names);
        echo "<metric>";
        for ($count=0; $count<$array_count; $count++){
          echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
          echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
        }
        echo "</metric>";
      }
    }
    echo "</metrics>";
  }

  echo"</metrics_report>";
}

?>
