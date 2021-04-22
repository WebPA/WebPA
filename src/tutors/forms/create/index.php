<?php
/**
 * WIZARD : Create a new form
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard('create a new form wizard');
$wizard->cancel_url = '../';

$wizard->add_step(1, 'class_wizardstep_1.php');
$wizard->add_step(2, 'class_wizardstep_2.php');

$wizard->show_steps(1); // Hide the last step from the user

$wizard->set_var('db', $DB);
$wizard->set_var('config', $_config);
$wizard->set_var('user', $_user);
$wizard->set_var('moduleId', $_module_id);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' ' .'Create a new form';
$UI->menu_selected = 'my forms';
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = [
  'home'                      => '../../',
  'my forms'                  => '../',
  'create a new form wizard'  => null,
];

$UI->set_page_bar_button('List Forms', '../../../../images/buttons/button_form_list.gif', '../');
$UI->set_page_bar_button('Create Form', '../../../../images/buttons/button_form_create.gif', '../create/');
$UI->set_page_bar_button('Clone a Form', '../../../../images/buttons/button_form_clone.gif', '../clone/');
$UI->set_page_bar_button('Import a Form', '../../../../images/buttons/button_form_import.gif', '../import/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p>This wizard takes you through the process of creating a new assessment form. When it is complete, you will be able to edit the form to add assessment criteria.</p>

<?php
$wizard->title();
$wizard->draw_errors();
?>

<div class="content_box">

<?php
  $wizard->draw_wizard();
?>

</div>

<?php

$UI->content_end();

?>
