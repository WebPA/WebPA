<?php
/**
 * This file will trigger a reminder email to be sent
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\tutors\assessments\email;

use WebPA\includes\classes\DAO;

class TriggerReminder
{
    use AssessmentNotificationTrait;

    private DAO $dao;

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    public function send()
    {
        $allDueQuery =
            'SELECT * ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
            'WHERE a.open_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY) ' .
            'AND a.email_opening = 1';

        //get a list of the assessment that will be run in two days from now
        $allDue = $this->dao->getConnection()->fetchAllAssociative($allDueQuery);

        if (!empty($allDue)) {
            foreach ($allDue as $assessment) {
                //specify the details of the email to be sent
                $subjectLn = 'Reminder: WebPA Assessment opening';
                $body = ' This is a reminder that the assessment your tutor set is due to open. The details are as below;' .
                    "\n Assessment Name:  " . $assessment['assessment_name'] .
                    "\n Open from:  " . $assessment['open_date'] .
                    "\n Closes on:  " . $assessment['close_date'] .
                    "\n To complete your assessment please go to: " . APP__WWW .
                    "\n \n -------------------------------------------------------------------------------" .
                    "\n This is an automated email sent by the WebPA tool \n\n";

                $this->mail_assessment_notification($assessment['collection_id'], $subjectLn, $body, $assessment['owner_id']);
            }
            unset($assessment);
        }
    }
}
