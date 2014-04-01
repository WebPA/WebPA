<?php
/**
 * 
 * INDEX - Main page			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */

require_once("include/inc_global.php");

//get the type of user from the url
//$_user = fetch_GET('id');
//check_user($_user);


if ($_user){
	if ($_user->type == 'staff'){
		header('Location: '.APP__WWW.'/tutors/');
	}else{
		header('Location: '.APP__WWW.'/students/');
	}
	
	exit;
}else{
	header('Location: '.APP__WWW.'/login.php');
}

exit;

?>