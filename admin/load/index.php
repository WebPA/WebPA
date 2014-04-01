<?php
/**
 * 
 * Upload information index page
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.7
 * @since 28 Mar 2007
 * 
 * 
 * + Added fourth category for uploading CSV data, Student Data with Groups. 
 * This format allows admins to upload student data, create a collection, create and 
 * assign groups to that collection, and assign the students to those groups in one click.
 * 
 * The new page now uses some DHTML JavaScript to automatically show and hide the new 
 * "Owner" and "Collection Name" text boxes - this can be safely disabled if necessary.
 * Morgan Harris [morgan@snowproject.net] as of 15/10/09
 */
 
 //get the include file required
 require_once("../../include/inc_global.php");
 
if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}
 
 //set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'upload data';
$UI->breadcrumbs = array ('home' => null);
$UI->help_link = '?q=node/237';

$UI->head();
?>
<script type="text/javascript">
function changeFormAction()
{
	var strContentType = document.fileLoad.rdoFileContentType;
	var srtSeparator = document.fileLoad.rdoFileSeperator;
	document.getElementById("fileLoad").action = '../../tmp/readfile.php?rdoFileContentType=' + strContentType + '&rdoFileSeperator=' +rdoFileSeperator;
	return true;
}
function fileTypeSelected(id)
{
	//this is called when a radio button is selected
	if(id==4)
	{
		document.getElementById('collectionNameDiv').style.display = 'block';
	}
	else
	{
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
$filecontenttype = array(4);
//even though it appears second, the 'student data with groups' option has a filecontenttype.value of 4 (to prevent possible breakage)

//this code is for use when one wants to create group collections first, then upload data
/*$sql = "select c.collection_id as id, c.collection_name, concat(a.forename, ' ', a.lastname) as name from user_collection c, user a where c.collection_owner_type = 'user' and c.collection_owner_id = a.user_id;";
$collections = $DB->fetch($sql);
$groupsAnnex = <<<HTML
<div style="display:none;" id="collectionNameDiv">
	<label>Collection:<select name="collection">
HTML;
	foreach($collections as $a)
	{
		$groupsAnnex .= "<option value='$a[id]'>$a[collection_name] ($a[name])</option>";
	}
$groupsAnnex .= '</select></label></div>';*/

$groupsAnnex = <<<HTML
<div style="display:none;padding:3px;" id="collectionNameDiv">
	<label>Collection Name:<input type="text" name="collection"/></label><br/>
	<label>Owner: <select name="owner">
HTML;
$sql = "SELECT user_id, concat(forename, ' ', lastname) as name FROM user WHERE user_type = 'staff'";
$users = $DB->fetch($sql);
foreach($users as $a)
	$groupsAnnex .= "<option value='$a[user_id]'>$a[name]</option>";
$groupsAnnex .= '</select></label></div>';

$filecontenttype[1] = array('screen'=>'<b>Student Data</b><p>CSV File format = institutional_reference, forename, lastname, email, username, module_code, department_id, course_id, password</p>', 'value'=>'1', 'instruction'=>'[institutional_reference, lastname, forename, email, username, password, module_code]',);
$filecontenttype[2] = array('screen'=>'<b>Student Data with Groups</b>'.$groupsAnnex.'<p>CSV File format = institutional_reference, forename, lastname, email, username, module_code, group_name, department_id, course_id, password</p>', 'value'=>'4', 'instruction'=>'[institutional_reference, lastname, forename, email, username, password, module_code]',);
$filecontenttype[3] = array('screen'=>'<b>Staff Data</b><p>CSV File format = institutional_reference, forename, lastname, email, username, module_code, department_id, course_id, password</p>', 'value'=>'2', 'instruction'=>'[institutional_reference, lastname, forename, email, username, password, module_code]');
$filecontenttype[4] = array('screen'=>'<b>Module Data</b><p>CSV File format = module_code, module_title</p>', 'value'=>'3', 'instruction'=>'[module_code, module_title]');
$fileseparator = 'Select the type of file separator that has been used:';
$separator = array(3);
$separator[1] = array('screen'=> 'Comma separated', 'value'=>',', 'status' => '');
$separator[2] = array('screen'=> 'Tab separated', 'value'=>'\t','status' => 'disabled');
$separator[3] = array('screen'=> 'Semi-colon', 'value'=>';','status' => 'disabled');

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
		<td width='25%'>
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
				for($checkbox = 1; $checkbox<= count($filecontenttype)-1; $checkbox++){
					
					echo '<input type="radio" name="rdoFileContentType" value="';
					echo $filecontenttype[$checkbox]['value'] . '" '; 	
					echo "onclick='fileTypeSelected({$filecontenttype[$checkbox]['value']})' id='rdoFileContentType-{$filecontenttype[$checkbox]['value']}'";
					echo ' />';
					echo $filecontenttype[$checkbox]['screen'];
					echo '<br/>';
					//echo $filecontenttype[$checkbox]['instruction'];
					//echo '<br/>';
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
