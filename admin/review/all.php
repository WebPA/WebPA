<?php
/**
 * 
 * Generic retrieval code
 * 
 * Retrieves information from the database on specific information
 * This page is included within other pages, to allow quick reuse
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.5
 * @since 23 Apr 2007
 * 
 */

  if (check_user($_user, 'staff')){

	if ($type == 'Module'){
		//build the string for the information to be collected from the database
		$query = "SELECT * FROM `". $table . "`;";
	}else{
		$query = "SELECT user_id, institutional_reference, forename, lastname, email, username FROM `". $table . "` WHERE user_type = '" . $type . "';";
	}
	//run the query
	$rs = $DB->fetch($query);
	
	echo '<h2>' . $rstitle . '</h2>';
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
				if (($icounter==0)&&(!($type == 'Module'))){
					echo '<td class="icon" width="16">';
					echo "<a href='../../edit/index.php?u=" . $tmparr2[$icounter] . "'>";
					echo '<img src="../../../images/buttons/edit.gif" width="16" height="16" alt="Edit user" /></a></td>';
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
			    	echo "<a href='../../edit/index.php?u=" .$field_name . "'>";
			    	echo '<img src="../../../images/buttons/edit.gif" width="16" height="16" alt="Edit user" /></a></td>';
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
	echo ' <p>You need to be logged into the system to see this information.</p>';
}
  



