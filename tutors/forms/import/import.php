<?php
/**
 * 
 * Area where the xml content is imported
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.3
 * @since 29 Oct 2007
 * 
 */


 require_once('../../../include/inc_global.php');
 require_once('../../../library/classes/class_xml_parser.php');
 require_once('../../../library/functions/lib_xml_validate.php');
 
 //get the posted data
 $xml =  stripslashes($_GET['txtXml']);
if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}


//check that we have something to validate

$empty = strlen(trim($xml));

if ($empty>0){
	$isValid = Validate($xml, 'schema.xsd');
	if ($isValid){
		//get the ID for the current User 
		$staff_id = $_user->staff_id;
		
		// from the XML extract the form ID number and the name for the form
		$parser = new XMLParser();
		$parsed = $parser->parse($xml);
		
		//set locally the form name and ID values
		$form_id =  $parsed['form']['formid']['_data'];
		$formname =  $parsed['form']['formname']['_data'];
		
		//check that the form doesn't already exist for the user or for another
		$results = $DB->fetch("SELECT * FROM form f WHERE form_id ='{$form_id}' AND form_name = '{$formname}' AND form_owner_id = '{$staff_id}';");
		
		if ($results){
			//we need to prompt that they are the same - or send to clone form
			$action_notify = "<p>You already have this form in your forms list.</p><p>If you would like to make a copy of the form please use the <a href=\"index.php\">'clone form'</a> function.</p>";
		}else{
			//need to replace the ID number before replacing in the system.
			$new_id = uuid_create();
			$parsed['form']['formid']['_data'] = $new_id;
			//re build the XML
			$xml = $parser->generate_xml($parsed);
			
			//now add to the database for this user
			$results = $DB->fetch("INSERT INTO form (form_id, form_name, form_owner_id) VALUES ('{$new_id}','{$formname}','{$staff_id}');");
			$results = $DB->fetch("INSERT INTO form_xml (form_id, form_xml) VALUES ('$new_id','{$xml}');");
			
			$action_notify = "<p>The form has been uploaded and can be found in your <a href=\"../index.php\">'my forms'</a> list.</p>";
		}
	}else{

		$action_notify = "<p>The import has failed due to the following reasons &#59; <br/>{$isValid}</p>";

	}
		
	
}else{
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