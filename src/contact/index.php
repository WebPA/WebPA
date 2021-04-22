<?php
/**
 * Contact Us
 *
 * This is the form that the user fills in which is processed and emailed in the contact_send.php
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../includes/inc_global.php';

use WebPA\includes\functions\Common;
use WebPA\includes\functions\Form;

Common::check_user($_user);

// Process GET/POST

$contact_type = Common::fetch_GET('q');

// Begin Page

$UI->page_title = APP__NAME . ' Contact';
$UI->menu_selected = 'contact';
$UI->help_link = '?q=node/379#intool';
$UI->breadcrumbs = ['home'		=> '../',
                             'contact'	=> null, ];


$UI->head();
?>
<script language="JavaScript" type="text/javascript">
<!--

	function do_send() {
		document.contact_form.submit();
	}// /do_send()

//-->
</script>
<?php
$UI->body();

$UI->content_start();

?>
	<p>If you want to report a problem or bug with any part of the WebPA system, have a technical query, or just need to ask a specific question regarding WebPA, please complete the form below.</p>

	<div class="content_box">
		<p>Please supply as much information with your message as possible (especially when sending a bug report!), this will allow us to respond to your message much faster!</p>

		<form action="contact_send.php" method="post" name="contact_form">
		<input type="hidden" name="contact_app" value="<?php echo $_config['app_id']; ?>" />

		<div class="form_section">
			<table class="form" cellpadding="2" cellspacing="2">
			<tr>
				<td><label for="contact_name">Your Name</label></td>
				<td><input type="text" name="contact_name" id="contact_name" maxlength="60" size="50" value="<?php echo "{$_user->forename} {$_user->lastname}"; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact_username">Your Username</label></td>
				<td><input type="text" name="contact_username" id="contact_username" maxlength="15" size="10" value="<?php echo $_user->username; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact_email">Your Email</label></td>
				<td><input type="text" name="contact_email" id="contact_email" maxlength="255" size="50" value="<?php echo $_user->email; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact_phone">Your Phone Number</label></td>
				<td><input type="text" name="contact_phone" id="contact_phone" maxlength="25" size="20" value="" /></td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td><label for="contact_type">Type of Message</label></td>
				<td>
					<select name="contact_type" id="contact_type">
						<?php
                            $contact_types = ['help'		=> 'Request for help!',
                                                     'info'		=> 'Information request',
                                                     'bug'		=> 'Bug / Error report',
                                                     'wish'		=> 'Suggestion / Wish List',
                                                     'misc'		=> 'Other type of message', ];

                            Form::render_options($contact_types, $contact_type);
                        ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="contact_message">Message Text</label></td>
				<td><textarea name="contact_message" id="contact_message" cols="60" rows="6"></textarea></td>
			</tr>
			</table>
		</div>

		<div class="button_bar">
			<input type="reset" name="resetbutton" id="resetbutton" value="reset form" />
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<input type="button" name="sendbutton" id="sendbutton" value="send message" onclick="do_send()" />
		</div>
		</form>
	</div>
<?php

$UI->content_end();

?>
