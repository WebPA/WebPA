<?php
/**
 *
 * Upload of the XML file for the form
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 5 Nov 2007
 *
 */

//get the include file required
require_once("../includes/inc_global.php");
require_once('../includes/classes/class_xml_parser.php');
require_once('../includes/functions/lib_xml_validate.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

$errno = -1;
foreach($_FILES as $file) {
  $errno = $file['error'];
}

if ($errno == 0){

  //file information
  $filename = $_FILES['uploadedfile']['name'];
  $source = $_FILES['uploadedfile']['tmp_name'];
  //get the information from the file
  $localfile = file_get_contents($source);

  //validate the XML
  $isValid = Validate($localfile, '../tutors/forms/import/schema.xsd');

  if ($isValid){
    //get the ID for the current User
    $staff_id = $_user->id;

    // from the XML extract the form ID number and the name for the form
    $parser = new XMLParser();
    $parsed = $parser->parse($localfile);

    //set locally the form name and ID values
    $form_id =  $parsed['form']['formid']['_data'];
    $formname =  $parsed['form']['formname']['_data'];
    $formtype =  $parsed['form']['formtype']['_data'];

    //check that the form doesn't already exist for the user or for another
    $results = $DB->fetch("SELECT * FROM " . APP__DB_TABLE_PREFIX . "form f WHERE form_id ='{$form_id}' AND form_name = '{$formname}'");

    if ($results){
      //we need to prompt that they are the same - or send to clone form
      $action_notify = "<p>You already have this form in your forms list.</p><p>If you would like to make a copy of the form please use the <a href=\"../tutors/forms/clone/index.php\">'clone form'</a> function.</p>";
    } else {
      //need to replace the ID number before replacing in the system.
      $new_id = uuid_create();
      $parsed['form']['formid']['_data'] = $new_id;
      //re build the XML
      $xml = $parser->generate_xml($parsed);

      //now add to the database for this user
      $fields = array(
                 'form_id' => $new_id,
                 'form_name' => $formname,
                 'form_type' => $formtype,
                 'form_xml' => ''
                );
      $DB->do_insert('INSERT INTO ' . APP__DB_TABLE_PREFIX . 'form ({fields}) VALUES ({values})', $fields);
      $DB->do_insert('UPDATE ' . APP__DB_TABLE_PREFIX . "form SET {fields} WHERE user_id = {$new_id}", array('form_xml' => $xml));
      $fields = array(
                 'form_id' => $new_id,
                 'module_id' => $_module_id
                );
      $DB->do_insert('INSERT INTO ' . APP__DB_TABLE_PREFIX . 'form_module ({fields}) VALUES ({values})', $fields);

      $action_notify = "<p>The form has been uploaded and can be found in your <a href=\"../tutors/forms/index.php\">'my forms'</a> list.</p>";
    }
  } else {
    $action_notify = "<p>The import has failed due to the following reasons &#59; <br/>{$isValid}</p>";
  }

} else if (isset($FILE_ERRORS[$errno])) {
  $action_notify = "<p>{$FILE_ERRORS[$errno]}</p>";
} else {
  $action_notify = "<p>Unable to upload file.</p>";
}
$UI->page_title = APP__NAME . ' load form';
$UI->menu_selected = 'my forms';
$UI->breadcrumbs = array('home' => '/', 'my forms'  => null ,);

$UI->set_page_bar_button('List Forms', '../../images/buttons/button_form_list.gif', '../tutors/forms/');
$UI->set_page_bar_button('Create a new Form', '../../images/buttons/button_form_create.gif', '../tutors/forms/create/');
$UI->set_page_bar_button('Clone a Form', '../../images/buttons/button_form_clone.gif', '../tutors/forms/clone/');
$UI->set_page_bar_button('Import a Form', '../../images/buttons/button_form_import.gif', '../tutors/forms/import/');

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
