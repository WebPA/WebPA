<?php
/**
 * Report: Marks Awarded For Each Question
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 */


require_once("../../../include/inc_global.php");
require_once(DOC__ROOT . '/library/functions/lib_array_functions.php');
require_once(DOC__ROOT . '/include/classes/class_assessment.php');
require_once(DOC__ROOT . '/include/classes/class_algorithm_factory.php');



if (!check_user($_user, 'staff')){
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


$assessment =& new Assessment($DB);
if (!$assessment->load($assessment_id)) {
	$assessment = null;
	
	echo('Error: The requested assessment could not be loaded.');
	exit;
} else {
	
	// ----------------------------------------	
	// Get the marking parameters used for the marksheet this report will display
	$marking_params = $assessment->get_marking_params($marking_date);
	
	if (!$marking_params) {
		echo('Error: The requested marksheet could not be loaded.');
		exit;
	}
	
	
	// ----------------------------------------	
	// Get a list of the groups, and their marks, used in this assessment
	$groups_and_marks = $assessment->get_group_marks();

	
	// ----------------------------------------
	// Get the appropriate algorithm and calculate the grades
	$algorithm = AlgorithmFactory::get_algorithm($marking_params['algorithm']);
	
	if (!$algorithm) {
		echo('Error: The requested algorithm could not be loaded.');
		exit;
	} else {
		$algorithm->set_grade_ordinals($ordinal_scale);
		$algorithm->set_assessment($assessment);
		$algorithm->set_marking_params($marking_params);
		$algorithm->calculate();

		$questions = $algorithm->get_questions();
		$submissions = $algorithm->get_submitters();
		$webpa_scores = $algorithm->get_webpa_scores();
		
		$intermediate_grades = $algorithm->get_intermediate_grades();
		$grades = $algorithm->get_grades();

		$penalties = $algorithm->get_penalties();
		if (!$penalties) { $penalties = array(); }
		
		$group_names = $algorithm->get_group_names();
		$group_members = $algorithm->get_group_members();
		$member_ids = array_keys($webpa_scores);
	}// /if-else(is algorithm)
	
}// /if-else(is assessment)



/*
 * --------------------------------------------------------------------------------
 * If report type is HTML view
 * --------------------------------------------------------------------------------
 */

if ($type == 'view') {
	// Begin Page

	$page_title = ($assessment) ? "{$assessment->name}" : 'report';


	$UI->page_title = APP__NAME . ' ' . $page_title;
	$UI->head();
	?>
	<style type="text/css">
	<!-- 

	#side_bar { display: none; }
	#main { margin: 0px; }

	table.grid th { padding: 8px; }
	table.grid td { padding: 8px; text-align: center; }

	table.grid th.top_names { writing-mode: tb-rl; filter: flipv fliph; }
	
	table.grid tr.q_total th { text-align: center; }
	
	-->
	</style>
	<?php
	$UI->content_start();
	?>

	<div class="content_box">

	<h2 style="font-size: 150%;">Marks Awarded For Each Question</h2>

	<?php
	foreach($group_members as $group_id => $g_members) {
		$g_member_count = count($group_members[$group_id]);
		?>
		<div style="margin-top: 40px; page-break-after: always;">
			<h3><?php echo($group_names[$group_id]); ?></h3>
			<?php
			foreach($questions as $question_id => $question) {
				$q_index = $question_id+1;

				echo("<p>Q{$q_index} : {$question['text']['_data']} (range: {$question['range']['_data']})</p>");
				?>
				<table class="grid" cellpadding="2" cellspacing="1" style="font-size: 0.8em">
				<tr>	
					<th>&nbsp;</th>
					<?php
					
					foreach($g_members as $i => $member_id) {
						$individ = $CIS->get_user($member_id);
						echo("<th class=\"top_names\"> {$individ['surname']}, {$individ['forename']}<br />({$individ['institutional_reference']})</th>");
					}
					?>
				</tr>
				<?php
				$q_total = array();
			
				foreach($g_members as $i => $member_id) {
					$q_total[$member_id] = 0;
				}
		
				foreach($g_members as $i => $member_id) {
					$individ = $CIS->get_user($member_id);
					echo('<tr>');
					echo("<th>{$individ['surname']}, {$individ['forename']}<br />({$individ['institutional_reference']})</th>");

					foreach($g_members as $j => $target_member_id) {	
						if ($assessment->assessment_type == '0'){
						
							if ($member_id == $target_member_id){
								$score = 'n/a';
							}else{
							
								$score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id); 
							}
						}else{
							$score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
						}

						$q_total[$target_member_id] += (int) $score;
						if (is_null($score)) { $score = '-'; }
						echo("<td>$score</td>");
					}
					echo('</tr>');

				}
				?>
				<tr class="q_total">
					<th>Score Received</th>
				<?php
				foreach($g_members as $i => $member_id) {
					echo("<th>{$q_total[$member_id]}</th>");
				}
				?>
				</tr>
				</table>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>	

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
	header("Content-Disposition: attachment; filename=\"webpa_marks_awarded_byquestion.csv\"");
	header('Content-Type: text/csv');

	echo('"Marks Awarded For Each Question"'."\n\n");
	echo("\"{$assessment->name}\"\n\n");
	
	foreach($group_members as $group_id => $g_members) {
		$g_member_count = count($group_members[$group_id]);

		echo("\"{$group_names[$group_id]}\"\n");
			
		foreach($questions as $question_id => $question) {
			$q_index = $question_id+1;

			echo("\n");
			echo("\"Q{$q_index} : {$question['text']['_data']} (range: {$question['range']['_data']})\"\n");

			echo("\"\",");
	
			foreach($g_members as $i => $member_id) {
				$individ = $CIS->get_user($member_id);
				echo("\"{$individ['surname']}, {$individ['forename']} ({$individ['institutional_reference']})\"");
				if ($i<$g_member_count) { echo(','); }
			}
			
			echo("\n");
			
			foreach($g_members as $i => $member_id) {
				$individ = $CIS->get_user($member_id);
				
				echo("\"{$individ['surname']}, {$individ['forename']} ({$individ['institutional_reference']})\",");
				
				foreach($g_members as $j => $target_member_id) {	
					if ($assessment->assessment_type == '0'){
						if ($member_id == $target_member_id){
							$score = 'n/a';
						}else{
							$score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
						}
					}else{
						$score = $algorithm->get_member_response($group_id, $member_id, $question_id, $target_member_id);
					}
									
					$q_total[$target_member_id] += (int) $score;
					if (is_null($score)) { $score = '-'; }
	
					echo("\"$score\"");
					if ($j<$g_member_count) { echo(','); }
				}
				echo("\n");
			}
		}
		echo("\n\n");
	}
}
?>