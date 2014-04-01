<?php
/**
 * 
 * Upload the data from a file to the database
 * 
 * Upload the file to the server
 * Open the file and read the information
 * Write the information to the database
 * Delete the file from the server
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.3
 * @since 28 Mar 2007
 * 
 */
 
 
 //get the include file required
 require_once("../include/inc_global.php");
 
check_user($_user, 'staff');
 
 /**
  * Function to read the contents of the file
  * 
  * @param string $filename	Name of the file that is to be read
  * @param string $typeseparator
  * 
  * @return array 
  * 
  */
 function readContents($filename,$delimiter){

 	//check the information passed is not empty
 	if (!empty($filename)&&!empty($delimiter)){
	 	//create the array that will be used to pass the information back
	 	$returnarr = array();
	 	$arr = array();
	 	//open the file for reading
	 	$fcontents = file ($filename); 
		for($i=0; $i<sizeof($fcontents); $i++) { 				
			$line = trim($fcontents[$i]); 
	    	$arr = explode($delimiter, $line); 
	    	$tempstring = implode("','", $arr);
	    	$returnarr[$i] =  $tempstring; 
		}
 	}else{
 		return false;
 	}
	return $returnarr;
 }
 

 /**
  * Function to parse a delimiter separated file
  * 
  * @param string $filename	
  * @param string $delimiter
  * 
  * @return array 2D array
  */
 function parse_csv($filename, $delimiter){
 	if (!empty($filename)&&!empty($delimiter)){
	 	$return = array();
	 	$handle = fopen($filename,"r");	
	 	$row = 0;
		while(! feof($handle))
		{
		  	$return[$row] = fgetcsv($handle,0,$delimiter);
		  	$row++;
		 }
		 fclose($handle);
		 
		 return $return;
 	}else{
 		return false;
 	}
  }


/**
 * Function to formulate the SQL queries
 * 
 * @param int $intType
 * @param string $strType
 * @param string $data
 * 
 * @return array
 */
function build_query ($intType, $strType, $data){
	
	//create an array of the data string
	$arr_data = explode(",", $data);
	$arr_size = count($arr_data);
	
	//take the first two entries of the array
	$institutional_ref = $arr_data[0];
	$lastname = $arr_data[1];

	//check for the module code (so that the entry can be joined up)
	if($arr_size == 7){
		$module_code = $arr_data[6];

		//pop the item off the array so that it is not added to the database
		$popped = array_pop($arr_data);
	}
	
	$arr_size = count($arr_data);
	
	//check for the password
	if($arr_size == 6){
		$password = $arr_data[5];
		if (!($password == "''")){
			//strip Quotes
			
			$password = ", md5({$password})";
		}else{
			$password = ", ''";
		}
		//pop the password of the array so that it can be hashed when added to the database
		$popped = array_pop($arr_data);
	}
	
	//now we have the information reqired off the array reconstruct it
	//get the array size first!
	$arr_size = count($arr_data);
	$data = implode(",", $arr_data);
	
	//pass word information was included
	if($arr_size == 5){
		//we have enough of the information to construct the sql elements
		$queryData[0] = "user (user_type, institutional_reference, lastname, forename, email, username, password)";
		$queryData[1] = $strType. "','" . $data . $password ;
		$queryData[2] = $module_code; 
		$queryData[3] = $institutional_ref;
		$queryData[4] = $lastname;
	}
			
	if(!empty($queryData)){
		return $queryData;
	}else{
		return false;
	}

}


 //set the page information


$UI->page_title = 'WebPA Administration';

$UI->menu_selected = 'admin';
$UI->breadcrumbs = array ('home' => null);

$UI->head();
$UI->body();
$UI->content_start();

$dataupload =' data is being uploaded.';
$page_intro = 'Uploading information to the system from a file';
$fileuploadfail = 'The system has failed to upload the following file: ';
$deleteFail = 'Failed to remove the temp file from the server.<br/>Please contact your Server Admins.';
$deleteSuccess ='File has been removed from the system.';
$success = 'Import of information sucessful';
$no_module_match = 'The module assigned does not match any already in the system.<br>You will need to associate the students with the modules once you have uploaded the module information';
$file_error = array(0=>"There is no error, the file uploaded with success",
        			1=>"The uploaded file exceeds the upload_max_filesize directive",
        			2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        			3=>"The uploaded file was only partially uploaded",
        			4=>"No file was uploaded",
        			6=>"Missing a temporary folder");
$datatype = array (1=>'student',
				   2=>'staff',
				   3=>'module',);

// the post information
$uploadtype = $_REQUEST["rdoFileContentType"];
$delimiter = $_REQUEST["rdoFileSeperator"];
if ($delimiter=='\t'){$delimiter = "\t"; }


foreach($_FILES as $file)
{
    $errno = $file['error'];
}

?>


<div class="content_box">

<h2><?php echo $page_intro; ?></h2>
<?php


if ($errno == 0){
	echo '<p>' . $datatype[$uploadtype] . $dataupload . '</p>';
	
	//file information
	$filename = $_FILES['uploadedfile']['name'];
	$source = $_FILES['uploadedfile']['tmp_name'];
	//get the information from the file
	$localfile = file_get_contents($source);
	$handle = fopen($_FILES['uploadedfile']['name'],"w");
	$flagfile = FALSE;
	//set a flag on whether the file write on the server is sucessful 
	if(fwrite($handle,$localfile)>-1){
		$flagfile = TRUE;
	}else{
		$flagfile = FALSE;
	}
	
	//read the file info and write to the database
	if($flagfile==TRUE){
		
		if($uploadtype =='3'){
			$results = parse_csv($filename, $delimiter);
						
			$total_rows = count($results);
			for($row = 0; $row <=$total_rows; $row++){
				$data = $results[$row];
				$SQL = "INSERT INTO module (module_code, module_title) VALUES ('". $data[0] ."','". mysql_real_escape_string($data[1]) ."');";
				$insert = $DB->execute($SQL);
			}
		}else{
			//readContents(file($filename), separator($delimiter));
			$arrContents = readContents ($filename, $delimiter);
		}
		
		//check for contents returned
		if (count($arrContents)>1){
			
			$total_rows = count($arrContents)-1;
			for ($row = 0; $row<=$total_rows; $row++){
				
				$tabledata = build_query ($uploadtype, $datatype[$uploadtype], $arrContents[$row]);
				
				if (!$tabledata[2]==""){
					//run a select to see if the record exists
					$SQL = "SELECT * FROM user WHERE institutional_reference = '{$tabledata[3]} AND lastname = {$tabledata[4]}";
					$check = $DB->fetch($SQL);
				
					if(!empty($check)){
						//we need to check that the module info for this student exists
						$SQL = "SELECT * FROM user_module WHERE user_id = '{$check[0][user_id]}' AND module_id = {$tabledata[2]}';";
						$check_modules = $DB->fetch($SQL);
						if(!empty($check_modules)){
							$insert = "INSERT INTO user_module (user_id, module_id) VALUES ('{$check[0][user_id]}'," . $tabledata[2] . "');";
							$comp = $DB->execute($insert);		
						}
					}else{			 					
					
						$SQL = "INSERT INTO ". $tabledata[0] ." VALUES ('". $tabledata[1] .");";				
						$execute = $DB->execute($SQL);									
						$insert_pos = $DB->get_insert_id();
	
						//import the user module info
						$insert = "INSERT INTO user_module (user_id, module_id) VALUES ('{$insert_pos}'," . $tabledata[2] . "');";
						$comp = $DB->execute($insert);	
					}
				}			
			}
			
			echo $success;
		
		}
		//close the file
		fclose($handle);
		
		// remove the file
		if (!empty($filename)){
	 		try {
	 			$removed = unlink($filename);
	 		}catch (exception $e){
	 			
	 			if (!$removed){
	 				echo $deleteFail;
	 			}else{
	 				echo "<p>" . $deleteSuccess . "</p>";
	 			}
	 			echo '<div class="error_box"><p>'. $deleteFail . '</p></div>';
	 		} 		
		}
	}else{
		echo '<div class="error_box"><p>' . $fileuploadfail . $filename . '</p></div>';
	}
}else{
	echo '<div class="error_box"><p>' . $file_error[$errno] . '</p></div>';
}
?>
</div>
<?php
$UI->content_end();

?>
