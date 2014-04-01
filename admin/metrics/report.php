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
 require_once("../../include/inc_global.php");
 require_once("../../library/functions/lib_university_functions.php");
 
if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

//get the format to be used
$format = fetch_post('format');

//get the reports that are to be generated
$assessments_run = fetch_POST('assessments_run');
$assessment_groups = fetch_POST('assessment_groups');
$assessment_students = fetch_POST('assessment_students');
	
$assessment_modules = fetch_POST('assessment_modules');
$assessment_feedback = fetch_POST('assessment_feedback');
$assessment_respondents = fetch_POST('assessment_respondents');
	
$assessment_tutors_thisyear = fetch_POST('assessment_tutors_thisyear');
$assessment_students_thisyear = fetch_POST('assessment_students_thisyear');
$assessment_tutor_departments = fetch_POST('assessment_tutor_departments');

$this_accademic_year = get_academic_year();

// formulate all the sql for the reports
//assessments which have been run
$run_assessments_SQL = "SELECT assessment_name, open_date, close_date, CONCAT(u.forename,' ',u.lastname) as academic, u.username AS academic
FROM assessment a LEFT JOIN user u ON a.owner_id=u.user_id
ORDER BY assessment_id ASC";

//number of groups per assessment
$run_groups_per_assessment_SQL = "SELECT assessment_name, COUNT(group_id) as group_count
FROM assessment a LEFT JOIN user_group g ON a.collection_id=g.collection_id
GROUP BY assessment_id, assessment_name
ORDER BY assessment_id ASC";

//number of students per assessment
$run_students_per_assessment_SQL = "SELECT  assessment_name, COUNT(user_id) as student_count
FROM assessment a LEFT JOIN user_group_member g ON a.collection_id=g.collection_id
GROUP BY assessment_id, assessment_name
ORDER BY assessment_id ASC";

//number of modules per assessment
$run_modules_per_assessments_SQL = "SELECT assessment_name, module.module_code, module_title
FROM assessment LEFT JOIN user_collection_module ON assessment.collection_id=user_collection_module.collection_id
LEFT JOIN module ON module.module_code=user_collection_module.module_id
ORDER BY assessment_id ASC";

//assessments where feedback has been used
$run_feedback_SQL = "SELECT a.assessment_name, u.forename, u.lastname, u.email, u.username FROM assessment a
INNER JOIN user u ON a.owner_id=u.user_id
where allow_feedback = '1'
ORDER BY u.lastname, u.forename";

//number of respondents per assessment
$run_respondents = "SELECT assessment_name, COUNT(DISTINCT um.user_id) as response_count
FROM assessment a
	LEFT JOIN user_mark um ON a.assessment_id=um.assessment_id
GROUP BY a.assessment_id, assessment_name
ORDER BY a.assessment_id ASC";

//who has run an assessment in the current academic year
$run_assessment_runners_SQL = "SELECT DISTINCT u.username, u.forename, u.lastname, u.email
FROM assessment a
	INNER JOIN user u ON a.owner_id=u.user_id
WHERE a.open_date>'{$this_accademic_year}-09-01 00:00:00'
ORDER BY u.username";

//number of students who has carried out an assessment this year 
$run_students_assessed_SQL = "SELECT COUNT(DISTINCT user_id) as 'Total unique students assessed'
FROM pa.assessment a
	LEFT JOIN user_group_member g ON a.collection_id=g.collection_id
WHERE a.open_date>'{$this_accademic_year}-09-01 00:00:00'";

//accademics and department who have run an assessment
$run_academics_departments_SQL = "SELECT DISTINCT u.username, u.department_id
FROM pa.assessment a
	INNER JOIN user u ON a.owner_id=u.user_id
  WHERE a.open_date>'{$this_accademic_year}-09-01 00:00:00'
ORDER BY u.username";


//-------------------------------------------------------
//view on screen
if($format == 'html'){
  //set the page information
$UI->page_title = APP__NAME . " general usage report";
$UI->menu_selected = 'metrics';
$UI->breadcrumbs = array ('home' => '../../');
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();

echo "<div class=\"content_box\">";

if (!empty($assessments_run)){
	$rs_assessments = $DB->fetch($run_assessments_SQL);
	
	echo "<h2>Assessments run in WebPA</h2>";
	echo "<table class=\"grid\">";
	
	$icounter = 0;
	//loop round the initial array
	foreach($rs_assessments as $assessment ){
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
	echo "</table><br/><br/>";
}

if(!empty($assessment_groups)){
	$rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
	echo "<h2>Number of groups per assessment</h2>";
	
	echo "<table class=\"grid\">";
	
	$icounter = 0;
	//loop round the initial array
	foreach($rs_groups as $groups){
		if($icounter==0){
			$field_names = array_keys($groups);
			
			foreach($field_names as $row){
				echo "<th>{$row}</th>";
			}
		}
	
		echo "<tr>";
		foreach($groups as $row){
			echo "<td>{$row}</td>";
		}
		echo "</tr>";
		$icounter ++;
	}
	echo "</table><br/><br/>";
}

if(!empty($assessment_students)){
	$rs_students = $DB->fetch($run_students_per_assessment_SQL);
	echo "<h2>Number of students per assessment</h2>";
	
	echo "<table class=\"grid\">";
	
	$icounter = 0;
	//loop round the initial array
	foreach($rs_students as $students){
		if($icounter==0){
			$field_names = array_keys($students);
			
			foreach($field_names as $row){
				echo "<th>{$row}</th>";
			}
		}
		echo "<tr>";
		foreach($students as $row){
			echo "<td>{$row}</td>";
		}
		echo "</tr>";
		$icounter++;
	}
	echo "</table><br/><br/>";
}

if(!empty($assessment_modules)){
	$rs_modules = $DB->fetch($run_modules_per_assessments_SQL);
	echo "<h2>Number of modules per assessment</h2>";
	
	echo "<table class=\"grid\">";
	
	$icounter = 0;
	//loop round the initial array
	foreach($rs_modules as $modules){
				
		if($icounter==0){
			$field_names = array_keys($modules);
			
			foreach($field_names as $row){
				echo "<th>{$row}</th>";
			}
		}
		
		echo "<tr>";
		
		foreach($modules as $row){
			echo "<td>{$row}</td>";
		}
		echo "</tr>";
		$icounter++;
	}
	echo "</table><br/><br/>";
}

if(!empty($assessment_feedback)){
	$rs_feedback = $DB->fetch($run_feedback_SQL);
	echo "<h2>Assessments where feedback has been used</h2>";
	
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
	echo "</table><br/><br/>";
}

if(!empty($assessment_respondents)){
	$respondents = $DB->fetch($run_respondents);
	echo "<h2>Number of Respondents per assessment</h2>";
	
	echo "<table class=\"grid\">";
	
	$icounter=0;
	//loop round the initial array
	foreach($respondents as $responses){
		
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
	echo "</table><br/><br/>";
}



if(!empty($assessment_tutors_thisyear)){
	$runners = $DB->fetch($run_assessment_runners_SQL);
	echo "<h2>Tutors who have run asn assessment this year</h2>";
	
	echo "<table class=\"grid\">";
	
	$icounter = 0;
	//loop round the initial array
	foreach($runners as $runner){
		
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
	echo "</table><br/><br/>";
}


if(!empty($assessment_students_thisyear)){
	$students = $DB->fetch($run_students_assessed_SQL);
	echo "<h2>Number of students who have carried out an assessment this year</h2>";
	
	echo "<table class=\"grid\">";
	//loop round the initial array
	foreach($students as $student){
		echo "<tr>";
		foreach($student as $row){
			echo "<td>{$row}</td>";
		}
		echo "</tr>";
	}
	echo "</table><br/><br/>";
}

if (!empty($assessment_tutor_departments)){
	$departments = $DB->fetch($run_academics_departments_SQL);
	echo "<h2>Tutors their departments for assessments run this year</h2>";
	
	echo "<table class=\"grid\">";
	
	$icounter=0;
	
	//loop round the initial array
	foreach($departments as $tutors){
		
		if($icounter==0){
			$field_names = array_keys($tutors);
			foreach($field_names as $row){
				echo"<th>{$row}</td>";
			}
		}
		
		echo "<tr>";
		foreach($tutors as $row){
			echo "<td>{$row}</td>";
		}
		echo "</tr>";
		$icounter++;
	}
	echo "</table><br/><br/>";
}

echo "</div>";
$UI->content_end();
}
//-------------------------------------------------------------------------------
//output csv
if ($format == 'csv') {
		header("Content-Disposition: attachment; filename=\"metrics.csv\"");
		header('Content-Type: text/csv');
		
		echo('"WebPA - Metrix report"'."\n\n");
		
		
	if (!empty($assessments_run)){
		$rs_assessments = $DB->fetch($run_assessments_SQL);	
		echo "Assessments run in WebPA\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($rs_assessments as $assessment ){
			if($icounter==0){
				//get an array of the key to the $assessment array
				$field_names = array_keys($assessment);			
				foreach($field_names as $row){
					echo "\"{$row}\",";
				}	
			}
			echo "\n";
			foreach($assessment as $row){
				echo "\"{$row}\",";
			}
			$icounter ++;
		}
	
	}
	
	if(!empty($assessment_groups)){
		$rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
		echo "Number of groups per assessment\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($rs_groups as $groups){
			if($icounter==0){
				$field_names = array_keys($groups);			
				foreach($field_names as $row){
					echo "\"{$row}\",";
				}
			}	
			echo "\n";
			foreach($groups as $row){
				echo "\"{$row}\",";
			}
			$icounter ++;
		}
	}
	
	if(!empty($assessment_students)){
		$rs_students = $DB->fetch($run_students_per_assessment_SQL);
		echo "Number of students per assessment\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($rs_students as $students){
			if($icounter==0){
				$field_names = array_keys($students);			
				foreach($field_names as $row){
					echo "\"{$row}\",";
				}
			}
			echo "\n";
			foreach($students as $row){
				echo "\"{$row}\",";
			}
			echo "\n";
			$icounter++;
		}
	}
	
	if(!empty($assessment_modules)){
		$rs_modules = $DB->fetch($run_modules_per_assessments_SQL);
		echo "Number of modules per assessment\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($rs_modules as $modules){				
			if($icounter==0){
				$field_names = array_keys($modules);			
				foreach($field_names as $row){
					echo "\"{$row}\",";
				}
			}		
			echo "\n";
			foreach($modules as $row){
				echo "\"{$row}\",";
			}
			echo "\n";
			$icounter++;
		}
	}
	
	if(!empty($assessment_feedback)){
		$rs_feedback = $DB->fetch($run_feedback_SQL);
		echo "Assessments where feedback has been used\n\n";
		$icounter = 0;	
		//loop round the initial array
		foreach($rs_feedback as $feedback){		
			if($icounter==0){
				$field_names = array_keys($feedback);
				foreach($field_names as $row){
					echo"\"{$row}\",";
				}
			}		
			echo "\n";
			foreach($feedback as $row){
				echo "\"{$row}\",";
			}
			echo "\n";
			$icounter++;
		}
	}
	
	if(!empty($assessment_respondents)){
		$respondents = $DB->fetch($run_respondents);
		echo "Number of Respondents per assessment\n\n";	
		$icounter=0;
		//loop round the initial array
		foreach($respondents as $responses){		
			if($icounter == 0){
				$field_names = array_keys($responses);
				foreach($field_names as $row){
					echo"\"{$row}\",";
				}
			}		
			echo "\n";
			foreach($responses as $row){
				echo "\"{$row}\",";
			}
			echo "\n";
			$icounter++;
		}
	}
	
	if(!empty($assessment_tutors_thisyear)){
		$runners = $DB->fetch($run_assessment_runners_SQL);
		echo "Tutors who have run asn assessment this year\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($runners as $runner){		
			if($icounter==0){
				$field_names = array_keys($runner);
				foreach($field_names as $row){
					echo "\"{$row}\",";
				}
			}		
			echo "\n";
			foreach($runner as $row){
				echo "\"{$row}\",";
			}
			echo "\n";
			$icounter++;
		}
	}
	
	if(!empty($assessment_students_thisyear)){
		$students = $DB->fetch($run_students_assessed_SQL);
		echo "Number of students who have carried out an assessment this year\n\n";
		//loop round the initial array
		foreach($students as $student){
			foreach($student as $row){
				echo"\"{$row}\",";
			}
		}
		echo"\n";
	}
	
	if (!empty($assessment_tutor_departments)){
		$departments = $DB->fetch($run_academics_departments_SQL);
		echo "Tutors their departments for assessments run this year\n\n";		
		$icounter=0;	
		//loop round the initial array
		foreach($departments as $tutors){		
			if($icounter==0){
				$field_names = array_keys($tutors);
				foreach($field_names as $row){
					echo"\"{$row}\",";
				}
				echo"\n";
			}		
			foreach($tutors as $row){
				echo "\"{$row}\",";
			}
			echo"\n";		
			$icounter++;
		}
	}	
}
//------------------------------------------------------------------------------------
//export as rtf
if ($format == 'rtf'){
	header("Content-Disposition: attachment;filename=student_grades.rtf");
	header("Content-Type: text/enriched\n");
	
			echo('WebPA - Metrix report'."\n\n");
		
		
	if (!empty($assessments_run)){
		$rs_assessments = $DB->fetch($run_assessments_SQL);	
		echo "Assessments run in WebPA\n\n";	
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
		echo "Number of groups per assessment\n\n";	
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
	
	if(!empty($assessment_students)){
		$rs_students = $DB->fetch($run_students_per_assessment_SQL);
		echo "Number of students per assessment\n\n";	
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
	
	if(!empty($assessment_modules)){
		$rs_modules = $DB->fetch($run_modules_per_assessments_SQL);
		echo "Number of modules per assessment\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($rs_modules as $modules){				
			if($icounter==0){
				$field_names = array_keys($modules);			
				foreach($field_names as $row){
					echo " {$row}  ";
				}
			}		
			echo "\n";
			foreach($modules as $row){
				echo " {$row}  ";
			}
			echo "\n";
			$icounter++;
		}
	}
	
	if(!empty($assessment_feedback)){
		$rs_feedback = $DB->fetch($run_feedback_SQL);
		echo "Assessments where feedback has been used\n\n";
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
	
	if(!empty($assessment_respondents)){
		$respondents = $DB->fetch($run_respondents);
		echo "Number of Respondents per assessment\n\n";	
		$icounter=0;
		//loop round the initial array
		foreach($respondents as $responses){		
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
	
	if(!empty($assessment_tutors_thisyear)){
		$runners = $DB->fetch($run_assessment_runners_SQL);
		echo "Tutors who have run asn assessment this year\n\n";	
		$icounter = 0;
		//loop round the initial array
		foreach($runners as $runner){		
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
	
	if(!empty($assessment_students_thisyear)){
		$students = $DB->fetch($run_students_assessed_SQL);
		echo "Number of students who have carried out an assessment this year\n\n";
		//loop round the initial array
		foreach($students as $student){
			foreach($student as $row){
				echo"{$row}  ";
			}
		}
		echo"\n";
	}
	
	if (!empty($assessment_tutor_departments)){
		$departments = $DB->fetch($run_academics_departments_SQL);
		echo "Tutors their departments for assessments run this year\n\n";		
		$icounter=0;	
		//loop round the initial array
		foreach($departments as $tutors){		
			if($icounter==0){
				$field_names = array_keys($tutors);
				foreach($field_names as $row){
					echo"{$row}  ";
				}
				echo"\n";
			}		
			foreach($tutors as $row){
				echo "{$row}  ";
			}
			echo"\n";		
			$icounter++;
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
		echo "<description>Assessments run in WebPA</description>";	
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
		echo "</metrics>";
	
	}
	
	if(!empty($assessment_groups)){
		$rs_groups = $DB->fetch($run_groups_per_assessment_SQL);
		echo "<metrics>";
		echo "<description>Number of groups per assessment</description>";	
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
		echo "</metrics>";
	}
	
	if(!empty($assessment_students)){
		$rs_students = $DB->fetch($run_students_per_assessment_SQL);
		echo "<metrics>";
		echo "<description>Number of students per assessment</description>";	
	
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
		echo "</metrics>";
	}
	
	if(!empty($assessment_modules)){
		$rs_modules = $DB->fetch($run_modules_per_assessments_SQL);
		echo "<metrics>";
		echo "<description>Number of modules per assessment</description>";	
		//loop round the initial array
		foreach($rs_modules as $modules){				
			//get an array of the key to the $modules array
			$field_names = array_keys($modules);	
			$field_content = array_values($modules);
					
			//get the number of elements in the arrays
			$array_count = count($field_names);
			echo "<metric>";
			for ($count=0; $count<$array_count; $count++){
				echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
				echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
			}
			echo "</metric>";
		}
		echo "</metrics>";
	}
	
	if(!empty($assessment_feedback)){
		$rs_feedback = $DB->fetch($run_feedback_SQL);
		echo "<metrics>";
		echo "<description>Assessments where feedback has been used</description>";	
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
		echo "</metrics>";
	}
	
	if(!empty($assessment_respondents)){
		$respondents = $DB->fetch($run_respondents);
		echo "<metrics>";
		echo "<description>Number of Respondents per assessment</description>";	
		//loop round the initial array
		foreach($respondents as $responses){		
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
		echo "</metrics>";
	}
	
	if(!empty($assessment_tutors_thisyear)){
		$runners = $DB->fetch($run_assessment_runners_SQL);
		echo "<metrics>";
		echo "<description>Tutors who have run asn assessment this year</description>";	
		//loop round the initial array
		foreach($runners as $runner){		
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
		echo "</metrics>";
	}
	
	if(!empty($assessment_students_thisyear)){
		$students = $DB->fetch($run_students_assessed_SQL);
		echo "<metrics>";
		echo "<description>Number of students who have carried out an assessment this year</description>";
		//loop round the initial array
		foreach($students as $student){
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
		echo "</metrics>";
	}
	
	if (!empty($assessment_tutor_departments)){
		$departments = $DB->fetch($run_academics_departments_SQL);
		echo "<metrics>";
		echo "<description>Tutors their departments for assessments run this year</description>";		
		$icounter=0;	
		//loop round the initial array
		foreach($departments as $tutors){		
			//get an array of the key to the $tutors array
			$field_names = array_keys($tutors);	
			$field_content = array_values($tutors);
					
			//get the number of elements in the arrays
			$array_count = count($field_names);
			echo "<metric>";
			for ($count=0; $count<$array_count; $count++){
				echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
				echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
			}
			echo "</metric>";
		}
		echo "</metrics>";
	}
	echo"</metrics_report>";
}	

?>