<?php
/**
 *
 *  WIZARD : Clone an existing form
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/classes/class_form.php');
require_once(DOC__ROOT . 'includes/classes/class_wizard.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard(gettext('clone an existing form wizard'));
$wizard->cancel_url = "../";

$wizard->add_step(1,'class_wizardstep_1.php');
$wizard->add_step(2,'class_wizardstep_2.php');
$wizard->add_step(3,'class_wizardstep_3.php');

$wizard->show_steps(2); // Hide the last step from the user

$wizard->set_var('db',$DB);
$wizard->set_var('user',$_user);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' '.gettext('Clone an existing form');
$UI->menu_selected = gettext('my forms');
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = array  (
  'home'          => '../../' ,
    gettext('my forms')        => '../' ,
    gettext('clone a form wizard') => null ,
);

$UI->set_page_bar_button(gettext('List Forms'), '../../../../images/buttons/button_form_list.gif', '../');
$UI->set_page_bar_button(gettext('Create Form'), '../../../../images/buttons/button_form_create.gif', '../create/');
$UI->set_page_bar_button(gettext('Clone a Form'), '../../../../images/buttons/button_form_clone.gif', '../clone/');
$UI->set_page_bar_button(gettext('Import a Form'), '../../../../images/buttons/button_form_import.gif', '../import/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p><?php echo gettext('This wizard takes you through the process of cloning an existing assessment form. When it is complete, you will have a new copy of the form, which you can then edit normally.');?></p>

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
