<?php
/**
 *
 * Index : forms
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.2
 *
 */

require_once('../../includes/inc_global.php');
require_once(DOC__ROOT . 'includes/classes/class_form.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

$generic_form = $DB->fetch('SELECT f.* FROM ' . APP__DB_TABLE_PREFIX . 'form f LEFT OUTER JOIN ' . APP__DB_TABLE_PREFIX .
   'form_module fm ON f.form_id = fm.form_id WHERE fm.form_id IS NULL ORDER BY form_name ASC');

$forms = $DB->fetch('SELECT f.* FROM ' . APP__DB_TABLE_PREFIX . 'form f INNER JOIN ' . APP__DB_TABLE_PREFIX .
   "form_module fm ON f.form_id = fm.form_id WHERE fm.module_id = {$_module_id} ORDER BY form_name ASC");

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME .' '.gettext('my forms');
$UI->menu_selected = gettext('my forms');
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = array  (
  'home'      => '../' ,
    gettext('my forms')  => null ,
);

$UI->set_page_bar_button(gettext('List Forms'), '../../../images/buttons/button_form_list.gif', '');
$UI->set_page_bar_button(gettext('Create a new Form'), '../../../images/buttons/button_form_create.gif', 'create/');
$UI->set_page_bar_button(gettext('Clone a Form'), '../../../images/buttons/button_form_clone.gif', 'clone/');
$UI->set_page_bar_button(gettext('Import a Form'), '../../../images/buttons/button_form_import.gif', 'import/');

$UI->head();
$UI->body();

$UI->content_start();
?>
  <p><?php echo gettext('Please select from the following options:');?></p>

  <div class="content_box">
    <h2><?php echo gettext('Existing forms');?></h2>
    <div class="form_section">

      <p><?php echo gettext('These are the forms you have already created. To edit a form, click on <img src="../../images/buttons/edit.gif" width="16" height="16" alt="edit form" title="edit" /> in the list below.');?></p>

      <div class="obj_list">


<?php
// @pmn - check to see if there are generic forms
if ($generic_form) {
?>
      <h3><?php echo gettext('generic / example form');?></h3>

<?php
  //out put the generic form
  foreach ($generic_form as $i => $form) {
    $clone_url = "clone/clone_example.php?f={$form['form_id']}";
    $edit_url = "edit/edit_form.php?f={$form['form_id']}";

?>
        <div class="obj">
          <table class="obj" cellpadding="2" cellspacing="2">
          <tr>
            <td class="icon" width="24"><a href="<?php echo($clone_url); ?>"><img src="../../images/icons/form.gif" width="24" height="24" alt="<?php echo gettext('Form');?>" /></a></td>
            <td class="obj_info">
              <div class="obj_name"><a class="text" href="<?php echo($clone_url); ?>"><?php echo($form['form_name']); ?></a></div>
            </td>
<?php
    if ($_user->is_admin()) {
?>
            <td class="button" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="<?php echo gettext('edit form');?>" title="<?php echo gettext('edit');?>" /></a></td>
            <td class="button" width="24"><a href="<?php echo($edit_url); ?>&command=delete" onclick="return confirm('<?php echo gettext('This assessment form will be deleted.\n\nClick OK to confirm.');?>');"><img src="../../images/buttons/cross.gif" width="16" height="16" alt="<?php echo gettext('delete form');?>" title="<?php echo gettext('delete');?>" /></a></td>
<?php
    }
?>
          </tr>
          </table>
        </div>
<?php
  } // @pmn -  /if (generic forms)
}
?>
  <h3><?php echo gettext('your forms');?></h3>

<?php
if (!$forms) {
?>
        <p><?php echo gettext('You do not have any assessment forms at the moment. Please <a href="create/">create a new form</a>.');?></p>
<?php
} else {

  //out put the form that the user owns
  foreach ($forms as $i => $form) {
    $edit_url = "edit/edit_form.php?f={$form['form_id']}";
    $export_url = "export/export_form.php?f={$form['form_id']}";
?>
          <div class="obj">
            <table class="obj" cellpadding="2" cellspacing="2">
            <tr>
              <td class="icon" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/icons/form.gif" width="24" height="24" alt="<?php echo gettext('Form');?>" /></a></td>
              <td class="obj_info">
                <div class="obj_name"><a class="text" href="<?php echo($edit_url); ?>"><?php echo($form['form_name']); ?></a></div>
              </td>
              <td class="button" width="24"><a href="<?php echo($export_url); ?>"><img src="../../images/file_icons/package_go.png" width="16" height="16" alt="<?php echo gettext('export form');?>" title="<?php echo gettext('export');?>" /></a></td>
              <td class="button" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="<?php echo gettext('edit form');?>" title="<?php echo gettext('edit');?>" /></a></td>
              <td class="button" width="24"><a href="<?php echo($edit_url); ?>&command=delete" onclick="return confirm('<?php echo gettext('This assessment form will be deleted.\n\nClick OK to confirm.');?>');"><img src="../../images/buttons/cross.gif" width="16" height="16" alt="<?php echo gettext('delete form');?>" title="<?php echo gettext('delete');?>" /></a></td>
            </tr>
            </table>
          </div>
<?php
  }
}
?>
    </div>
  </div>
</div>

<?php

$UI->content_end();

?>
