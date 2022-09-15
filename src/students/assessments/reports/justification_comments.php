<?php

/**
 * Report showing justification for peer marks
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\functions\Common;

$report_hash = Common::fetch_GET('r');

$userAssessmentQuery =
    'SELECT             uj.justification_text, u.forename ' .
    'FROM               ' . APP__DB_TABLE_PREFIX . 'user_justification_report ujr ' .
    'LEFT JOIN          ' . APP__DB_TABLE_PREFIX . 'user_justification uj ' .
    'ON                 ujr.assessment_id = uj.assessment_id ' .
    'AND                ujr.user_id = uj.marked_user_id ' .
    'LEFT JOIN          ' . APP__DB_TABLE_PREFIX . 'user u ' .
    'ON                 u.user_id = ujr.user_id ' .
    'WHERE              ujr.user_justification_report_id = ?';

$comments = $DB
    ->getConnection()
    ->fetchAllAssociative($userAssessmentQuery, [$report_hash], [ParameterType::STRING]);

// Begin page

$UI->page_title = APP__NAME . ' peer comments';
$UI->head();
?>
<style>
    #side_bar {
        display: none;
    }

    #main {
        margin: 0;
    }

    table.grid th { padding: 8px; }
    table.grid td { padding: 8px; text-align: center; }
    table.grid td.important { background-color: #eec; }
</style>
<?php
$UI->body();
$UI->content_start();
?>

<h2>Peer Feedback Justification Comments</h2>

<table class="grid">
    <tr>
        <th>#</th>
        <th>Comment</th>
    </tr>
    <?php foreach ($comments as $index => $comment) : ?>
    <tr>
        <td><?= $index + 1 ?></td>
        <td><?= $comment['justification_text'] ?></td>
    </tr>

    <?php endforeach; ?>
</table>

<?php

$UI->content_end();