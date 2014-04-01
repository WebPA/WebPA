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

require_once("../include/inc_global.php");
require_once('../library/classes/class_email.php');

check_user($_user);


// Process GET/POST

$contact_app_id = APP__ID;

$contact_to = APP__EMAIL_HELP;

$contact_user_id = $_user->id;
$contact_user_username = $_user->username;
$contact_user_fullname = "{$_user->forename} {$_user->surname}";
$contact_user_email = $_user->email;

$contact_username = fetch_POST('contact_username');
$contact_fullname = fetch_POST('contact_name');
$contact_email = fetch_POST('contact_email');
$contact_phone = fetch_POST('contact_phone');

$contact_date = date('d-M-Y H:i');
$contact_type = fetch_POST('contact_type');
$contact_message = fetch_POST('contact_message');

$app_www = APP__WWW;

$email_body = <<<EndBody
Contact Sent
----------------------------------------
Application  : $contact_app_id ($app_www)
Contact Type : $contact_type
Date         : $contact_date
----------------------------------------

Contact Details
----------------------------------------
Fullname : $contact_fullname
Username : $contact_username
Email    : $contact_email
Phone    : $contact_phone
----------------------------------------

User Account Details
----------------------------------------
User ID  : $contact_user_id
Fullname : $contact_user_fullname
Username : $contact_user_username
Email    : $contact_user_email
----------------------------------------

Message:
----------------------------------------
$contact_message
----------------------------------------
EndBody;


// Send the email
$email = new Email();
$email->set_to($contact_to);
$email->set_from($contact_email);
$email->set_subject("$contact_app_id : $contact_type");
$email->set_body($email_body);
$email->send();


// Begin Page
 
$UI->page_title = APP__NAME . ' Message Sent';
$UI->menu_selected = 'contact';
$UI->breadcrumbs = array	('home'		=> '/' ,
							'contact'	=> null ,);

$UI->head();
$UI->body();

$UI->content_start();
?>

	<div class="content_box">
		<p>Your message has now been sent.</p>
		<p>We will try and respond as soon as possible, but at times our team can be very busy. We apologise in advance for any delay in getting back to you.</p>
		<p>Thanks for your time</p>
		<p>The engCETL team</p>
	</div>
<?php

$UI->content_end();

?>