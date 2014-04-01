<?php
/**
 * 
 * Export the form to file
 * 
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 26 Oct 2007
 * 
 */
 
 require_once("../../../include/inc_global.php");
 require_once(DOC__ROOT. '/include/classes/class_form.php');
 
 //get the form ID from the URL so that we can access the form from the database.
 $form_id = fetch_GET('f');
 $command = fetch_POST('command');
 
 $form = $DB->fetch_row("SELECT * FROM form INNER JOIN form_xml ON form.form_id=form_xml.form_id WHERE form.form_id='$form_id' LIMIT 1");

 header("Content-Disposition: attachment; filename=\"{$form['form_name']}.xml\"");
 header('Content-Type: application/xml');
 echo $form['form_xml'];
 exit;
?>