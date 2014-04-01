<?php
/**
 * 
 * Perform an action on a question
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */

require_once("../../../include/inc_global.php");
require_once(DOC__ROOT . '/include/classes/class_form.php');

if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST


$form_id = fetch_GET('f');
$question_id = fetch_GET('q');

$action = strtolower( fetch_GET('a') );


// --------------------------------------------------------------------------------

$form =& new Form($DB);
if ($form->load($form_id)) {
	$form_qs = "f={$form->id}";

	$question_count = (int) $form->get_question_count();

	$question = $form->get_question($question_id);


	if ($question_count>0) {
		switch ($action) {
			case 'up':
						if ($question_id>0) {
							$question_2 = $form->get_question($question_id-1);
							$form->set_question($question_id-1,$question);
							$form->set_question($question_id,$question_2);
						}
						break;
			// --------------------
			case 'down':
						$question_2 = $form->get_question($question_id+1);
						if ($question_2) {
							$form->set_question($question_id,$question_2);
							$form->set_question($question_id+1,$question);
						}
						break;
			// --------------------
			case 'delete':
						if ($question) {
							$form->remove_question($question_id);
						}
						break;
			// --------------------
		}// /switch
		
		$form->save();
	}
} else {
	$form_qs = '';
}


header('Location: '. APP__WWW ."/tutors/forms/edit/edit_form.php?{$form_qs}#questions");
exit;
?>