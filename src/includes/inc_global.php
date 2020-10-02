<?php
/**
 * Global configuration file for WebPA
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

// Add composer's autoloader
require __DIR__ . '/../../vendor/autoload.php';

use WebPA\includes\classes\EngCIS;
use WebPA\includes\classes\DAO;
use WebPA\includes\classes\UI;
use WebPA\includes\classes\User;
use WebPA\includes\functions\Common;

// Set the correct timezone for your server.
date_default_timezone_set('Europe/London');

/*
 * Configuration
 */

////
// User configuration section
////

define('APP__WWW', '');
define('DOC__ROOT', ''); //must include the trailing /
define('CUSTOM_CSS', '');  // Optional custom CSS file
define('SESSION_NAME', 'WEBPA');
ini_set('session.cookie_path', '/');

// The month (1-12) in which the academic year is deemed to start (always on 1st of the month)
define('APP__ACADEMIC_YEAR_START_MONTH', 9);

//Database information
define('APP__DB_HOST', ''); // If on a non-standard port, use this format:  <server>:<port>
define('APP__DB_USERNAME', '');
define('APP__DB_PASSWORD', '');
define('APP__DB_DATABASE', '');
define('APP__DB_TABLE_PREFIX', 'pa2_');

// Contact info
define('APP__EMAIL_HELP', 'someone@email.com');
define('APP__EMAIL_NO_REPLY', 'no-reply@email.com');

// logo
define('APP__INST_LOGO', APP__WWW.'/images/logo.png');
define('APP__INST_LOGO_ALT','Your institution name');

//the following lines are to accomodate the image size within the css file to prevent the image from over flowing the area provided
define('APP__INST_HEIGHT', '25'); //image height in pixels
define('APP__INST_WIDTH', '102'); //image width in pixels

//define whether the option to allow textual input is allowed
/*NB. In the UK if requested any information about the student would need to be
* made available to them under the Freedom of Information Act 2000
* Therefore it is up to the installer of the software to meet the institutional requirements
* dependant on this act.
*
*/
define('APP__ALLOW_TEXT_INPUT', TRUE);

// enable delete options for users and modules
define('APP__ENABLE_USER_DELETE', FALSE);
define('APP__ENABLE_MODULE_DELETE', FALSE);

// set the mail server variables if different mail server is to be used.
ini_set('SMTP','localhost');
ini_set('smtp_port','25');
// if using a windows structure you need to set the send mail from
ini_set('sendmail_from','someone@email.com');

//define the authentication to be used and in the order they are to be applied
$LOGIN_AUTHENTICATORS[] = 'DB';
$LOGIN_AUTHENTICATORS[] = 'LDAP';

// LDAP settings
define('LDAP__HOST', "kdc.lboro.ac.uk");
define('LDAP__PORT', 3268);
define('LDAP__USERNAME_EXT', '@lboro.ac.uk');
define('LDAP__BASE', 'dc=lboro, dc=ac, dc=uk');
define('LDAP__FILTER', 'name={username}*');
define('LDAP__BINDRDN', '');
define('LDAP__PASSWD', '');
$LDAP__INFO_REQUIRED = array('displayname','mail','sn');
// Name of attribute to use to check user type (via function below)
define('LDAP__USER_TYPE_ATTRIBUTE', 'description');
define('LDAP__DEBUG_LEVEL', 7);
define('LDAP__AUTO_CREATE_USER', TRUE);

// define installed modules
$INSTALLED_MODS = array();

////
// System configuration section - do not change unless you know what you're doing!
////

//Application information
define('APP__NAME', 'WebPA OS');
define('APP__TITLE', 'WebPA OS : Online Peer Assessment System');
define('APP__ID', 'webpa');
define('APP__VERSION', '3.1.1');
define('APP__DESCRIPTION','WebPA, an Open source, online peer assessment system.');
define('APP__KEYWORDS','peer assessment, online, peer, assessment, tools, open source');

define('APP__DB_TYPE', 'MySQLDAO');

define('APP__DB_PERSISTENT', FALSE);
define('APP__DB_CLIENT_FLAGS', 2);

// User types
define('APP__USER_TYPE_ADMIN', 'A');
define('APP__USER_TYPE_TUTOR', 'T');
define('APP__USER_TYPE_STUDENT', 'S');

//Moodle gradebook output allowed...
define('APP__MOODLE_GRADEBOOK', FALSE); // If the grade book xml for moodle can be output then set to true, else if not required set to false

//Automatic emailing options.
//this is dependant on cron jobs being set for the following files;
//  /tutors/assessments/email/TriggerReminder.php
//  /tutors/assessments/email/ClosingReminber.php
define('APP__REMINDER_OPENING', FALSE);
define('APP__REMINDER_CLOSING', FALSE);

//set in individual pages to link to the most appropriate help sections.
//this is not an option that can be changed in the configuration
define ('APP__HELP_LINK','http://www.webpaproject.com/');

//define the terminology presented to the student as mark, rating or score
define('APP__MARK_TEXT', 'Score(s)');

// Collection owner types
define('APP__COLLECTION_USER', 'user');
define('APP__COLLECTION_ASSESSMENT', 'assessment');

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

// File upload error messages
$FILE_ERRORS = array(
  UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
  UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
  UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
  UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
  UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
  UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
  UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
  UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
  );

// Old config compatibility
$_config['app_id'] = APP__ID;
$_config['app_www'] = APP__WWW;

// Initialisation

session_name(SESSION_NAME);
session_start();

// Initialise DB object

$DB = new DAO( APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE);
$DB->set_debug(FALSE);

// Initialise User Object

$_user = null;

// Get info from the session
$_user_id = Common::fetch_SESSION('_user_id', NULL);
$_user_source_id = Common::fetch_SESSION('_user_source_id', NULL);
$_user_context_id = Common::fetch_SESSION('_user_context_id', NULL);
$_source_id = Common::fetch_SESSION('_source_id', '');
$_module_id = Common::fetch_SESSION('_module_id', NULL);
$BRANDING['logo'] = Common::fetch_SESSION('branding_logo', APP__INST_LOGO);
$BRANDING['logo.width'] = Common::fetch_SESSION('branding_logo.width', APP__INST_WIDTH);
$BRANDING['logo.height'] = Common::fetch_SESSION('branding_logo.height', APP__INST_HEIGHT);
$BRANDING['logo.margin'] = $BRANDING['logo.height'] + 10;
$BRANDING['name'] = Common::fetch_SESSION('branding_name', APP__INST_LOGO_ALT);
$BRANDING['css'] = Common::fetch_SESSION('branding_css', CUSTOM_CSS);
$BRANDING['email.help'] = Common::fetch_SESSION('branding_email.help', APP__EMAIL_HELP);
$BRANDING['email.noreply'] = Common::fetch_SESSION('branding_email.noreply', APP__EMAIL_NO_REPLY);

$CIS = new EngCIS($_source_id, $_module_id);

// If we found a user to load, load 'em!
if ($_user_id){

  $_user_info = $CIS->get_user($_user_id);

  // Actually create the user object
  $_user = new User();
  $_user->load_from_row($_user_info);
  $_user_info = null;   // We're done with the data, so clear it

  // save session data
  $_SESSION['_user_id'] = $_user->id;
}

if (!is_null($_user)) {
    $CIS->setUser($_user);
}

// If we found a module to load, load it!
if ($_module_id){
  $sql_module = 'SELECT module_id, module_code, module_title FROM ' . APP__DB_TABLE_PREFIX . "module WHERE module_id = {$_SESSION['_module_id']}";
  $_module = $DB->fetch_row($sql_module);
  $_module_code = $_module['module_code'];

}

$UI = new UI($INSTALLED_MODS, $_source_id, $BRANDING, $CIS, $_module, $_user);

function get_LDAP_user_type($data) {

    $description_str = $data[0];

    //check in the string for staff
    if(strripos ($description_str, 'staff') !== false) {
      $user_type = APP__USER_TYPE_TUTOR;
    } else {
      $user_type = APP__USER_TYPE_STUDENT;
    }

  return $user_type;

}

?>
