<?php
/**
 *
 * This area provide the edit location for the users held in the database
 *
 * Dependant on the information held in the WebPA system the administrator
 * who is the person able to access the system through a number of routes may
 * or may not be shown all of the information.
 *
 * On saving the edit the information is processed via the class_user.php for the majority
 * of the information and then the module information is processed.
 *
 *
 * @copyright 2008 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.5
 * @since 30 June 2008
 *
 */

 //get the include file required
 require_once("../../include/inc_global.php");

 if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

 //set the page information
$UI->page_title = APP__NAME . ' Edit system users';
$UI->menu_selected = 'view data';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
$UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../search/index.php');
$UI->breadcrumbs = array ('home' => '../../../','review data'=>'../review/','edit'=>null, );
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();
//build the content to be written to the screen

//get the passed user ID passed as u
$user = fetch_GET('u');

$action =  fetch_POST('command');

$sScreenMsg = '';

//-----------------------------------------------------------------------

//collect all the information about the person to populate the fields
$user_id = $CIS->get_user($user);
$edit_user = new User();
$edit_user->load_from_row($user_id);


//----------------------------------------------------------------------
//process form

	//get the posted information


	$action = fetch_POST('save');

	if (($action)) {					//incase we want to do more than save changes in the future
		switch ($action) {
			case 'Save Changes':
			//put all the elements back into the structures
			$edit_user->forename = fetch_POST('name');
			$edit_user->surname = fetch_POST('surname');
			$edit_user->email = fetch_POST('email');
			$edit_user->type = fetch_POST('rdo_type');
			switch ($edit_user->type) {
				case 'staff':
					$edit_user->staff_id = $user_id;
					break;
				// --------------------
				case 'student':
					$edit_user->student_id = $user_id;
					break;
				// --------------------
				default: break;
			}// /switch

			$edit_user->institutional_ref = fetch_POST('inst_ref');
			$edit_user->department_id = fetch_POST('dept');
			$edit_user->course_id = fetch_POST('course');

			//check to see if the password needs to be saved
			if(!(fetch_POST('password')=='!!!!!!')) {
				$password = md5(fetch_POST('password'));
				$edit_user->update_password($password);
			}

			if((fetch_POST('username'))){
				$edit_user->update_username(fetch_POST('username'));
			}

			//set the admin permission
			if (fetch_POST('chk_admin')== 'on'){
				$edit_user->admin = 1;
			}else{
				$edit_user->admin = 0;
			}

			$edit_user->set_dao_object($DB);

			//save all of the data
			$edit_user->save_user();

			//reload user
			$edit_user = new User();
			$edit_user->load_from_row($user_id);

			//now process the module information and save the changes

			//the fetch brings back an array of the module codes, this
			//is the key used in the user_module table
			$modules = fetch_POST('module_id');

			//build the fields that we will process
			foreach($modules as $module => $modules) {
				$fields[] = array ('user_id' => $edit_user->id ,
							       'module_id' => $modules,
							      );
			}
			//delete all the entrys for this user first
			$DB->execute("DELETE FROM user_module " .
						   "WHERE user_id='{$edit_user->id}'");

			//set up the SQL to be run
			$SQL = "REPLACE INTO user_module ({fields}) VALUES {values} ";

			//re instate all the users modules
			$DB->do_insert_multi($SQL, $fields);

			//send notification to the screen that the save has occured.
			$sScreenMsg = "The changes made for the user have been saved";


			//collect all the updated information
			$user_id = $CIS->get_user($user);
			$edit_user = new User();
			$edit_user->load_from_row($user_id);
		}

	}




//-----------------------------------------------------------------------
//build the page and fill in the spaces


$page_intro = '<p>Here you are able to edit the details of a user within the system. There may be some elements of the information which do not appear' .
		'		to have been completed and this will be dependant on the information stored in the system.</p>';

if ( AUTH__CLASS=='DBAuthenticator'){
	$page_conditions = '<p>N.B. <strong>Database authentication</strong> has been set as the for the login of a user. You will need to ensure that a username and password are included.</p>';
}else{
	$page_conditions = '<p>N.B. <strong>LDAP authenication</strong> has been set for the login of the users. No username or password needs to be set.';
}
?>
<?php echo $page_intro; ?>


<div class="content_box">

<?php echo $page_conditions;

	if(!empty($sScreenMsg)){
		echo "<div class=\"success_box\">{$sScreenMsg}</div>";
	}

?>

<form action="index.php?u=<?php echo $user; ?>" method="post" name="edit_user">
<table class="option_list" style="width: 100%;">
<tr><td colspan=2><h2>User Details</h2></td></tr>
	<tr>
		<td width="17%"><label for="name">First name</label>
		</td>
		<td width="25%">
			<input type="text" id="name" name="name" value="<?php echo $edit_user->forename; ?>">
		</td>

		<td width="20%"><label for="surname">Last name</label>
		</td>
		<td>
			<input type="text" id="surname" name="surname" value="<?php echo $edit_user->surname; ?>">
		</td>
	</tr>
	<tr>
		<td><label for="inst_ref">Institutional reference</label>
		</td>
		<td>
			<input type="text" name="inst_ref" id="inst_ref" value="<?php echo $edit_user->institutional_ref; ?>">
		</td>

		<td><label for="email">Email</label>
		</td>
		<td>
			<input type="text" name="email" id="email" value="<?php echo $edit_user->email; ?>">
		</td>
	</tr>
		<tr>
		<td><label for="username">Username</label>
		</td>
		<td>
			<input type="text" name="username" id="username" value="<?php echo $edit_user->username; ?>">
		</td>

		<td><label for="password">Password</label>
		<p style="font-size:xx-small;">N.B. If a password is present in the system then 6 characters are shown. For security reasons the password is not displayed in clear text.</p>
		</td>
		<td>
		<?php
		if (!empty($edit_user->password)) {
			$show = '!!!!!!';
		}
		?>
			<input id="password" name="password" type="password" value="<?php echo $show; ?>">
		</td>
	</tr>
		<tr>
		<td><label for="dept">Department ID</label>
		</td>
		<td>
			<input type="text" name="dept" id="dept" value="<?php echo $edit_user->department_id; ?>">
		</td>

		<td><label for="course">Course ID</label>
		</td>
		<td>
			<input type="text" name="course" id="course" value="<?php echo $edit_user->course_id; ?>">
		</td>
	</tr>
<tr><td colspan='4'><hr/></td></tr>
	<tr><td colspan=2><h2>User type information</h2></td></tr>


		<tr>
		<td >
			<input type='radio' value="staff" id="staff" name="rdo_type" <?php if (!empty($edit_user->staff_id)){echo "checked";}?>> <label for="staff">Staff</label><br/>
			<input type="radio" value="student" id="student" name="rdo_type" <?php if (!empty($edit_user->student_id)){echo "checked";}?>> <label for="student">Student</label>
		</td>
		<td>
		</td>

		<td><input type="checkbox" id="admin" name="chk_admin" <?php if ($edit_user->admin=='1'){echo "checked";}?>> <label for="admin">Administrator</label>
		</td>
		<td>
		</td>
	</tr>
	<tr><td colspan='4'><hr/></td></tr>
	<tr><td colspan='2'><h2>Module</h2></td></tr>
			<tr>

			<?php
				//get all the modules in the system and write them to the screen
				$modules = $CIS->get_all_modules();
				$module_select = 'multiple';

				//get the modules associated with the user being edited
				if(!empty($edit_user->staff_id)){
					$users_modules = $CIS->get_staff_modules($edit_user->staff_id);
				}else{
					$users_modules = $CIS->get_student_modules($edit_user->student_id);
				}

				$user_mods=null;

				//return just the module _id's for comparision later on
				if (is_array($users_modules)){
					foreach ($users_modules as $mods){
						$user_mods[] = $mods['module_id'];
					}
				}

				if (!$modules) {
			?>
			<td><p>There are no modules in the system. You will need to add modules before a user can be linked to one.</p></td>

			<?php

				} else {
					$input_type = ($module_select=='multiple') ? 'checkbox' : 'radio' ;

					echo "<table>";
					foreach ($modules as $i => $module) {
						$checked_str = ( (is_array($user_mods)) && (in_array($module['module_id'],$user_mods)) ) ? 'checked="checked"' : '' ;
						echo('<tr>');
						echo("<td><input type=\"$input_type\" name=\"module_id[]\" id=\"module_{$module['module_id']}\" value=\"{$module['module_id']}\" $checked_str /></td>");
						echo("<td><label style=\"font-weight: normal;\" for=\"module_{$module['module_id']}\">{$module['module_id']} : {$module['module_title']}</label></td>");
						echo('</tr>');
					}
					echo "</table>";
				}


			?>

	</tr>
	<tr><td colspan='2'><hr/></td></tr>
	<tr>

		<td>
		</td>
		<td>
			<input type="submit" value="Save Changes" name="save" id="save">
		</td>
	</tr>
</table>
</form>
</div>
<?php
$UI->content_end();

?>
