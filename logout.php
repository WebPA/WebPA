<?php
/**
 * 
 * Logout page
 * 	
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
require_once("include/inc_global.php");

$msg = (fetch_GET('msg',null)) ? fetch_GET('msg',null) : 'logout' ;

unset($_SESSION['_user_username']);
unset($_SESSION['_user_user_type']);
session_destroy();

$_cookie->delete();

header('Location: login.php?msg=$msg');

?>