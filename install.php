 <?php
 /**
  * 
  * Install setup page to allow the inital user to be set up
  * 
  * This page will allow the person who is setting up WebPA
  * to set the db password and username for the administration
  * acount. Once this has been done then this file will need to be
  * removed from the system to prevent any one else changing the admin
  * details are.
  * 
  * Hopefully this page will be modified over time to try and assist the users
  * as and when users request functionality.
  *
  * 
  * @copyright 2007 Loughborough University
  * @license http://www.gnu.org/licenses/gpl.txt
  * @version 0.0.0.3
  * @since 8 Oct 2010
  * 
  */
  
  require_once("include/inc_global.php");
  require_once('library/classes/class_email.php');
 
 
 
  //build the page
  $UI->page_title = 'Web-PA Admin Setup';
  $UI->menu_selected = '';
  $UI->help_link = '?q=node/26';
  $UI->breadcrumbs = array	('Admin setup'	=> null ,);
 
 
  $UI->head();
 
  $UI->body();
  $UI->content_start();
  
  // see if any of the settings have been sent
  $forename = (string) fetch_POST('forename', null);
  $lastname = (string) fetch_POST('lastname', null);
  $institutional_ref = (string) fetch_POST('institutional_ref', null);
  $dept_id = (string) fetch_POST('department_id', null);
  $email = (string) fetch_POST('email', null);
  $username = (string) fetch_POST('username', null);
  $password = (string) fetch_POST('password', null);
  $send_team = fetch_POST('rdo_send', null);
  
 
  //if we have at least the username and the password then we can say that we are processing
  if ($username and $email){
  	
  	// Sanitize the username/password data
 	$username = substr($username,0,32);
 	$password = substr($password,0,32);
 	
 	//hash the password if one has been supplied
 	//and create the query string
 	if (!empty($password)){
 		$password = md5($password);
 		
 		$sql = 'INSERT INTO user(user_type, institutional_reference, forename, lastname, email, username, department_id, course_id, password, admin)
 		        VALUES ("staff", "'.$institutional_ref.'", "'.$forename.'", "'.$lastname.'", "'.$email.'", "'.$username.'", "'.$dept_id.'", "", "'.$password.'", "1" );';
 		$sql_check = 'SELECT * FROM user WHERE forename = "'.$forename.'" AND lastname="'.$lastname.'" AND username="'.$username.'" AND password="'.$password .'";';
 	
 	}else{
 		//just create a query string with out the password
 		$sql = 'INSERT INTO user(user_type, institutional_reference, forename, lastname, email, username, department_id, course_id, admin)
 		        VALUES ("staff", "'.$institutional_ref.'", "'.$forename.'", "'.$lastname.'", "'.$email.'", "'.$username.'", "'.$dept_id.'", "", "1" );';
 		$sql_check = 'SELECT * FROM user WHERE forename = "'.$forename.'" AND lastname="'.$lastname.'" AND username="'.$username.'";';
 	}
 	//check the admin hasn't already been added
 	$check_result = $DB->fetch_row($sql_check);
 	
 	if (empty($check_result)){
 	
 		//add this information to the database
 		$DB->_process_query($sql);
 ?>	<div class="content_box">
 		<div class="success_box">
 			<p> Your details have been set up and a new Administrator account has been created for you.</p>
 		</div>
 		<div class="error_box">
 			<p> To ensure that no other administrator accounts can be created we recomend that you remove this file from the server</p>
 		</div>
 		
 		<p>You can <a href='install.php'>add further administrators</a>, or move to adding further users to the WebPA system via the admin interface. To add further users
 		you will need to <a href='login.php'>login.</a></p>
 	</div>
 <?php	
 		//check to see if we have to send the details to the WebPA team at Loughborough
 		if ($send_team == 'on'){
 			
 			$to_list = "n.wilkinson@lboro.ac.uk";
 			$subjectLn = "WebPA: New institutional user";
 			$body_content = "The following user has been created as an administrator " . $forename . " " . $lastname . " (email: ". $email . ")" .
 					". They are willing for their institution to be added to the " .
 					"community pages on the website.";
 			
 			$email = new Email();
 			$email->set_to($to_list);
 			$email->set_from($email);
 			$email->set_subject($subjectLn);
 			$email->set_body($body_content);
 			$email->send();
 		}
 
 	}else{
 ?>
 	<div class="content_box">
 		<div class="error_box">
 			<p> The information for this administrator has already been added.</p>		
 		</div>
 		<p>You can <a href='install.php'>add further administrators</a>, or move to adding further users to the WebPA system via the admin interface. To add further users
 		you will need to <a href='login.php'>login.</a></p>
 	</div>
 <?php
 	}
 	
  }else{ 
 	
 	//check to see if we have been round once and if so 
 ?>
 <div class="content_box">
 	<p>This page allows you to set up the administrator for WebPA. The information entered will create the admin account, which in turn will allow the addition
 	of further users to the system.</p>
 	<p>If you choose so the information will also be sent to the WebPA project team who will include your institutional information as WebPA users on the
 	projects website.</p>
 </div>
 <div class="warning_box">
 		<p>If you are intending to use the Database Authentication then you will need to enter a passsword. If you are using the LDAP 
 	Authentication then you still need to enter all the information, but the password is not required. </p>
 
 </div>
 <div class="content_box">
 	
 	<form action="install.php" method="post" name="login_form" style="margin-bottom: 2em;">
 		<div style="width: 300px;">
 			<table class="form" cellpadding="2" cellspacing="1" width="100%">
 				<tr>
 					<th><label for="forename">Forename</label></th>
 					<td><input type="text" name="forename" id="forename" maxlength="30" size="30" value=""/></td>
 				</tr>
 				<tr>
 					<th><label for="lastname">Surname</label></th>
 					<td><input type="text" name="lastname" id="lastname" maxlength="30" size="30" value="" /></td>
 				</tr>
 				<tr>
 					<th><label for="institutional_ref">Institution</label></th>
 					<td><input type="text" name="institutional_ref" id="institutional_ref" maxlength="30" size="30" value="" /></td>
 				</tr>
 				<tr>
 					<th><label for="department_id">Department ID</label></th>
 					<td><input type="text" name="department_id" id="department_id" maxlength="30" size="30" value="" /></td>
 				</tr>
 				<tr>
 					<th><label for="email">Email</label></th>
 					<td><input type="text" name="email" id="email" maxlength="30" size="30" value="" /></td>
 				</tr>
 				<tr>
 					<th><label for="username">Username</label></th>
 					<td><input type="text" name="username" id="username" maxlength="16" size="10" value="" /></td>
 				</tr>
 				<tr>
 					<th><label for="password">Password</label></th>
 					<td><input type="password" name="password" id="password" maxlength="16" size="10" value="" /></td>
 				</tr>
 			</table>
 			
 			
 			  <table class="form">
 			   <tr>
 					<th width="90px"><input type="checkbox" name="rdo_send" id="send_ok" /></th>
 					<td><label for="send_ok"> Include my institution in the list of WebPA users</label></td>
 				</tr>    
   			</table>

   
 
 			<div class="form_button_bar">
 				<input class="safe_button" type="submit" name="submit" value="Set up" />
 			</div>
 	</div>
 	</form>
 </div>
 <?php
  }
  
  $UI->content_end(false);
 ?>