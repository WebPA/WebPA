<?php

// Validate the request?

require_once '../../../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:' . APP__WWW . '/logout.php?msg=denied');

    exit;
}

$dbConn = $DB->getConnection();

$sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'moderated_user_justification VALUES '
     . '(?, ?) '
     . 'ON DUPLICATE KEY UPDATE moderated_comment = ?';

try {
    $stmt = $dbConn->prepare($sql);

    $stmt->bindValue(1, (int) $_POST['comment-id'], \Doctrine\DBAL\ParameterType::INTEGER);
    $stmt->bindValue(2, $_POST['comment']);
    $stmt->bindValue(3, $_POST['comment']);

    $response = $stmt->execute();
} catch (\Doctrine\DBAL\Exception $ex) {
    http_response_code(500);
}


// Return the response
die('<pre>' . print_r($response, true) . '</pre>');