<?php
/**
 *
 * Global configuration file for WebPA
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.4
 * @since 2006
 *
 */

// Site Setup
// [pmn] @changed :  error_reporting(0);
session_start();

// Turn off warning about possible session & globals compatibility problem
ini_set('session.bug_compat_warn', 0);


// Set the correct timezone for your server.
date_default_timezone_set('Europe/London');


/*
 * Configuration
 */

//Application information
define('APP__NAME', 'WebPA OS');
define('APP__TITLE', 'WebPA OS : Online Peer Assessment System');
define('APP__WWW', '');
define('APP__ID', 'webpa');
define('APP__VERSION', '1.1.0.1');
define('APP__DESCRIPTION','WebPA, an Open source, online peer assessment system.');
define('APP__KEYWORDS','peer assessment, online, peer, assessment, tools, open source');

define('APP__INST_LOGO', APP__WWW.'/images/lboro.png');
define('APP__INST_LOGO_ALT','Loughborough University');

//the following lines are to accomidate the image size within the css file to prevent the image from over flowing the area provided
define('APP__INST_HEIGHT', '51');	//image height in pixels
define('APP__INST_WIDTH', '205');	//image width in pixels
define('APP__INST_MARGIN', APP__INST_HEIGHT + 10);

define('APP__MD5_SALT', 'PF46ALC9Z1');


//Database information
define('APP__DB_TYPE', 'MySQLDAO');

define('APP__DB_HOST', '');	// If on a non-standard port, use this format:  <server>:<port>
define('APP__DB_USERNAME', '');
define('APP__DB_PASSWORD', '');
define('APP__DB_DATABASE', 'pa');

define('APP__DB_PERSISTENT', false);
define('APP__DB_CLIENT_FLAGS', 2);

// Contact info
define('APP__EMAIL_INFO', 'someone@email.com');
define('APP__EMAIL_HELP', 'someone@email.com');
define('APP__EMAIL_TECH', 'someone@email.com');

//Moodle gradebook out put allowed...
define('APP__MOODLE_GRADEBOOK', false); // If the grade book xml for moodle can be output then set to true, else if not required set to false

//Automatic emailing options.
//this is dependant on cron jobs being set for the following files;
//	/tutors/assessments/email/trigger_reminder.php
//	/tutors/assessments/email/closing_reminber.php
define('APP__REMINDER_OPENING', false);
define('APP__REMINDER_CLOSING', false);

// Includes
define ('DOC__ROOT', '');	// Must not include the trailing /
require_once(DOC__ROOT.'/library/functions/lib_common.php');
require_once(DOC__ROOT.'/library/classes/class_dao.php');
require_once(DOC__ROOT.'/library/classes/class_user.php');
require_once(DOC__ROOT.'/library/classes/class_cookie.php');
require_once(DOC__ROOT.'/library/classes/class_engcis.php');
require_once(DOC__ROOT.'/include/classes/class_ui.php');

//set in individual pages to link to the most appropriate help sections.
//this is not an option that can be changed in the configuration
define ('APP__HELP_LINK','http://www.webpaproject.com/');

//define the authentication to be used
define('AUTH__CLASS', 'DBAuthenticator');	//LDAP Authentication is 'LDAPAuthenticator' and database authentication is 'DBAuthenticator'

//define the terminology presented to the student as mark, rating or score
define('APP__MARK_TEXT', 'Score(s)');


//ordinal scale
//This scale is used in the reports as some institution and academic tutors prefer this scale.
//However, it must be noted that the majority of universities in the UK are using arithmetic mean for classifications.
$ordinal_scale = array (
	'A+' => '78',
	'A'  => '75',
	'B+' => '68',
	'B'  => '65',
	'B-' => '62',
	'C+' =>'58',
	'C'  =>'55',
	'C-' =>'52',
	'D+' =>'48',
	'D'  =>'45',
	'D-' =>'42',
	'F'  =>'38',
	'F-' => '32',
	'X'  => '25',
	'X-' => '15',
	'Z'  =>'0',
);


// When reporting grades as decimals, define the precision, etc using this constant
define('APP__REPORT_DECIMALS', "%01.2f");


// Old config compatibility
$_config['app_id'] = APP__ID;
$_config['app_www'] = APP__WWW;

//define whether the option to allow textural input is allowed
/*NB. In the UK if requested any information about the student would need to be
* made available to them under the Freedom of Information Act 2000
* Therefore it is up to the installer of the software to meet the institutional requirements
* dependant on this act.
*
*/
define('APP__ALLOW_TEXT_INPUT', true);

//set the mail server variables if different mail server is to be used.
ini_set('SMTP','localhost');
ini_set('smtp_port','25');
// if using a windows structure you need to set the send mail from
ini_set('sendmail_from','');

// Initialisation

// Magic quotes workaround
set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc()) {
	function stripslashes_deep($value) {
		return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	}
//NW added in request as well
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_POST = array_map('stripslashes_deep', $_POST);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

// Initialise DB object

$DB = new DAO( APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE);
$DB->set_debug(false);

// Initialise The EngCIS Handler object

$CIS = new EngCIS();

// Initialise User Object

$_user = null;

// Initialise the cookie
$_cookie = new Cookie();

// Get info from the session
$_user_id = fetch_SESSION('_user_id', null);

// If there's no user in the session, but there is in the cookie, use that
if ( (!$_user_id) && ($_cookie->validate()) && (array_key_exists('user_id',$_cookie->vars)) ) {
	$_user_id = $_cookie->vars['user_id'];
}

// If we found a user to load, load 'em!
if ($_user_id){

	$_user_info = $CIS->get_user($_user_id);

	// Actually create the user object
	$_user = new User();
	$_user->load_from_row($_user_info);
	$_user_info = null;		// We're done with the data, so clear it

	// save session data
	$_SESSION['_user_id'] = $_user->id;

	// Save cookie data
	$_cookie->vars['user_id'] = $_user->id;

	$_cookie->save();
}

// Initialise UI Object

$UI = new UI($_user);

// Global Functions

/**
*	Check if the user is logged in and is a user of the given type
*	If not, it logs the user out
*	@param string $_user
*	@param string $user_type
*/
function check_user($_user, $user_type = null) {

	// Is the user valid?
	if ($_user) {

		// if we're not checking the user type, or we are checking and it matches, return OK
		if ( (!$user_type) || ($_user->type == $user_type) ) {
			return true;
		}
	}else{
		return false;
	}


	// If we didn't call 'return' then the user is denied access

	// If they tried to access the main index page, assume they haven't logged in and go to the login page directly
	if ($_SERVER['PHP_SELF']=='/index.php') {
		header('Location: '. APP__WWW .'/login.php');
	} else {	// log them out and give the DENIED message
		header('Location:'. APP__WWW .'/logout.php?msg=denied');
	}
	exit;
}


/**
 * Function for the debug print out
 * @param string  $var
 */
function debug_print($var) {
	echo('<pre>');
	print_r($var);
	echo('</pre>');
}

?>