<?php
/**
 *
 * Class : WizardStep2  (Create new groups wizard)
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class WizardStep2 {

  // Public
  public $wizard = null;
  public $step = 2;

  /*
  * CONSTRUCTOR
  */
  function WizardStep2(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = gettext('&lt; Back');
    $this->wizard->next_button = gettext('Finish');
    $this->wizard->cancel_button = gettext('Cancel');
  }// /WizardStep2()

  function head() {
    $html = <<<HTMLEnd
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
  }// /body_onload()

//-->
</script>
HTMLEnd;

    echo($html);
  }// /->head()

  function form() {
    $group_handler = $this->wizard->get_var('group_handler');
    $collection = $group_handler->get_collection($this->wizard->get_field('collection_id'));

    if (is_empty($this->wizard->get_field('collection_name'))) {
      $this->wizard->set_field('collection_name', $collection->name);
    }
?>
    <p><?php echo gettext('You have chosen to clone:');?> <em><?php echo($collection->name); ?></em></p>
    <h2><?php echo gettext('Name of Clone');?></h2>
    <p><?php echo gettext('To avoid confusion, the name of your cloned collection of groups should be unique, but you can use the same name if you wish.');?></p>
    <p><?php echo gettext('The name should be describe what the groups are for. For example, if the students are doing coursework for module 05ABC123, then name the collection, <em>05ABC123 - Coursework Groups</em>.');?></p>

    <div class="form_section">
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="collection_name"><?php echo gettext('Name for this new collection');?></label></th>
        <td><input type="text" name="collection_name" id="collection_name" maxlength="50" size="40" value="<?php echo( $this->wizard->get_field('collection_name') ); ?>" /></td>
      </tr>
      </table>
    </div>
<?php
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('collection_name',fetch_POST('collection_name'));
    if (is_empty($this->wizard->get_field('collection_name'))) { $errors[] = gettext('You must provide a name for your new collection of groups'); }

    return $errors;
  }// /->process_form()

}// /class: WizardStep2

?>
