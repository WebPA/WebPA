<?php
/**
 * 
 *  INC: List Open Assessments
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 * To be used from the assessments index page
 * 
 * @param int $year e.g. 2005
 * @param mixed $academic_year e.g. 2005/06
 * @param string $tab eg pending
 * @param string $qs ="tab={$tab}&y={$year}";
 * @param string $page_url "/tutors/assessment/";
 * 
 */
?>

<h2>Open assessments for <?php echo($academic_year); ?></h2>

<p>These assessments are now open and available for students to take.</p>
<hr />

<?php
// --------------------------------------------------------------------------------
// Get and organise assessments

// get the assessment that are open
$assessments = $DB->fetch(	"SELECT *
							FROM assessment
							WHERE owner_id = '{$_user->id}'
								AND open_date>='{$year}-09-01 00:00:00'
								AND open_date<='{$next_year}-08-31 23:59:59'
								AND open_date<NOW() AND close_date>NOW()
							ORDER BY open_date, close_date, assessment_name");

if (!$assessments) {
	?>
	<p>You do not have any assessments in this category.</p>
	<p>Please choose another category from the tabs above, or <a href="/tutors/assessments/create/">create a new assessment</a>.</p>
	<?php
} else {
	?>
	<div class="obj_list">
	<?php
	// prefetch response counts for each assessment
	$result_handler =& new ResultHandler($DB);
	$responses = $result_handler->get_responses_count_for_user($_user->id, $year);
	$members = $result_handler->get_members_count_for_user($_user->id, $year);
	
	// loop through and display all the assessments
	$assessment_iterator =& new SimpleObjectIterator($assessments,'Assessment','$DB');
	for ($assessment_iterator->reset(); $assessment_iterator->is_valid(); $assessment_iterator->next()) {
		$assessment =& $assessment_iterator->current();

		$num_responses = (array_key_exists($assessment->id, $responses)) ? $responses[$assessment->id] : 0 ;
		$num_members =  (array_key_exists($assessment->id, $members)) ? $members[$assessment->id] : 0 ;
		$completed_msg = ($num_responses==$num_members) ? '- <strong>COMPLETED</strong>' : '';

		$edit_url = "edit/edit_assessment.php?a={$assessment->id}&{$qs}";
		$email_url = "email/index.php?a={$assessment->id}&{$qs}";
		$responded_url = "students_who_responded.php?a={$assessment->id}&{$qs}";
		$groupmark_url = "marks/set_group_marks.php?a={$assessment->id}&{$qs}";
		$delete_marks_url = "delete_marks.php?a={$assessment->id}&{$qs}";
		?>
		<div class="obj">
			<table class="obj" cellpadding="2" cellspacing="2">
			<tr>
				<td class="icon" width="24"><img src="../../images/icons/open_icon.gif" alt="Open" title="Open" height="24" width="24" /></td>
				<td class="obj_info">
					<div class="obj_name"><?php echo($assessment->name); ?></div>
					<div class="obj_info_text">scheduled: <?php echo($assessment->get_date_string('open_date')); ?> &nbsp;-&nbsp; <?php echo($assessment->get_date_string('close_date')); ?></div>
					<div class="obj_info_text">student responses: <?php echo("$num_responses / $num_members $completed_msg"); ?></div>
				</td>
				<td class="buttons">
					<a href="<?php echo($edit_url); ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="Edit" title="Edit assessment" /></a>
					<a href="<?php echo($email_url); ?>"><img src="../../images/buttons/email.gif" width="16" height="16" alt="Email" title="Email students" /></a>
					<a href="<?php echo($responded_url); ?>"><img src="../../images/buttons/students_responded.gif" width="16" height="16" alt="Students responded" title="Check which students have responded" /></a>
					<a href="<?php echo($groupmark_url); ?>"><img src="../../images/buttons/group_marks.gif" width="16" height="16" alt="Group Marks" title="Set group marks" /></a>
					<a href="<?php echo($delete_marks_url); ?>"><img src="../../images/icons/group_delete.png" width="16" height="16" alt="Delete individual marks" title="Delete individual marks" /></a>
				</td>
			</tr>
			</table>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
