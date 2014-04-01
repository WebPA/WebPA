<?php
/**
 * 
 * WIZARD : Clone own groups
 *
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
require_once("../../../../include/inc_global.php");
require_once(DOC__ROOT . '/library/classes/class_wizard.php');
require_once(DOC__ROOT . '/library/classes/class_group_handler.php');

if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard('clone own groups wizard');
$wizard->cancel_url = "../../../groups/clone/";

$wizard->add_step(1,'class_wizardstep_1.php');
$wizard->add_step(2,'class_wizardstep_2.php');
$wizard->add_step(3,'class_wizardstep_3.php');


$wizard->set_var('config', $_config);
$wizard->set_var('user', $_user);

$group_handler = new GroupHandler();
$wizard->set_var('group_handler', $group_handler);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard



// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' Clone own groups';
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = array	(
	'home' 											=> '../../../' ,
	'my groups'									=> '../../' ,
	'clone groups'							=> '../../clone/' ,
	'clone own groups wizard'		=> null ,
);

$UI->set_page_bar_button('List Groups', '../../../../../images/buttons/button_group_list.gif', '../../');
$UI->set_page_bar_button('Create Groups', '../../../../../images/buttons/button_group_create.gif', '../../create/');
$UI->set_page_bar_button('Clone Groups', '../../../../../images/buttons/button_group_clone.gif', '../../clone/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p>This wizard takes you through the process of cloning an existing collection of associated groups. When it is complete, you will be able to edit the groups and reassign students.</p>

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