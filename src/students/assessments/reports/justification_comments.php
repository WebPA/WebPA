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

$comments = null;
$errors = null;
$report_hash = Common::fetch_GET('r');

if (empty($report_hash)) {
    $errors[] = 'No report ID has been provided';
} else {
    $userAssessmentQuery =
        'SELECT             uj.justification_text, muj.moderated_comment ' .
        'FROM               ' . APP__DB_TABLE_PREFIX . 'user_justification_report ujr ' .
        'LEFT JOIN          ' . APP__DB_TABLE_PREFIX . 'user_justification uj ' .
        'ON                 ujr.assessment_id = uj.assessment_id ' .
        'AND                ujr.user_id = uj.marked_user_id ' .
        'LEFT JOIN          ' . APP__DB_TABLE_PREFIX . 'moderated_user_justification muj ' .
        'ON                 muj.user_justification_id = uj.id ' .
        'WHERE              ujr.user_justification_report_id = ?';

    try {
        $comments = $DB
            ->getConnection()
            ->fetchAllAssociative($userAssessmentQuery, [$report_hash], [ParameterType::STRING]);
    } catch (\Doctrine\DBAL\Exception $e) {
        error_log('Message: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());

        $errors[] = 'A problem was encountered when retrieving the report.';
    }
}

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

$UI->draw_boxed_list(
        $errors,
        'error_box',
        'The following errors were found:',
        'If this problem continues, please report it to the WebPA admins'
);
?>

<div class="content_box">
 <h2 style="font-size: 150%">Peer Feedback Justification Comments</h2>

    <?php if (!empty($comments)) : ?>
    <table class="grid">
        <tr>
            <th>#</th>
            <th>Comment</th>
        </tr>
        <?php foreach ($comments as $index => $comment) : ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= !empty($comment['moderated_comment']) ? $comment['moderated_comment'] : $comment['justification_text']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else : ?>
    <div>
        <p>There are no comments available to display</p>
    </div>
    <?php endif; ?>
</div>

<?php

$UI->content_end(false, false, false);