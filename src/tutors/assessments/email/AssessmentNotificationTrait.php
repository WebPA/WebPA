<?php
/**
 * Email new assessment
 *
 * Email the collection that the students are associated with when the
 * new assessment is created.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\tutors\assessments\email;

use WebPA\includes\classes\Email;
use WebPA\includes\classes\EngCIS;
use WebPA\includes\classes\GroupHandler;
use WebPA\includes\functions\ArrayFunctions;
use WebPA\includes\functions\Common;

trait AssessmentNotificationTrait
{
    /*
     * Function mail_assessment_notification
     *
     * Sends an email to the complete collection when called
     * This function was primarily designed to be called when a
     * new assessment is created and schedualed.
     *
     * @param mixed $collectionId The ID value for the collection that is to be emailed
     * @param string $subjectLn The subject line that is to be sent for the email
     * @param string $body_content Body content of the email to be sent
     * @return mixed Either a true if successful or an error message is failed
     */
    public function mail_assessment_notification($collectionId, $subjectLn, $body_content, $_user_id)
    {
        //get the collection to whom the email is to be sent
        $group_handler = new GroupHandler();
        $collection = $group_handler->clone_collection($collectionId);

        //get an array of the collection members to send the email to
        $member_arr = $collection->get_members();

        $users_to_email = array_keys($member_arr);

        // create bcc list of recipients
        $bcc_list = null;

        $sourceId = Common::fetch_SESSION('_source_id', '');
        $moduleId = Common::fetch_SESSION('_module_id', null);

        $CISa = new EngCIS($sourceId, $moduleId);

        if (is_array($users_to_email)) {
            $users_arr = $CISa->get_user($users_to_email);
            $bcc_list = ArrayFunctions::array_extract_column($users_arr, 'email');

            //get the current userID
            $this_user = $CISa->get_user($_user_id);
            $bcc_list[] = $this_user['email'];
        } else {
            $errors[] = 'Unable to build email list - no students to email.';
            return $errors;
        }

        if (is_array($bcc_list)) {
            // Send the email
            $email = new Email();
            $email->set_bcc($bcc_list);
            $email->set_from($this_user['email']);
            $email->set_subject($subjectLn);
            $email->set_body($body_content);
            $email->send();
        } else {
            $errors[] = 'No list of students to email.';

            return $errors;
        }

        return true;
    }
}
