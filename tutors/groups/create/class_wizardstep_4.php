<?php
/**
 *
 * Class : WizardStep4  (Create new groups wizard)
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class WizardStep4 {

  // Public
  public $wizard = null;
  public $step = 4;

  /*
  * CONSTRUCTOR
  */
  function WizardStep4(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = null;
    $this->wizard->next_button = null;
    $this->wizard->cancel_button = null;
  }// /WizardStep4()

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

    global $_module_id;

    $CIS = $this->wizard->get_var('CIS');
    $db = $this->wizard->get_var('db');
    $user = $this->wizard->get_var('user');
    $config = $this->wizard->get_var('config');

    $group_handler = new GroupHandler();

    // Run a load of checks to see if we can create these new groups!
    $errors = null;

    // If the staff isn't allocated some of the modules given (hack-attempt), show error

    // Generate the names of the new groups
    $num_groups = (int) $this->wizard->get_field('num_groups');
    if ($num_groups>0) {
      $group_names = $group_handler->generate_group_names($num_groups, $this->wizard->get_field('group_name_stub'), $this->wizard->get_field('group_numbering'));
    }

    $collection =& $group_handler->create_collection();
    $collection->module_id = $_module_id;
    $collection->name = $this->wizard->get_field('collection_name');

    // If errors, show them
    if (is_array($errors)) {
      $this->wizard->back_button = gettext('&lt; Back');
      $this->wizard->cancel_button = gettext('Cancel');
      echo('<p><strong>'.gettext('Unable to create your new collection of groups.').'</strong></p>');
      echo('<p>'.gettext('To correct the problem, click <em>back</em> and amend the details entered.').'</p>');
    } else {// Else.. create the groups!
      if ($collection->save()) {
        if ($num_groups>0) {
          foreach($group_names as $group_name) {
            $new_group = $collection->new_group($group_name);
            $new_group->save();
          }
        }
      } else {
        echo('<p><strong>'.gettext('An error occurred while trying to create your new collection of groups.').'</strong></p>');
        echo('<p>'.gettext('You may be able to correct the problem by clicking <em>back</em>, and then <em>next</em> again.').'</p>');
      }

?>
      <p><strong><?php echo gettext('Your new groups have been created.');?></strong></p>
      <p style="margin-top: 20px;"><?php echo sprintf(gettext('To allocate students to your new groups, you can use the <a href="../edit/edit_collection.php?c=%s">group editor</a>.'), $collection->id);?></p>
      <p style="margin-top: 20px;"><?php echo gettext('Alternatively, you can return to <a href="../">my groups</a>, or to the <a href="../../index.php">WebPA home page</a>.');?></p>
<?php
    }
  }// /->form()

  function process_form() {
    $this->wizard->_fields = array(); // kill the wizard's stored fields
    return null;
  }// /->process_form()

}// /class: WizardStep4

?>
