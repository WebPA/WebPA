<?php
/**
 * INDEX - Main page
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once 'includes/inc_global.php';

$url = '';
if (isset($_GET['url'])) {
    $url = $_GET['url'];
}

if ($_user) {
    $id = '';
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
    header('Location: ' . APP__WWW . '/index.php?id=' . $id);
} elseif ($url) {
    if (strpos($url, '?') === false) {
        $url .= '?';
    } else {
        $url .= '&';
    }
    header('Location: ' . $url . 'lti_errormsg=' . urlencode('Unable to connect to ' . APP__NAME . '; please ensure that your browser is not blocking third-party cookies'));
} else {
    header('Location: ' . APP__WWW . '/login.php?msg=cookies');
}

exit;
