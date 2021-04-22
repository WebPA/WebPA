<?php
/**
 * Upload information index page
 *
 * Added fourth category for uploading CSV data, Student Data with Groups.
 * This format allows admins to upload student data, create a collection, create and
 * assign groups to that collection, and assign the students to those groups in one click.
 *
 * The new page now uses some DHTML JavaScript to automatically show and hide the new
 * "Owner" and "Collection Name" text boxes - this can be safely disabled if necessary.
 * Morgan Harris [morgan@snowproject.net] as of 15/10/09
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../includes/inc_global.php';

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR) || ($_source_id != '')) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

//set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'upload data';
$UI->breadcrumbs = ['home' => null];
$UI->help_link = '?q=node/237';

$UI->head();
?>
<script type="text/javascript">
function changeFormAction() {
  var strContentType = document.fileLoad.rdoFileContentType;
  var srtSeparator = document.fileLoad.rdoFileSeperator;
  document.getElementById("fileLoad").action = '../../tmp/readfile.php?rdoFileContentType=' + strContentType + '&rdoFileSeperator=' +rdoFileSeperator;
  return true;
}
function fileTypeSelected(id) {
  //this is called when a radio button is selected
  if(id==4) {
    document.getElementById('collectionNameDiv').style.display = 'block';
  } else {
    document.getElementById('collectionNameDiv').style.display = 'none';
  }
}
</script>
<?php
$UI->body();
$UI->content_start();
//build the content to be written to the screen

$page_intro = 'You can upload the data the system requires from this page';
$filename = 'Enter the file name to be used:';
$filecontent = 'Select the type of information you are uploading:<br>';
$filecontenttype = [4];
//even though it appears second, the 'student data with groups' option has a filecontenttype.value of 4 (to prevent possible breakage)

//this code is for use when one wants to create group collections first, then upload data
$group_handler = new GroupHandler();
$collections = $group_handler->get_module_collections($_module_id);

$groupsAnnex = '<div style="display: none;" id="collectionNameDiv">
  <label>Collection:';
if (count($collections) > 0) {
    $groupsAnnex .= '&nbsp;<select name="collectionlist">';
    $groupsAnnex .= '<option value="">Create using name entered...</option>';
    foreach ($collections as $collection) {
        $groupsAnnex .= "<option value=\"{$collection['collection_id']}\">{$collection['collection_name']}</option>";
    }
    $groupsAnnex .= '</select></label>&nbsp;<label>new:';
}
$groupsAnnex .= '&nbsp;<input type="text" name="collection"/></label></div>';

if ($_user->is_admin()) {
    $filecontenttype[1] = ['screen'=>'<strong>Student Data</strong><p>CSV File format = id_number, forename, lastname, email, username, password, department_id, module_code</p>', 'value'=>'1'];
    $filecontenttype[2] = ['screen'=>'<strong>Student Data with Groups</strong>'.$groupsAnnex.'<p>CSV File format = id_number, forename, lastname, email, username, group_name, password, module_code</p>', 'value'=>'4'];
    $filecontenttype[3] = ['screen'=>'<strong>Staff Data</strong><p>CSV File format = id_number, forename, lastname, email, username, password, department_id, module_code</p>', 'value'=>'2'];
} else {
    $filecontenttype[1] = ['screen'=>'<strong>Student Data</strong><p>CSV File format = id_number, forename, lastname, email, username, password, department_id</p>', 'value'=>'1'];
    $filecontenttype[2] = ['screen'=>'<strong>Student Data with Groups</strong>'.$groupsAnnex.'<p>CSV File format = id_number, forename, lastname, email, username, group_name, password</p>', 'value'=>'4'];
    $filecontenttype[3] = ['screen'=>'<strong>Staff Data</strong><p>CSV File format = id_number, forename, lastname, email, username, password, department_id</p>', 'value'=>'2'];
}
$filecontenttype[4] = ['screen'=>'<strong>Module Data</strong><p>CSV File format = module_code, module_title</p>', 'value'=>'3'];
$fileseparator = 'Select the type of file separator that has been used:';
$separator = [3];
$separator[1] = ['screen'=> 'Comma separated', 'value'=>',', 'status' => ''];
$separator[2] = ['screen'=> 'Tab separated', 'value'=>'\t', 'status' => 'disabled'];
$separator[3] = ['screen'=> 'Semi-colon', 'value'=>';', 'status' => 'disabled'];

$btn_name = 'Upload';
$pasteinstruction ='Copy and paste the contents of the file you want to add to the system, ensuring that the information is comma separated and that each entry begins on a new line.';

?>
<p><?php echo $page_intro; ?></p>
<form id="fileLoad" enctype="multipart/form-data" action="simple.php" method="POST" onsubmit="return changeFormAction()">
<div class="content_box">
<h2>Upload data via a file</h2>
<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
  <table class="option_list" >
  <tr>
    <td width="25%">
      <?php echo $filename; ?>
    </td>
    <td>
      <input name="uploadedfile" type="file" />
    </td>

  </tr>
  <tr>
    <td>
      <?php echo $filecontent; ?>
    </td>
    <td>
      <?php
        for ($checkbox = 1; $checkbox<= count($filecontenttype)-1; $checkbox++) {
            echo '<input type="radio" name="rdoFileContentType" value="';
            echo $filecontenttype[$checkbox]['value'] . '" ';
            echo "onclick=\"fileTypeSelected({$filecontenttype[$checkbox]['value']})\" id=\"rdoFileContentType-{$filecontenttype[$checkbox]['value']}\"";
            echo ' />';
            echo $filecontenttype[$checkbox]['screen'];
            echo '<br/>';
        }
      ?>
    </td>
  </tr>
  <tr>
    <td>
    </td>
    <td>
        <input type="submit" name="btnUpload" value="<?php echo $btn_name; ?>"/>
    </td>
  </tr>
  </table>
</div>
</form>

There are <a href="templates.php">templates</a> which you can follow for uploading the information to the WebPA system.

<?php

$UI->content_end();

?>
