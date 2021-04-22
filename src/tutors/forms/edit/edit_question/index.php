<?php
/**
 * WIZARD : Create a new criterion
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../../includes/inc_global.php';

use WebPA\includes\classes\Form;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$form_id = Common::fetch_GET('f', Common::fetch_POST('form_id'));
$question_id = Common::fetch_GET('q', Common::fetch_POST('question_id'));

$form = new Form($DB);
if ($form->load($form_id)) {
    $form_qs = "f={$form->id}";
} else {
    $form = null;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard('edit criterion wizard');

if ($form) {
    $wizard->set_wizard_url("index.php?f=$form_id&q=$question_id");

    $wizard->cancel_url = "../edit_form.php?f=$form_id";

    $valid_types = ['likert', 'split100'];

    if ($form->type=='split100') {
        $wizard_path = DOC__ROOT . '/tutors/forms/edit/edit_question/' . $form->type .'/';

        $wizard->add_step(1, $wizard_path.'class_wizardstep_1.php');
        $wizard->add_step(2, $wizard_path.'class_wizardstep_2.php');

        $wizard->show_steps(1); // Hide the last step from the user
    } else {
        $wizard_path = DOC__ROOT . '/tutors/forms/edit/edit_question/' . $form->type .'/';

        $wizard->add_step(1, $wizard_path.'class_wizardstep_1.php');
        $wizard->add_step(2, $wizard_path.'class_wizardstep_2.php');
        $wizard->add_step(3, $wizard_path.'class_wizardstep_3.php');

        $wizard->show_steps(2); // Hide the last step from the user
    }

    $wizard->set_var('db', $DB);
    $wizard->set_var('config', $_config);
    $wizard->set_var('user', $_user);
    $wizard->set_var('form', $form);
    $wizard->set_var('question_id', $question_id);

    $wizard->prepare();

    $wiz_step = $wizard->get_step();
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME.' Edit a criterion';
$UI->menu_selected = 'my forms';
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = [
  'home'          => '../../../',
  'my forms'        => '../../',
  "edit: {$form->name}" => "../edit_form.php?$form_qs",
  'edit: criterion'   => null,
];

$UI->set_page_bar_button('List Forms', '../../../../../images/buttons/button_form_list.gif', '../../');
$UI->set_page_bar_button('Create Form', '../../../../../images/buttons/button_form_create.gif', '../../create/index.php');
$UI->set_page_bar_button('Clone a Form', '../../../../../images/buttons/button_form_clone.gif', '../../clone/');
$UI->set_page_bar_button('Import a Form', '../../../../../images/buttons/button_form_import.gif', '../../import/');

$UI->head();
if ($form) {
    $wizard->head();
    $UI->body('onload="body_onload()"');
} else {
    $UI->body();
}
$UI->content_start();
?>

<p>This wizard takes you through the process of editing an existing assessment criterion.</p>

<?php
if ($form) {
    $wizard->title();
    $wizard->draw_errors();
}
?>

<div class="content_box">

<?php
if ($form) {
    $wizard->draw_wizard();
} else {
    echo '<p>The given assessment form failed to load so this wizard cannot be started.</p>';
}
?>

</div>

<?php

$UI->content_end();

?>
