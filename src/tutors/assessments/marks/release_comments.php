<?php

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Email;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:' . APP__WWW . '/logout.php?msg=denied');

    exit;
}

// Process the form submission
$assessmentId = Common::fetch_POST('assessment-id');


if (empty($assessmentId)) {

} else  {

}

// Get every user in the assessment
$userQuery =
    'SELECT             a.assessment_id, ugm.user_id, u.email ' .
    'FROM               ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
    'LEFT JOIN          ' . APP__DB_TABLE_PREFIX . 'user_group ug ' .
    'ON                 ug.collection_id = a.collection_id ' .
    'LEFT JOIN          ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ' .
    'ON                 ugm.group_id = ug.group_id ' .
    'LEFT JOIN          '. APP__DB_TABLE_PREFIX . 'user u ' .
    'ON                 u.user_id = ugm.user_id ' .
    'WHERE              a.assessment_id = ?';

$assessmentUsers = $DB
    ->getConnection()
    ->fetchAllAssociative($userQuery, [$assessmentId], [ParameterType::STRING]);


foreach ($assessmentUsers as $user) {
    $hash = hash('sha256', $user['assessment_id'] . $user['user_id'] . random_int(0, 1000000));

    $logReportIdQuery =
        'INSERT INTO             ' . APP__DB_TABLE_PREFIX . 'user_justification_report ' .
        'VALUES (?, ?, ?)';

    $stmt = $DB->getConnection()->prepare($logReportIdQuery);

    $stmt->bindValue(1, $hash);
    $stmt->bindValue(2, $user['assessment_id']);
    $stmt->bindValue(3, $user['user_id'], ParameterType::INTEGER);

    $stmt->execute();

    $email = new Email();


    $body =
        "Dear Andrew, \n\n" .
        "<a href=\"https://www-test.webpa.is.ed.ac.uk/students/assessments/reports/justification_comments.php?r=$hash\">" .
        "Justification comments </a> for the marks you received from your peers for assessment X are now available " .
        "for you to view, \n\n" .
        "Many thanks,\n" .
        "WebPA";

    $email->set_to( 'andrew.millington@ed.ac.uk'/* $user['email'] */);
    $email->set_from(APP__EMAIL_NO_REPLY);
    $email->set_subject('WebPA - Peer Feedback Comments Available');
    $email->set_body("You know it's just the same as it was $hash");
    $email->send();
}

// Close the comments edit
$publishedDateQuery =
    'INSERT INTO                 ' . APP__DB_TABLE_PREFIX . 'user_justification_publish_date ' .
    'VALUES                      (?, NOW())';

$stmt = $DB->getConnection()->prepare($publishedDateQuery);

$stmt->bindValue(1, $user['assessment_id']);

$stmt->execute();

// Begin the page
$UI->page_title = APP__NAME . ' ' . 'release student justification comments';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';

$UI->breadcrumbs = [
    'home' => '../../',
    'my assessments' => '../',
    'review justifications' => null,
];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();

$UI->content_start();

//$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'No changes have been saved. Please check the details in the form, and try again.');
?>
<div class="content_box">
    <strong>Feedback Released</strong>
<!-- DISPLAY ERRORS GENERATING FEEDBACK HERE -->
    <p>
        Feedback reports have been generated for all students. Links have been emailed to the students to let them view
        feedback from their peers.
    </p>
</div>

<?php

$UI->content_end();
