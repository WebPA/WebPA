<?php
/**
 * Perform an action on a question
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Form;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$form_id = Common::fetch_GET('f');
$question_id = Common::fetch_GET('q');

$action = strtolower(Common::fetch_GET('a'));

// --------------------------------------------------------------------------------

$form = new Form($DB);
if ($form->load($form_id)) {
    $form_qs = "f={$form->id}";

    $question_count = (int) $form->get_question_count();

    $question = $form->get_question($question_id);

    if ($question_count>0) {
        switch ($action) {
      case 'up':
        if ($question_id>0) {
            $question_2 = $form->get_question($question_id-1);
            $form->set_question($question_id-1, $question);
            $form->set_question($question_id, $question_2);
        }
        break;
      // --------------------
      case 'down':
        $question_2 = $form->get_question($question_id+1);
        if ($question_2) {
            $form->set_question($question_id, $question_2);
            $form->set_question($question_id+1, $question);
        }
        break;
      // --------------------
      case 'clone':
        $question = $form->get_question($question_id);
        if ($question) {
            $form->add_question($question);
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
