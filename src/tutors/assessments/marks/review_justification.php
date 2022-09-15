<?php

/**
 * Review and edit justification comments from students
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Assessment;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:' . APP__WWW . '/logout.php?msg=denied');

    exit;
}

$assessment_id = Common::fetch_GET('a');

$tab = Common::fetch_GET('tab');
$year = Common::fetch_GET('y', date('Y'));

$command = Common::fetch_POST('command');

$list_url = "../index.php?tab={$tab}&y={$year}";

$assessment = new Assessment($DB);

if ($assessment->load($assessment_id)) {
    // Not sure we need to load the assessment at the moment - anyways, here is the query to get the comments
    $feedbackCommentsQuery =
        'SELECT uj.id, uj.justification_text, mdj.moderated_comment, uj.group_id, ug.group_name ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'user_justification uj ' .
        'LEFT JOIN ' . APP__DB_TABLE_PREFIX . 'user_group ug ' .
        'ON uj.group_id = ug.group_id ' .
        'LEFT JOIN ' . APP__DB_TABLE_PREFIX . 'moderated_user_justification mdj ' .
        'ON mdj.user_justification_id = uj.id ' .
        'WHERE assessment_id = ? ' .
        'ORDER BY group_id';

    $comments = $DB
        ->getConnection()
        ->fetchAllAssociative($feedbackCommentsQuery, [$assessment_id], [ParameterType::STRING]);

    $groupComments = [];
    $groupNameIdMap = [];

    // Format the comments into a friendly format to iterate through
    foreach ($comments as $comment) {
        // only add comments if we have a comment to add
        if (!empty($comment['justification_text'])) {
            $groupComments[$comment['group_id']][] = [
                    'id' => $comment['id'],
                    'comment' => $comment['justification_text'],
                    'moderatedComment' => $comment['moderated_comment'],
            ];
        }

        if (!array_key_exists($comment['group_id'], $groupNameIdMap)) {
            $groupNameIdMap[$comment['group_id']] = $comment['group_name'];
        }
    }
} else {
    $assessment = null;
}

// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if (($command) && ($assessment)) {
    switch ($command) {
        case 'save':
            // Generate hash for each user
            // Save published date
            // Generate email
            break;
    }
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' review student justification comments';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = ['home' => '../../',
    'my assessments' => '../',
    'review justifications' => null,];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-notify@0.5.5/dist/simple-notify.min.css" />
<style>

    table.grid th {
        text-align: center;
    }

    table.grid td {
        text-align: left;
    }

    span.id_number {
        color: #666;
    }
    -->
</style>
<script src="https://cdn.jsdelivr.net/npm/simple-notify@0.5.5/dist/simple-notify.min.js"></script>
<script>
  function do_command (com) {
    switch (com) {
      default :
        document.groupmark_form.command.value = com
        document.groupmark_form.submit()
    }
  }

  const editCommentToggle = function (e) {
    e.preventDefault();

    toggleCommentDiv(e.target);
  }

  function toggleCommentDiv(target) {
    const formDiv = target.nextElementSibling;

    if (target.innerText === 'Edit') {
      target.innerText = 'Hide';
      formDiv.classList.remove('hide');
    } else {
      target.innerText = 'Edit';
      formDiv.classList.add('hide');
    }
  }

  const submitEditCommentForm = function (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const data = e.target;
      const commentId = data['comment-id'].value;

      fetch(data.getAttribute('action'), {
        method: data.getAttribute('method'),
        body: new FormData(data)
      }).then(response => {
        if (!response.ok) {
          throw new Error('Comment not updated');
        }

        new Notify({
          status: 'success',
          title: 'Comment Updated',
          text: 'Your edit has been saved',
          effect: 'slide',
          speed: 300,
          customClass: null,
          customIcon: null,
          showIcon: true,
          showCloseButton: true,
          autoclose: true,
          autotimeout: 5000,
          gap: 20,
          distance: 20,
          type: 1,
          position: 'right top'
        });

        toggleCommentDiv(document.getElementById(`edit-toggle-${commentId}`));

        const comment = document.getElementById(`comment-${commentId}`);

        comment.innerText = data.comment.value;
      })
      .catch(error => {
        new Notify({
            status: 'error',
            title: 'Error',
            text: 'A problem occurred. Your comment was not saved',
            effect: 'slide',
            speed: 300,
            customClass: null,
            customIcon: null,
            showIcon: true,
            showCloseButton: true,
            autoclose: false,
            autotimeout: 5000,
            gap: 20,
            distance: 20,
            type: 1,
            position: 'right top'
          });
        });
      });
  }

  function addHandlers() {
    const editLinks = [...document.getElementsByClassName('edit-toggle')];

    for (editLink of editLinks) {
      editLink.onclick = editCommentToggle;
    }

    const editCommentForms = document.querySelectorAll('form.edit-comment');

    editCommentForms.forEach(submitEditCommentForm);
  }

  window.onload = addHandlers;
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'No changes have been saved. Please check the details in the form, and try again.');

?>

<p>
    On this page you can review justification comments from students and edit them before releasing them to students.
    Please note that if you edit a justification, the original justification will be retained but the edited version is
    the only one that will show to the student.
</p>

<div class="content_box">

    <?php
    if (!$assessment) {
        ?>
        <div class="nav_button_bar">
            <a href="<?= $list_url ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to
                assessments list</a>
        </div>

        <p>The assessment you selected could not be loaded for some reason - please go back and try again.</p>
        <?php
    } else {
        ?>
        <div class="nav_button_bar">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td><a href="<?php echo $list_url; ?>"><img src="../../../images/buttons/arrow_green_left.gif"
                                                                alt="back -"> back to assessment list</a></td>
                </tr>
            </table>
        </div>

        <h2>Student Justification Comments</h2>

        <div style="display: flex; justify-content: flex-end">
            <form action="post">
                <button type="submit">Release Comments</button>
            </form>
        </div>
        <div>
            <?php foreach ($groupComments as $groupId => $comments) : ?>
            <section>
                <h3><?= $groupNameIdMap[$groupId] ?></h3>

                <?php foreach ($comments as $comment) : ?>
                <?php $displayComment = !empty($comment['moderatedComment']) ? $comment['moderatedComment'] : $comment['comment']; ?>
                <div style="margin-bottom: 2em;">
                    <p id="comment-<?= $comment['id'] ?>">
                        <?= $displayComment ?>
                    </p>
                    <a href="#" class="edit-toggle" id="edit-toggle-<?= $comment['id'] ?>">Edit</a>
                    <div class="hide">
                        <form action="edit_comment.php" method="post" class="edit-comment">
                            <textarea name="comment" style="width:100%;"><?= $displayComment ?></textarea>
                            <input type="hidden" name="comment-id" value="<?= $comment['id'] ?>">
                            <button type="submit">Save</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </section>
            <?php endforeach; ?>
        </div>
    <?php
    }
    ?>
    <div style="display: flex; justify-content: flex-end">
        <form action="release_comments.php" method="post">
            <input type="hidden" name="assessment-id" value="<?= $assessment_id ?>">
            <button type="submit">Release Comments</button>
        </form>
    </div>
</div>

<?php

$UI->content_end();
