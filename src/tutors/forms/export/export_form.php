<?php

/**
 * Export the form to file
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\Config;
use WebPA\includes\functions\Common;

 if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)){
   header('Location:'. Config::APP__WWW .'/logout.php?msg=denied');
   exit;
 }

 //get the form ID from the URL so that we can access the form from the database.
 $form_id = Common::fetch_GET('f');
 $command = Common::fetch_POST('command');

 $form = $DB->fetch_row("SELECT f.* FROM " . APP__DB_TABLE_PREFIX . "form f WHERE f.form_id = '$form_id' LIMIT 1");

 header("Content-Disposition: attachment; filename=\"{$form['form_name']}.xml\"");
 header('Content-Type: application/xml');
 echo $form['form_xml'];
 exit;

?>
