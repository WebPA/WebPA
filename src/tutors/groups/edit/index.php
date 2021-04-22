<?php

/**
 * Edit Groups : Edit Group and members
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\SimpleObjectIterator;
use WebPA\includes\functions\Common;
use WebPA\lang\en\Generic;
use WebPA\lang\en\tutors\Tutors;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------

$group_handler = new GroupHandler();
$collections = $group_handler->get_user_collections($_user->id);

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME. ' ' . Generic::EDIT__GROUP;;
$UI->menu_selected = Generic::MY__GROUPS;
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = [
  'home'      => '/',
  'my groups'   => '/groups/',
  'edit groups' => null,
];

$UI->set_page_bar_button(Generic::BTN__LIST_GROUPS, '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button(Generic::BTN__CREATE_GROUPS, '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button(Generic::BTN__CLONE_GROUPS, '../../../../images/buttons/button_group_clone.gif', '../clone/');

$UI->head();
?>
<style type="text/css">
<!--

div.collection {
  margin-bottom: 10px;
  padding: 2px 2px 2px 20px;
  background: url(../../../images/icons/spot_black.gif) no-repeat top left;
}

div.collection_locked {
  margin-bottom: 10px;
  padding: 2px 2px 2px 20px;
  background: url(../../../images/icons/padlock_16.gif) no-repeat top left;
}

div.collection_info { margin-left: 40px; font-size: 84%; }

span.locked { font-size: 82%; }

-->
</style>

<?php
$UI->content_start();
?>

<p><?php echo Tutors::GROUPS__EDIT__DESC; ?></p>

<div class="content_box">

<h2><?php echo Tutors::GROUPS__EDIT_TITLE; ?></h2>
<div class="form_section">
<?php
if (!$collections) {
    echo '<p>'. Tutors::NO_COLLECTIONS .'</p>';
} else {
    $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', $DB);

    echo '<p>' . Tutors::GROUPS__EDIT_INST . '</p>';

    $any_locks = false;
    for ($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next()) {
        $collection = $collection_iterator->current();

        $group_count = count($collection->get_groups_array());
        $modules = (is_array($collection->get_modules())) ? implode(', ', $collection->get_modules()) : 'none' ;

        echo '<div class="collection">';
        echo "  <div><a href=\"edit_collection.php?c={$collection->id}\">{$collection->name}</a></div>";
        echo '  <div class="collection_info"><div><strong>' . ASSOCIATED__MODULES . ":</strong> $modules</div><div><strong>" . Generic::NO__GROUPS . " :</strong> $group_count</div></div>";
        echo '</div>';
    }
}
?>
</div>
</div>

<?php

$UI->content_end();
