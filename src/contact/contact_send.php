<?php
/**
 * Contact Us
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../includes/inc_global.php';

use WebPA\includes\classes\Email;
use WebPA\includes\functions\Common;
use WebPA\includes\functions\Form;

Common::check_user($_user);

// Process GET/POST

$contact_app_id = APP__ID;

$contact_to = $BRANDING['email.help'];

$contact_user_id = $_user->id;
$contact_user_username = $_user->username;
$contact_user_fullname = "{$_user->forename} {$_user->lastname}";
$contact_user_email = $_user->email;

$contact_username = Common::fetch_POST('contact_username');
$contact_fullname = Common::fetch_POST('contact_name');
$contact_email = Common::fetch_POST('contact_email');
$contact_phone = Common::fetch_POST('contact_phone');

$contact_date = date('d-M-Y H:i');
$contact_type = Common::fetch_POST('contact_type');
$contact_message = Common::fetch_POST('contact_message');

$app_www = APP__WWW;

$errors = [];

if ($contact_fullname == '') {
    $errors[] = 'Name is required';
}

if ($contact_message == '') {
    $errors[] = 'Message is required';
}

if ($contact_email == '') {
    $errors[] = 'Email is required';
} elseif (!Form::is_email($contact_email)) {
    $errors[] = 'Email is not valid';
}

if (empty($errors)) {
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
}

// Begin Page

$UI->page_title = APP__NAME . ' Message Sent';
$UI->menu_selected = 'contact';
$UI->breadcrumbs = ['home'   => '/',
              'contact' => null, ];

$UI->head();
$UI->body();

$UI->content_start();
?>

  <div class="content_box">
    <?php if (empty($errors)) { ?>
    <p>Your message has now been sent.</p>
    <p>We will try and respond as soon as possible, but at times our team can be very busy. We apologise in advance for any delay in getting back to you.</p>
    <p>Thanks for your time</p>
    <?php } else { ?>
    	<p>Please correct the following errors:</p>
    	<ul>
    	<?php foreach ($errors as $error) { ?>
    		<li><?php echo $error?></li>
    	<?php } ?>
    	</ul>
    	<br>
    	<button onclick="javascript:window.history.back();">Fix Errors</button>
    <?php } ?>
  </div>
<?php

$UI->content_end();

?>
