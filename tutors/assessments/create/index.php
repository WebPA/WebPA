<?php
/**
 *
 * WIZARD : Create a new Assessment
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.2
 *
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/classes/class_wizard.php');
require_once(DOC__ROOT . 'includes/functions/lib_form_functions.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard(gettext('create a new assessment wizard'));
$wizard->cancel_url = "../";

$wizard->add_step(1,'class_wizardstep_1.php');
$wizard->add_step(2,'class_wizardstep_2.php');
$wizard->add_step(3,'class_wizardstep_3.php');
$wizard->add_step(4,'class_wizardstep_4.php');
$wizard->add_step(5,'class_wizardstep_5.php');
$wizard->add_step(6,'class_wizardstep_6.php');

$wizard->set_var('db',$DB);
$wizard->set_var('config',$_config);
$wizard->set_var('user',$_user);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME .' '.gettext('Create a new assessment');
$UI->menu_selected = gettext('my assessments');
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = array  ('home'               => '../../' ,
    gettext('my assessments')         => '../' ,
    gettext('create a new assessment wizard') => null ,);

$UI->set_page_bar_button(gettext('List Assessments'), '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button(gettext('Create Assessments'), '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p><?php echo gettext('This wizard takes you through the process of creating a new assessment. When it is complete, you will have scheduled your assessment, and set the form and groups to assess.');?></p>

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
