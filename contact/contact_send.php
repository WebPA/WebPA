<?php
/**
 *
 * Contact Us
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.9
 *
 */

require_once("../includes/inc_global.php");
require_once('../includes/classes/class_email.php');

check_user($_user);

// Process GET/POST

$contact_app_id = APP__ID;

$contact_person = fetch_POST('contact_person');
$contact_to = $BRANDING['email.help'];
if($contact_person < 1) {
  switch($contact_person) {
    case -1:
      $contact_to = $BRANDING['email.help'];
      break;
    case 0:
      if($_module_id) {
        $query = 'SELECT u.email FROM ' . APP__DB_TABLE_PREFIX . 'user u '
                    . ' JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ON u.user_id = um.user_id'
                  . ' WHERE (um.module_id = '. $DB->escape_str($_module_id) . ' AND um.user_type = \'T\')';
        $contact_to = $DB->fetch_col($query, 0);
      }

      if(empty($contact_to)) {
        $contact_to = $BRANDING['email.help'];
      }
      break;
  }
} else {
  $query = 'SELECT u.email FROM ' . APP__DB_TABLE_PREFIX . 'user u WHERE (u.user_id = '. $DB->escape_str($contact_person) .')';
  $contact_to = $DB->fetch_value($query);
}

$contact_user_id = $_user->id;
$contact_user_username = $_user->username;
$contact_user_fullname = "{$_user->forename} {$_user->lastname}";
$contact_user_email = $_user->email;

$contact_username = fetch_POST('contact_username');
$contact_fullname = fetch_POST('contact_name');
$contact_email = fetch_POST('contact_email');
$contact_phone = fetch_POST('contact_phone');

$contact_date = date('d-M-Y H:i');
$contact_type = fetch_POST('contact_type');
$contact_message = fetch_POST('contact_message');

$app_www = APP__WWW;

$email_body = 'Contact Sent'.'
----------------------------------------
'.gettext('Application').'  : '.$contact_app_id . ' (' . $app_www . ')'.'
'.gettext('Contact Type').' : '.$contact_type.'
'.gettext('Date').'         : '.$contact_date.'
----------------------------------------

'.gettext('Contact Details').'
----------------------------------------
'.gettext('Fullname').' : '.$contact_fullname.'
'.gettext('Username').' : '.$contact_username.'
'.gettext('Email').'    : '.$contact_email.'
'.gettext('Phone').'    : '.$contact_phone.'
----------------------------------------

'.gettext('User Account Details').'
----------------------------------------
'.gettext('User ID').'  : '.$contact_user_id.'
'.gettext('Fullname').' : '.$contact_user_fullname.'
'.gettext('Username').' : '.$contact_user_username.'
'.gettext('Email').'    : '.$contact_user_email.'
----------------------------------------

'.gettext('Message').':
----------------------------------------
'.$contact_message.'
----------------------------------------';

// Send the email
$email = new Email();
$email->set_to($contact_to);
$email->set_from($contact_email);
$email->set_subject("$contact_app_id : $contact_type");
$email->set_body($email_body);
$email->send();

// Begin Page

$UI->page_title = APP__NAME .' '. gettext('Message Sent');
$UI->menu_selected = gettext('contact');
$UI->breadcrumbs = array  ('home'   => '/' ,
    gettext('contact') => null ,);

$UI->head();
$UI->body();

$UI->content_start();
?>

  <div class="content_box">
    <p><?php echo gettext('Your message has now been sent.'); ?></p>
    <p><?php echo gettext('We will try and respond as soon as possible, but at times our team can be very busy. We apologise in advance for any delay in getting back to you.');?></p>
    <p><?php echo gettext('Thanks for your time');?></p>
  </div>
<?php

$UI->content_end();

?>
