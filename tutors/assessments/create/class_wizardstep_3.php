<?php
/**
 *
 * Class : WizardStep3    (Create new assessment wizard)
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");

class WizardStep3 {

  // Public
  public $wizard = null;
  public $step = 3;

  /*
  * CONSTRUCTOR
  */
  function WizardStep3(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = '&lt; Back';
    $this->wizard->next_button = 'Next &gt;';
    $this->wizard->cancel_button = 'Cancel';
  }// /WizardStep3()

  function head() {
    ?>
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
  }// /body_onload()

//-->
</script>
<?php
  }// /->head()

  function form() {

    global $_module;

    $DB =& $this->wizard->get_var('db');
    $config =& $this->wizard->get_var('config');
    $user =& $this->wizard->get_var('user');

    require_once(DOC__ROOT . 'includes/classes/class_group_handler.php');
    require_once(DOC__ROOT . 'includes/classes/class_simple_object_iterator.php');

    global $group_handler;
    $group_handler = new GroupHandler();
    $collections = $group_handler->get_module_collections($_module['module_id']);

    $collection_id = $this->wizard->get_field('collection_id');

    if (!$collections) {
      $this->button_next = '';
?>
      <p>You haven't yet created any group collections.</p>
      <p>You need to <a href="../../groups/create/">create some groups</a> before you will be able to run any peer assessments.</p>
<?php
    } else {
      $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', "\$GLOBALS['group_handler']->_DAO");
?>
      <p>Please select the collection of groups you wish to use in this assessment from the list below.</p>
      <p>The collection you select will be copied into your new assessment.  Subsequent changes to the collection of groups <strong>will not</strong> affect your assessment.</p>
      <h2>Your collections</h2>
      <div class="form_section">
        <table class="form" cellpadding="0" cellspacing="0">
<?php
        if ($collection_iterator->size()==1) {
          $collection = $collection_iterator->current();
          $collection_id = $collection->id;
          unset($collection);
        }
        for($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next() ) {
          $collection = $collection_iterator->current();

          $group_count = count($collection->get_groups_array());

          $checked = ($collection_id==$collection->id) ? 'checked="checked"' : '' ;

          echo('<tr>');
          echo("  <td><input type=\"radio\" name=\"collection_id\" id=\"collection_{$collection->id}\" value=\"{$collection->id}\" $checked /></td>");
          echo("  <td><label class=\"small\" for=\"collection_{$collection->id}\">{$collection->name}</label>");
          echo("  <div style=\"margin-left: 10px; font-size: 84%;\"><div>Number of Groups : $group_count</div></div></td>");
          echo('</tr>');
        }
?>
        </table>
      </div>
<?php
    }
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('collection_id',fetch_POST('collection_id'));
    if (is_empty($this->wizard->get_field('collection_id'))) { $errors[] = 'You must select a collection of groups to use in your new assessment'; }

    return $errors;
  }// /->process_form()

}// /class: WizardStep3

?>
