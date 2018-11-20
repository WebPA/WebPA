<?php
/**
 * WIZARD : Create new groups
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/classes/class_group_handler.php');
require_once(DOC__ROOT . 'includes/classes/class_wizard.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard('create new groups wizard');
$wizard->cancel_url = '../../../tutors/groups/';

$wizard->add_step(1,'class_wizardstep_1.php');
$wizard->add_step(2,'class_wizardstep_2.php');
$wizard->add_step(3,'class_wizardstep_3.php');
$wizard->add_step(4,'class_wizardstep_4.php');

$wizard->set_var('CIS',$CIS);
$wizard->set_var('db', $DB);
$wizard->set_var('config',$_config);
$wizard->set_var('user',$_user);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' Create new groups';
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = array  (
  'home'                      => '../../' ,
  'my groups'                 => '../' ,
  'create new groups wizard'  => null ,
);

$UI->set_page_bar_button('List Groups', '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button('Create Groups', '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button('Clone Groups', '../../../../images/buttons/button_group_clone.gif', '../clone/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p>This wizard takes you through the process of creating a new collection of associated groups. When it is complete, you will be able to edit the groups and assign students to them.</p>

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
