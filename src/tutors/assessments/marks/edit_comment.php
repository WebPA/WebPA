<?php

require_once '../../../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    http_response_code(401);

    exit;
}

$dbConn = $DB->getConnection();

$sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'moderated_user_justification VALUES '
     . '(?, ?) '
     . 'ON DUPLICATE KEY UPDATE moderated_comment = ?';

try {
    $stmt = $dbConn->prepare($sql);

    $stmt->bindValue(1, (int) Common::fetch_POST('comment-id'), \Doctrine\DBAL\ParameterType::INTEGER);
    $stmt->bindValue(2, Common::fetch_POST('comment'));
    $stmt->bindValue(3, Common::fetch_POST('comment'));

    $stmt->execute();
} catch (\Doctrine\DBAL\Exception $ex) {
    http_response_code(500);
}

http_response_code(200);