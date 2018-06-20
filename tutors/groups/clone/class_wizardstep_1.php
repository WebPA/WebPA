<?php
/**
 *
 * Class : WizardStep1  (Create new groups wizard)
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class WizardStep1 {

  // Public
  public $wizard = null;
  public $step = 1;

  /*
  * CONSTRUCTOR
  */
  function __construct(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = null;
    $this->wizard->next_button = 'Next &gt;';
    $this->wizard->cancel_button = 'Cancel';
  }// /WizardStep1()

  function head() {
    $html = <<<HTMLEnd
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
    document.getElementById('collection_name').focus();
  }// /body_onload()

//-->
</script>
HTMLEnd;

    echo($html);
  }// /->head()

  function form() {
    $config = $this->wizard->get_var('config');
    $module = $this->wizard->get_var('module');
    $group_handler = $this->wizard->get_var('group_handler');

    require_once("../../../includes/classes/class_simple_object_iterator.php");

    $collections = $group_handler->get_module_collections($module, $config['app_id']);

    $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', "\$GLOBALS['group_handler']->_DAO");

    if ($collection_iterator->size()==0) {
?>
      <p>You have not created any groups, so there are none to clone.</p>

<?php
      $this->wizard->next_button = null;
    } else {
?>
      <h2>Your Groups</h2>
      <p>Firstly, you need to choose a collection of groups to clone. Please select which you want to clone:</p>
      <div class="form_section">
        <table class="form" cellpadding="1" cellspacing="1">
<?php
      for($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next() ) {
        $collection = $collection_iterator->current();

        $group_count = count($collection->get_groups_array());
        $group_plural = ($group_count==1) ? 'group' : 'groups';

        $checked_str = ($collection->id==$this->wizard->get_field('collection_id')) ? 'checked="checked"' : '' ;
        echo('<tr>');
        echo("<td><input type=\"radio\" name=\"collection_id\" id=\"collection_{$collection->id}\" value=\"{$collection->id}\" $checked_str /></td>");
        echo("<td><label style=\"font-weight: normal;\" for=\"collection_{$collection->id}\">{$collection->name} &nbsp; ($group_count $group_plural)</label></td>");
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
    if (is_empty($this->wizard->get_field('collection_id'))) {
      $errors[] = 'You must select a collection of groups to clone.';
    }

    return $errors;
  }// /->process_form()

}// /class: WizardStep1

?>
