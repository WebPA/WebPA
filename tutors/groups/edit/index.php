<?php
/**
 *
 * Edit Groups : Edit Group and members
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/classes/class_group_handler.php');
require_once(DOC__ROOT . 'includes/classes/class_simple_object_iterator.php');

require_once("../../../lang/en/generic.php");
require_once("../../../lang/en/tutors/tutors.php");

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------

global $group_handler;
$group_handler = new GroupHandler();
$collections = $group_handler->get_user_collections($_user->id, $_config['app_id']);

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME. ' ' . EDIT__GROUP;
$UI->menu_selected = MY__GROUPS;
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = array  (
  'home'      => '/' ,
    gettext('my groups')   => '/groups/' ,
    gettext('edit groups') => null ,
);

$UI->set_page_bar_button(BTN__LIST_GROUPS, '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button(BTN__CREATE_GROUPS, '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button(BTN__CLONE_GROUPS, '../../../../images/buttons/button_group_clone.gif', '../clone/');

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

<p><?php echo GROUPS__EDIT__DESC; ?></p>

<div class="content_box">

<h2><?php echo GROUPS__EDIT_TITLE; ?></h2>
<div class="form_section">
<?php
if (!$collections) {
  echo('<p>'. NO_COLLECTIONS .'</p>');
} else {
  $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', "\$GLOBALS['group_handler']->_DAO");

  echo '<p>' . GROUPS__EDIT_INST . '</p>';

  $any_locks = false;
  for($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next() ) {
    $collection = $collection_iterator->current();

    $group_count = count($collection->get_groups_array());
    $modules = (is_array($collection->get_modules())) ? implode(', ',$collection->get_modules()) : 'none' ;

    echo('<div class="collection">');
    echo("  <div><a href=\"edit_collection.php?c={$collection->id}\">{$collection->name}</a></div>");
    echo("  <div class=\"collection_info\"><div><strong>".ASSOCIATED__MODULES.":</strong> $modules</div><div><strong>".NO__GROUPS." :</strong> $group_count</div></div>");
    echo('</div>');
  }
}
?>
</div>
</div>

<?php

$UI->content_end();

?>
