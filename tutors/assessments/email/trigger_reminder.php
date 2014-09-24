<?php
/**
 *
 * This file will trigger a reminder email to be sent
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.2
 * @since 7 Nov 2007
 *
 */

 //gather all include files needed.
 require_once("../../../includes/inc_global.php");
 require_once('new_assessment.php');

 //get a list of the assessment that will be run in two days from now
 $allDue = $DB->fetch("SELECT * FROM " . APP__DB_TABLE_PREFIX . "assessment a
             WHERE a.open_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND a.email_opening = 1");

 if (!empty($allDue)) {
  //cycle round and for each collection send the emails
  $assessments = count($allDue);

  foreach($allDue as $assessment){

  //specify the details of the email to be sent
  $subjectLn = gettext('Reminder: WebPA Assessment opening');
  $body = gettext("This is a reminder that the assessment your tutor set is due to open. The details are as below;") .
      "\n ".gettext("Assessment Name:")."  " . $assessment['assessment_name'] .
      "\n ".gettext("Open from:")."  " . $assessment['open_date'] .
      "\n ".gettext("Closes on:")."  " . $assessment['close_date'] .
      "\n ".gettext("To complete your assessment please go to:")." " . APP__WWW .
      "\n \n -------------------------------------------------------------------------------" .
      "\n ".gettext("This is an automated email sent by the WebPA tool")." \n\n";

  mail_assessment_notification ($assessment['collection_id'], $subjectLn, $body, $assessment['owner_id']);

  }
  unset($assessment);
 }

 exit;

?>