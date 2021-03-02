<?php
/**
 * This area provide the edit location for the modules held in the database
 *
 * Dependant on the information held in the WebPA system the administrator
 * who is the person able to access the system through a number of routes may
 * or may not be shown all of the information.
 *
 * On saving the edit the information is processed via the User class for the majority
 * of the information and then the module information is processed.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../includes/inc_global.php';

use WebPA\includes\classes\Module;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

 //set the page information
$UI->page_title = APP__NAME . ' Edit system users';
$UI->menu_selected = 'view data';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
$UI->set_page_bar_button('View Admin Data', '../../../images/buttons/button_admin_user.png', '../review/admin/index.php');
$UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../search/index.php');
$UI->breadcrumbs = ['home' => '../', 'review data'=>'../review/', 'edit'=>null];
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();

//build the content to be written to the screen

//get the passed module ID passed as m
$module = intval(Common::fetch_GET('m'));

$action = Common::fetch_POST('command');

$sScreenMsg = '';

//-----------------------------------------------------------------------

//collect all the information about the module to populate the fields
$module_id = $CIS->get_module($module);
$edit_module = new Module();
$edit_module->load_from_row($module_id);

//----------------------------------------------------------------------
//process form

//get the posted information
$action = Common::fetch_POST('save');

if ($action) {          //incase we want to do more than save changes in the future
    switch ($action) {
    case 'Save Changes':
    //put all the elements back into the structures
    $edit_module->module_code = Common::fetch_POST('module_code');
    $edit_module->module_title = Common::fetch_POST('module_title');

    //save all of the data
    $edit_module->set_dao_object($DB);
    if (empty($module)) {
        $module = $edit_module->add_module();
    } else {
        $edit_module->save_module();
    }

    //reload module
    $module_id = $CIS->get_module($module);
    $edit_module = new Module();
    $edit_module->load_from_row($module_id);

    //send notification to the screen that the save has occured.
    $sScreenMsg = 'The changes made for the module have been saved';

  }
}

//-----------------------------------------------------------------------
//build the page and fill in the spaces

$page_intro = '<p>Here you are able to edit the details of a module within the system. There may be some elements of the information which do not appear' .
    '   to have been completed and this will be dependant on the information stored in the system.</p>';

?>
<?php echo $page_intro; ?>


<div class="content_box">

<?php

  if (!empty($sScreenMsg)) {
      echo "<div class=\"success_box\">{$sScreenMsg}</div>";
  }

?>

<form action="module.php?m=<?php echo $module; ?>" method="post" name="edit_module">
<table class="option_list" style="width: 100%;">
  <tr>
    <td>
      <h2>Module Details</h2>
    </td>
  </tr>
  <tr>
    <td><label for="code">Code</label></td>
    <td>
      <input type="text" id="code" name="module_code" value="<?php echo $edit_module->module_code; ?>" size="20" maxlength="255">
    </td>
  </tr>
  <tr>
    <td><label for="title">Title</label></td>
    <td>
      <input type="text" id="title" name="module_title" value="<?php echo $edit_module->module_title; ?>" size="40" maxlength="255">
    </td>
  </tr>
  <tr><td colspan="2"><hr/></td></tr>
  <tr>
    <td colspan="2">
      <input type="submit" value="Save Changes" name="save" id="save">
    </td>
  </tr>
</table>
</form>
</div>
<?php

$UI->content_end();

?>
