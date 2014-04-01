<?php
/**
 * 
 * Search area for looking up people within the system
 * 
 * This is the landing page for the search section and acts as a gate way
 * to the other sections within this area of the site.
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 30 June 2008
 * 
 */
 
 //get the include file required
 require_once("../../include/inc_global.php");
 
 if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

//-------------------------------------------------------------------------
//process the form

$post_name = fetch_GET('name');
$post_surname = fetch_GET('surname');
$post_inst_ref = fetch_GET('inst_ref');
$post_search = fetch_GET('search');

echo ($post_inst_ref);


$Query = "";
$sMessage="";

if(!empty($post_search)){
	//build the search string dependant on the data entered
	if(!empty($post_name)){
		//we have a first name, check for the surname
		if(!empty($post_surname)){
			//we have the surname, check for the instutional ID
			if(!empty($post_inst_ref)){
				//we have the institutional ref, write the search query
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE forename='{$post_name}' OR lastname='{$post_surname}' OR institutional_reference='{$post_inst_ref}'";
			}else{
				//we have no institutional ref write the search
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE forename='{$post_name}' OR lastname='{$post_surname}'";
			}
			
		}else{
			//we have no fir, check for the institutional ref
			if(!empty($post_inst_ref)){
				//we have the institutional ref, write the search query
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE forename='{$post_name}' OR institutional_reference='{$post_inst_ref}'";
			}else{
				//we have no institutional ref write the search
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE forename='{$post_name}'";
			}
		}
	}else{
		//no first name has been passed trough
		if(!empty($post_surname)){
			//we have the surname, check for the instutional ID
			if(!empty($post_inst_ref)){
				//we have the institutional ref, write the search query
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE lastname='{$post_surname}' OR institutional_reference='{$post_inst_ref}'";
			}else{
				//we have no institutional ref write the search
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE lastname='{$post_surname}'";
			}
			
		}else{
			//we have no fir, check for the institutional ref
			if(!empty($post_inst_ref)){
				//we have the institutional ref, write the search query
				$sQuery = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM user WHERE institutional_reference='{$post_inst_ref}'";				
echo $sQuery;
			}else{
				//nothing has been entered that can be searched for
				$sMessage = "<p>You have not entered any information for the search<br/>Please check and re-try.</p>";
			}
		}
	}

	$rs = $DB->fetch($sQuery);
	
}



 //------------------------------------------------------------------------
 
 //set the page information
$UI->page_title = APP__NAME . "  search for a user";
$UI->menu_selected = 'view data';
$UI->breadcrumbs = array ('home' => '../review/', 'search'=>null,);
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
$UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', 'index.php');

$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();
//build the content to be written to the screen

$page_intro = '<p>Search the WebPA system for a user within the system</p>';
$page_description = '<p>Enter the any combination of the information below for the individual that you would like to locate in the WebPA system. The person being searched for can be a student or staff member. When you are ready click the "Search Button".</p>';
$rstitle = "Search results";
?>
<?php echo $page_intro; ?>


<div class="content_box">
<?php 
if(!empty($post_search)){
echo  '<h2>' . $rstitle . '</h2>';

if (!empty($rs)){
	echo '<div class="obj">';
	echo '<table class="obj" cellpadding="2" cellspacing="2">';
	//work through the recordset if it is not empty
	$recordcounter = 0;
	while($recordcounter<=count($rs)-1){
		
		if($recordcounter == 0){
			$tmpcounter = 0;
			foreach ($rs[$recordcounter] as $field_index => $field_name){
				//create two temp arrays before writing to the screen
				$tmparr1[$tmpcounter]= $field_index;
				$tmparr2[$tmpcounter]= $field_name;
				$tmpcounter++;
			}
			
			//write the table field headers to the screen
			$icounter = 0;
			echo '<tr>';
			while($icounter<=count($tmparr1)-1){
				echo '<th>';
				echo $tmparr1[$icounter];
				echo '</th>';
				$icounter++;
			}
			echo '</tr><tr>';
			$icounter = 0;
			while($icounter<=count($tmparr2)-1){
				if ($icounter==0){
					echo '<td class="icon" width="16">';
					echo "<a href='../edit/index.php?u=" . $tmparr2[$icounter] . "'>";
					echo '<img src="../../images/buttons/edit.gif" width="16" height="16" alt="Edit user" /></a></td>';
				}else{			
					echo '<td class="obj_info_text">';
					echo $tmparr2[$icounter];
					echo '</td>';
				}
				$icounter++;
			}
			echo '</tr>';
		}else{
			echo '<tr>';
			foreach ($rs[$recordcounter] as $field_index => $field_name){
				if($field_index=='user_id'){
					echo '<td class="icon" width="16">';
			    	echo "<a href='../edit/index.php?u=" .$field_name . "'>";
			    	echo '<img src="../../images/buttons/edit.gif" width="16" height="16" alt="Edit user" /></a></td>';
				}else{
			    	echo '<td class="obj_info_text">'.$field_name.'</td>';
				}
			}
			echo '</tr>';
		}
		$recordcounter++;	
	}
	echo '</table>';
	echo '</div>';
}else{
	echo "<div class=\"warning_box\">The search has not found any matching information.</div>";
	echo $page_description; 
echo "<form method=\"get\" name=\"search\" action=\"index.php\">" ;
echo "<table class=\"option_list\" style=\"width: 500px;\">";
echo	"<tr><td><label for=\"name\">First name</label></td><td><input type=\"text\" id=\"name\" name=\"name\" ></td></tr>" ;
echo	"<tr><td><label for=\"surname\">Last name</label></td><td><input type=\"text\" id=\"surname\" name=\"surname\"></td></tr>" ;
echo	"<tr><td><label for=\"inst_ref\">Institutional reference</label></td><td><input type=\"text\" id=\"inst_ref\" name=\"inst_ref\"></td></tr>" ;
echo	"<tr><td><input type=\"hidden\" id=\"search\" name=\"search\" value=\"search\"></td><td><input type=\"Submit\" value=\"Search\" id=\"Search\"></td></tr>" ;
echo "</table>" ;
echo "</form>";
}
}else{
 if(!empty($sMessage)){echo "<div class=\"warning_box\">{$sMessage}</div>";} 

echo $page_description; 
echo "<form method=\"get\" name=\"search\" action=\"index.php\">" ;
echo "<table class=\"option_list\" style=\"width: 500px;\">";
echo	"<tr><td><label for=\"name\">First name</label></td><td><input type=\"text\" id=\"name\" name=\"name\" ></td></tr>" ;
echo	"<tr><td><label for=\"surname\">Last name</label></td><td><input type=\"text\" id=\"surname\" name=\"surname\"></td></tr>" ;
echo	"<tr><td><label for=\"inst_ref\">Institutional reference</label></td><td><input type=\"text\" id=\"inst_ref\" name=\"inst_ref\"></td></tr>" ;
echo	"<tr><td><input type=\"hidden\" id=\"search\" name=\"search\" value=\"search\"></td><td><input type=\"Submit\" value=\"Search\" id=\"Search\"></td></tr>" ;
echo "</table>" ;
echo "</form>";

}
?>
</div>
<?php
$UI->content_end();

?>
