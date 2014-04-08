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
  function WizardStep1(&$wizard) {
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
    $module_select = $this->wizard->get_field('module_select');
?>
    <p>Firstly, we need to give this new collection of groups a name. To avoid confusion, the name should be unique, but you can create collections using the same name if you wish.</p>
    <p>The name should be describe what the groups are for. For example, if the students are doing coursework for module 05ABC123, then name the collection, <em>05ABC123 - Coursework Groups</em>.</p>

    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="collection_name">Name for this new collection</label></th>
      <td><input type="text" name="collection_name" id="collection_name" maxlength="50" size="40" value="<?php echo( $this->wizard->get_field('collection_name') ); ?>" /></td>
    </tr>
    </table>

<?php
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('collection_name', fetch_POST('collection_name'));
    if (is_empty($this->wizard->get_field('collection_name'))) {
      $errors[] = 'You must provide a name for your new collection of groups';
    }

    return $errors;
  }// /->process_form()

}// /class: WizardStep1

?>
