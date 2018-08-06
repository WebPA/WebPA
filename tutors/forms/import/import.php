<?php

/**
 * Area where the xml content is imported
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once('../../../includes/inc_global.php');
require_once('../../../includes/classes/class_xml_parser.php');
require_once('../../../includes/functions/lib_xml_validate.php');

//get the posted data
$xml =  stripslashes($_GET['txtXml']);
if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

//check that we have something to validate
$empty = strlen(trim($xml));
if ($empty>0) {
  $isValid = Validate($xml, 'schema.xsd');
  if ($isValid) {
    //get the ID for the current User
    $staff_id = $_user->id;

    // from the XML extract the form ID number and the name for the form
    $parser = new XMLParser();
    $parsed = $parser->parse($xml);

    //set locally the form name and ID values
    $form_id =  $parsed['form']['formid']['_data'];
    $formname =  $parsed['form']['formname']['_data'];
    $formtype =  $parsed['form']['formtype']['_data'];

    //check that the form doesn't already exist for the user or for another
    $results = $DB->fetch("SELECT * FROM " . APP__DB_TABLE_PREFIX . "form f WHERE form_id = '{$form_id}' AND form_name = '{$formname}'");

    if ($results){
      //we need to prompt that they are the same - or send to clone form
      $action_notify = "<p>You already have this form in your forms list.</p><p>If you would like to make a copy of the form please use the <a href=\"index.php\">'clone form'</a> function.</p>";
    } else {
      //need to replace the ID number before replacing in the system.
      $new_id = uuid_create();
      $parsed['form']['formid']['_data'] = $new_id;
      //re build the XML
      $xml = $parser->generate_xml($parsed);

      //now add to the database for this user (or generic if being imported by an administrator)
      //need to replace the ID number before replacing in the system.
      $new_id = uuid_create();
      $parsed['form']['formid']['_data'] = $new_id;
      //re build the XML
      $xml = $parser->generate_xml($parsed);

      //now add to the database
      $fields = array(
                 'form_id' => $new_id,
                 'form_name' => $formname,
                 'form_type' => $formtype,
                 'form_xml' => $xml,
                );
      $DB->do_insert('INSERT INTO ' . APP__DB_TABLE_PREFIX . 'form ({fields}) VALUES ({values})', $fields);
      $DB->do_insert('UPDATE ' . APP__DB_TABLE_PREFIX . "form SET {fields} WHERE user_id = {$new_id}", array('form_xml' => $xml));
      $fields = array(
                 'form_id' => $new_id,
                 'module_id' => $_module_id
                );
      $DB->do_insert('INSERT INTO ' . APP__DB_TABLE_PREFIX . 'form_module ({fields}) VALUES ({values})', $fields);
      $action_notify = "<p>The form has been uploaded and can be found in your <a href=\"../index.php\">'my forms'</a> list.</p>";
    }
  } else {

    $action_notify = "<p>The import has failed due to the following reasons &#59; <br/>{$isValid}</p>";

  }

} else {
  $action_notify = "<p>There was no form information to upload.<br> Please go back and try again</p>";
}

$UI->page_title = APP__NAME . ' load form';
$UI->menu_selected = 'my forms';
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = array('home'      => '/' ,
            'my forms'  => null ,);

$UI->set_page_bar_button('List Forms', '../../../../images/buttons/button_form_list.gif', '../');
$UI->set_page_bar_button('Create a new Form', '../../../../images/buttons/button_form_create.gif', '../create/');
$UI->set_page_bar_button('Clone a Form', '../../../../images/buttons/button_form_clone.gif', '../clone/');
$UI->set_page_bar_button('Import a Form', '../../../../images/buttons/button_form_import.gif', '../import/');

$UI->head();
$UI->body();
$UI->content_start();

?>
<div class="content_box">
  <h2>form loading</h2>
  <?php echo $action_notify; ?>
</div>
<?php

$UI->content_end();

?>
