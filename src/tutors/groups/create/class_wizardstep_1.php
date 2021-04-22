<?php
/**
 * Class : WizardStep1  (Create new groups wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep1
{
    public $wizard;

    public $step = 1;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->wizard->back_button = null;
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep1()

    public function head()
    {
        $html = <<<HTMLEnd
            <script language="JavaScript" type="text/javascript">
            <!--

              function body_onload() {
                document.getElementById('collection_name').focus();
              }// /body_onload()

            //-->
            </script>
            HTMLEnd;

        echo $html;
    }

    // /->head()

    public function form()
    {
        $module_select = $this->wizard->get_field('module_select'); ?>
    <p>Firstly, we need to give this new collection of groups a name. To avoid confusion, the name should be unique, but you can create collections using the same name if you wish.</p>
    <p>The name should be describe what the groups are for. For example, if the students are doing coursework for module 05ABC123, then name the collection, <em>05ABC123 - Coursework Groups</em>.</p>

    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="collection_name">Name for this new collection</label></th>
      <td><input type="text" name="collection_name" id="collection_name" maxlength="50" size="40" value="<?php echo $this->wizard->get_field('collection_name'); ?>" /></td>
    </tr>
    </table>

<?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('collection_name', Common::fetch_POST('collection_name'));
        if (empty($this->wizard->get_field('collection_name'))) {
            $errors[] = 'You must provide a name for your new collection of groups';
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep1

?>
