<?php
/**
 *
 * Simple.php is a re-write of the previously used file upload process.
 *
 * A file with the table elements correctly named at the top of a cvs file
 * can be uploaded to the database. The system checks and links the module information with the students
 * The database is then checked for duplicates and removes them.
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.2
 * @since 28 Jul 2008
 *
 * + Added implementation for Student Data with Groups upload. This is marked with the
 * PHP comment "GROUP HANDLING".
 *
 * - Removed existing implementation of duplicate protection - this code was inefficient
 * (with large table sizes, the indexing process could take six or seven seconds) and
 * never deleted the index, meaning that dozens of indices are left on the `user` table.
 *
 * + Implemented new duplicate checking that consists simply of an "INSERT ON DUPLICATE
 * KEY UPDATE" SQL call.
 * !! This code relies on the existence of a unique key on (`institutional_reference`,
 * `username`). The SQL call to create this key is currently commented out in the code -
 * in order to implement it either:
 * 		a) uncomment the line following '//Uncomment this line to initialise duplicate
 * 		   checking', or
 * 		b) enter the following at a mysql prompt:
 * 		   ALTER TABLE user ADD UNIQUE KEY(`institutional_reference`,`username`);
 *
 * The second is probably easier and means one less database call with every page load.
 *
 * Morgan Harris [morgan@snowproject.net] as of 15/10/09
 */

 require_once("../../include/inc_global.php");
require_once("../../library/classes/class_group_handler.php");

 $uploadtype = $_REQUEST["rdoFileContentType"];
 $filename = $_FILES['uploadedfile']['tmp_name'];

 //define variable we are using
 $user_warning = '';
 $user_msg = '';

 $filecontenttype[1] = array('screen'=>'<b>Student Data</b>',);
 $filecontenttype[2] = array('screen'=>'<b>Staff Data</b>',);
 $filecontenttype[3] = array('screen'=>'<b>Module Data</b>');
 $filecontenttype[4] = array('screen'=>'<b>Student Data with Groups</b>');

 $expected_fields[1] = array(0=>'institutional_reference', 'forename', 'lastname', 'email', 'username', 'module_code');
 $expected_fields[2] = array(0=>'institutional_reference', 'forename', 'lastname', 'email', 'username', 'module_code');
 $expected_fields[3] = array(0=>'module_code', 'module_title');
 $expected_fields[4] = array(0=>'institutional_reference', 'forename', 'lastname', 'email', 'username', 'module_code', 'group_name');
 $flg_match=null;

 //increase the execution time to handle large files
 ini_set('max_execution_time',120);

 $row = 0;
 $fields = array();
 $final_rows = array();
 $handle = fopen("$filename", "r");
 while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
    $num = count($data);

    //if in the first row we should be getting the field names
   	//make them into an array
   	if($row == 0){
   		for ($c=0; $c < $num; $c++) {
        	$fields[$c]=trim($data[$c]);
    	}

    	//check to see that we have the correct information in the array
    	if(array_diff($expected_fields, $fields)){

    		//we have diferences. We need to check to see if any of the key elements have matched
    		foreach($expected_fields[$uploadtype] as $key_field){
    			if(array_search($key_field, $fields)== false){
    				//no match
    				$flg_match = false;
    			}else{  				  			}
    				$flg_match=true;
    			}
    		}

    if(!$flg_match){
    			for ($c=0; $c < $num; $c++) {
        			$final_rows[$row][$c] = trim($data[$c]);
        			$fields[$c] = $c;
    			}


    	}else{
    		$flg_match = true;
    	}

   	}else{
	    //build the associative array for the table entrys
	    for ($c=0; $c < $num; $c++) {
	    	if($flg_match){
	    		$final_rows[$row-1][$fields[$c]] =trim($data[$c]);
	    	}else{
	    		$final_rows[$row][$fields[$c]] =trim($data[$c]);
	    	}
	    }
   	}
   	$row++;
 }

//now we have the information in arrays continue to process.
fclose($handle);


//check which array we are being given at this point (the one with the named fields or the other)
if($flg_match){
	//we can process this very easily into the database

	//check to see if we have staff or student.
	if(($uploadtype=='1')||($uploadtype=='2')||($uploadtype=='4')){

		$process_copy = $final_rows;
//print_r(count($process_copy));
		//remove the modules from this copy
		for($counter = 0; $counter<count($process_copy); $counter++){
			unset($process_copy[$counter]['module_code']);
			unset($process_copy[$counter]['group_name']);

			//tag on the user type
			if($uploadtype=='2'){
				$process_copy[$counter]['user_type']='staff';
			}else{
				$process_copy[$counter]['user_type']='student';
			}

			//set the admin option
			$process_copy[$counter]['admin'] =0;


			//if there are passwords in the list, they will need to be MD5 hashed
			if (!empty($process_copy[$counter]['password'])){
				$process_copy[$counter]['password']=md5($process_copy[$counter]['password']);
			}
		}

		/* CHANGED 14/10/09 by Morgan Harris [morgan@snowproject.net]
		 * This was basically the old way of doing things, that had an unfortunate side effect of
		 * adding a huge number of indices to the table, and deleting old data when it found it.
		 * The new method relies on the existence of a
		 * 			UNIQUE KEY(`institutional_reference`,`username`)
		 * on the `user` table.
		 *

		//set up the SQL to be run
		$SQL = "REPLACE INTO user ({fields}) VALUES {values} ";

		//re instate all the users modules
		$DB->do_insert_multi($SQL, $process_copy);

		//check for and remove user duplicates in the database
		$temp_fields = $fields;
		$flipped = array_flip($temp_fields);
		//unset all the non-unique fields
		unset($flipped['module_code']);
		unset($flipped['group_name']);
		unset($flipped['password']);
		$temp_fields = array_flip($flipped);

		print_r($temp_fields);

		$str_fields = implode(',',$temp_fields);
		$SQL = "ALTER IGNORE TABLE user ADD UNIQUE KEY ({$str_fields})";
		$DB->execute($SQL);
//echo $SQL;
		//now deal with the module information, by finding the user in the database and then adding the information to the module table
		foreach ($final_rows as $entry){
			$sql = "SELECT user_id FROM user WHERE institutional_reference='{$entry['institutional_reference']}' AND email='{$entry['email']}'";
			$user_id = $DB->fetch_value($sql);
//echo $sql;
			$sql = "INSERT INTO user_module(user_id,module_id) VALUES ('$user_id','{$entry['module_code']}')";
			$DB->execute($sql);

			//check for duplicates in module tables
			$SQL = "ALTER IGNORE TABLE user_module ADD UNIQUE KEY (user_id,module_id)";
			$DB->execute($SQL);
//echo $SQL;
		} */

		/*
		NEW METHOD FOR INSERTING/UPDATING IMPLEMENTED 14/10/09 by Morgan Harris [morgan@snowproject.net]
		*/

		//Uncomment this line to initialise duplicate checking
		//This code only needs to be run once, but it won't matter if it runs multiple times -
		//although you will get MySQL error 1061 showing up in your logs

		//$DB->execute("CREATE INDEX institutional_reference(`institutional_reference`,`username`) ON user");
//print_r($process_copy);
		foreach($process_copy as $i)
		{
			$els = array();
			foreach($i as $key => $val)
			{
				$els[] = "$key = '$val'";
			}
			$sql = "INSERT INTO user SET " . implode(', ',$els);
			unset($els['institutional_reference']);
			unset($els['username']);
			$sql .= " ON DUPLICATE KEY UPDATE " . implode(', ',$els);
//echo $sql . "\n";
			$DB->execute($sql);
		}

		//GROUP HANDLING
		//If the type is 4, then the table contains group data.
		if($uploadtype=='4')
		{
			if(!isset($_REQUEST['collection']))
			{
				$user_warning .= "Collection name not set. Group creation aborted.<br/>";
			}
			else
			{
				//first we need to create this collection
				//for this we need the following data:
				//-collection name
				//-each module name
				$collection = new GroupCollection($DB);
				$collection->create();
				$collection->name = $_REQUEST['collection'];
				$collection->set_owner_info($_REQUEST['owner'],'webpa','user');
				//get all the modules (and the groups at the same time)
				$modules = $groups = array();
				foreach($final_rows as $entry)
				{
					if(!in_array($entry['module_code'],$modules))
						$modules[] = $entry['module_code'];
					if(!in_array($entry['group_name'],$groups))
						$groups[] = $entry['group_name'];
				}
				$collection->set_modules($modules);

				$collection->save();

				$fails = array();
				//create all the groups
				foreach($groups as $a)
				{
					$group = $collection->new_group($a);
					//for every student who is a member of that group, add them to that group.
					foreach($final_rows as $entry)
					{
						if($entry['group_name']==$a)
						{
							$sql = "SELECT user_id FROM user WHERE institutional_reference='$entry[institutional_reference]' AND email='$entry[email]'";
							$user_id = $DB->fetch_value($sql);
							$group->add_member($user_id,'member');
						}
					}
					if($group->save()!=true)
						$fails[] = $a;
				}
				if(count($fails))
				{
					$user_warning .= "Failed to save the following groups:<ul>";
					foreach($fails as $f)
						$user_warning .= "<li>$f</li>";
					$user_warning .= "</ul>";
				}

				//save it to the database and we're done!

				$user_warning .= $collection->save() ? "" : "Failed to save collection.<br/>";

			}
			//this code is for use when you want the tutor to create the collection/groups, then the admin to upload data
			/*$crow = $DB->fetch_row("SELECT * FROM user_collection WHERE collection_id = '$_REQUEST[collection]'");
			$collexion = new GroupCollection($DB);
			$collexion->load_from_row($crow);
			$groupsarr = $collexion->get_groups_array(); //this is a 2-dim array
			$groups = array();
			foreach($groupsarr as $g)
			{
				$groups[$g['group_name']] = $g['group_id'];
			}
			foreach($final_rows as $entry)
			{
				if(array_key_exists($entry['group_name'],$groups))
				{
					$group = $collexion->get_group_object($groups[$entry['group_name']]);
					$sql = "SELECT user_id FROM user WHERE institutional_reference='{$entry['institutional_reference']}' AND email='{$entry['email']}'";
					$user_id = $DB->fetch_value($sql);
					$group->add_member($user_id,'member');
					$group->save();
				}
			}*/
		}


	}else{

	//as we don't have staff or student then we must have "module"

		//build the SQL
		$sql = "REPLACE INTO module({fields}) VALUES {values}";
		$DB->do_insert_multi($sql,$final_rows);

		//check for and remove the duplicates
		$temp_fields = $fields;
		$str_fields = implode(',',$temp_fields);
		$sql = "ALTER IGNORE TABLE module ADD UNIQUE KEY ({$str_fields})";

		$DB->execute($sql);
	}

	$user_msg = "<p>Successful up load of the {$filecontenttype[$uploadtype]} information to the database.</p>";

}else{
	//we want to notify that the information is not structured as expected therefore bounce back to the user
	$user_warning .= "The information supplied can not be processed.</div><div><p>We suggest that you review the information to be uploaded and they <a href=\"../\">try again</a>. Alternatively you may wish to download a template to put the information in.</p>";
}

//write to screen the page information
//set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'upload data';
$UI->breadcrumbs = array ('home' => null);
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
$UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../review/search/index.php');

$UI->head();
$UI->body();
$UI->content_start();
?>
<div class="content_box">
<?php if(!empty($user_warning)){echo "<div class=\"warning_box\">{$user_warning}</div>";}?>

<?php if(!empty($user_msg)){echo $user_msg;}?>

</div>


<?php
$UI->content_end();
?>