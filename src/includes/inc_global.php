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

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\DAO;
use WebPA\includes\classes\EngCIS;
use WebPA\includes\classes\UI;
use WebPA\includes\classes\User;
use WebPA\includes\functions\Common;

// load environment config
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');

$dotenv->load();

// Set the correct timezone for your server.
date_default_timezone_set('Europe/London');

// Configuration

////
// User configuration section
////

define('APP__WWW', $_ENV['APP_WWW']);
define('DOC__ROOT', $_ENV['DOC_ROOT']); //must include the trailing /
define('CUSTOM_CSS', $_ENV['CUSTOM_CSS_PATH']);  // Optional custom CSS file
define('SESSION_NAME', $_ENV['SESSION_NAME']);
ini_set('session.cookie_path', '/');

// The month (1-12) in which the academic year is deemed to start (always on 1st of the month)
define('APP__ACADEMIC_YEAR_START_MONTH', $_ENV['ACADEMIC_YEAR_START_MONTH']);

//Database information
define('APP__DB_HOST', $_ENV['DB_HOST']);
define('APP__DB_PORT', $_ENV['DB_PORT']);
define('APP__DB_USERNAME', $_ENV['DB_USER']);
define('APP__DB_PASSWORD', $_ENV['DB_PASS']);
define('APP__DB_DATABASE', $_ENV['DB_NAME']);
define('APP__DB_TABLE_PREFIX', $_ENV['DB_PREFIX']);

// Contact info
define('APP__EMAIL_HELP', $_ENV['HELP_EMAIL']);
define('APP__EMAIL_NO_REPLY', $_ENV['NO_REPLY_EMAIL']);

// logo
define('APP__INST_LOGO', $_ENV['LOGO_PATH']);
define('APP__INST_LOGO_ALT', $_ENV['LOGO_ALT_TEXT']);

//the following lines are to accomodate the image size within the css file to prevent the image from over flowing the area provided
define('APP__INST_HEIGHT', $_ENV['LOGO_HEIGHT']); //image height in pixels
define('APP__INST_WIDTH', $_ENV['LOGO_WIDTH']); //image width in pixels

//define whether the option to allow textual input is allowed
/*NB. In the UK if requested any information about the student would need to be
* made available to them under the Freedom of Information Act 2000
* Therefore it is up to the installer of the software to meet the institutional requirements
* dependant on this act.
*
*/
define('APP__ALLOW_TEXT_INPUT', $_ENV['ALLOW_TEXT_INPUT'] === 'true');

// enable delete options for users and modules
define('APP__ENABLE_USER_DELETE', $_ENV['ENABLE_USER_DELETE'] === 'true');
define('APP__ENABLE_MODULE_DELETE', $_ENV['ENABLE_MODULE_DELETE'] === 'true');

// set the mail server variables if different mail server is to be used.
ini_set('SMTP', $_ENV['SMTP_HOST']);
ini_set('smtp_port', $_ENV['SMTP_PORT']);
// if using a windows structure you need to set the send mail from
ini_set('sendmail_from', $_ENV['EMAIL_ADDRESS']);

//define the authentication to be used and in the order they are to be applied
$LOGIN_AUTHENTICATORS[] = 'DB';

// define installed modules
$INSTALLED_MODS = [];

////
// System configuration section - do not change unless you know what you're doing!
////

//Application information
define('APP__NAME', 'WebPA OS');
define('APP__TITLE', 'WebPA OS : Online Peer Assessment System');
define('APP__ID', 'webpa');
define('APP__VERSION', '3.1.2');
define('APP__DESCRIPTION', 'WebPA, an Open source, online peer assessment system.');
define('APP__KEYWORDS', 'peer assessment, online, peer, assessment, tools, open source');

// User types
define('APP__USER_TYPE_ADMIN', 'A');
define('APP__USER_TYPE_TUTOR', 'T');
define('APP__USER_TYPE_STUDENT', 'S');

//Moodle gradebook output allowed...
define('APP__MOODLE_GRADEBOOK', $_ENV['ENABLE_MOODLE_GRADEBOOK'] === 'true'); // If the grade book xml for moodle can be output then set to true, else if not required set to false

//Automatic emailing options.
//this is dependant on a cron job being set to run against the following file:
//  /jobs/Email.php
define('APP__REMINDER_OPENING', $_ENV['SEND_OPENING_REMINDER'] === 'true');
define('APP__REMINDER_CLOSING', $_ENV['SEND_CLOSING_REMINDER'] === 'true');

//set in individual pages to link to the most appropriate help sections.
//this is not an option that can be changed in the configuration
define('APP__HELP_LINK', 'http://www.webpaproject.com/');

//define the terminology presented to the student as mark, rating or score
define('APP__MARK_TEXT', $_ENV['MARK_TERMINOLOGY']);

// Collection owner types
define('APP__COLLECTION_USER', 'user');
define('APP__COLLECTION_ASSESSMENT', 'assessment');

//ordinal scale
//This scale is used in the reports as some institution and academic tutors prefer this scale.
//However, it must be noted that the majority of universities in the UK are using arithmetic mean for classifications.
$ordinal_scale = [
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
];

// When reporting grades as decimals, define the precision, etc using this constant
define('APP__REPORT_DECIMALS', '%01.2f');

// File upload error messages
$FILE_ERRORS = [
  UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
  UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
  UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
  UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
  UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
  UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
  UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
  UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
  ];

// Old config compatibility
$_config['app_id'] = APP__ID;
$_config['app_www'] = APP__WWW;

// Initialisation

session_name(SESSION_NAME);
session_start();

// Initialise DB object

$DB = new DAO(APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE, APP__DB_PORT);

// Initialise User Object

$_user = null;

// Get info from the session
$_user_id = Common::fetch_SESSION('_user_id', null);
$_user_source_id = Common::fetch_SESSION('_user_source_id', null);
$_user_context_id = Common::fetch_SESSION('_user_context_id', null);
$_source_id = Common::fetch_SESSION('_source_id', '');
$_module_id = Common::fetch_SESSION('_module_id', null);
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
if ($_user_id) {
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

// initialise module
$_module = null;

// If we found a module to load, load it!
if ($_module_id) {
    $dbConn = $DB->getConnection();

    $query = 'SELECT module_id, module_code, module_title FROM ' . APP__DB_TABLE_PREFIX . 'module WHERE module_id = ?';

    $_module = $dbConn->fetchAssociative($query, [$_SESSION['_module_id']], [ParameterType::INTEGER]);

    $_module_code = $_module['module_code'];
}

$UI = new UI($INSTALLED_MODS, $_source_id, $BRANDING, $CIS, $_module, $_user);
