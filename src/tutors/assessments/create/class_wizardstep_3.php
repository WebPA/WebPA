<?php
/**
 * Class : WizardStep3    (Create new assessment wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\SimpleObjectIterator;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep3
{
    public $wizard;

    public $step = 3;

    private $module;

    private $groupHandler;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->module = $this->wizard->get_var('module');

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    public function head()
    {
        ?>
        <script language="JavaScript" type="text/javascript">
          <!--

          function body_onload () {
          }// /body_onload()

          //-->
        </script>
        <?php
    }

    // /->head()

    public function form()
    {
        $group_handler = new GroupHandler();
        $collections = $group_handler->get_module_collections($this->module['module_id']);

        $collection_id = $this->wizard->get_field('collection_id');

        if (!$collections) {
            $this->wizard->next_button = ''; ?>
            <p>You haven't yet created any group collections.</p>
            <p>You need to <a href="../../groups/create/">create some groups</a> before you will be able to run any peer
                assessments.</p>
            <?php
        } else {
            $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', $this->wizard->get_var('db')); ?>
            <p>Please select the collection of groups you wish to use in this assessment from the list below.</p>
            <p>The collection you select will be copied into your new assessment. Subsequent changes to the collection
                of groups <strong>will not</strong> affect your assessment.</p>
            <h2>Your collections</h2>
            <div class="form_section">
                <table class="form" cellpadding="0" cellspacing="0">
                    <?php
                    if ($collection_iterator->size() == 1) {
                        $collection = $collection_iterator->current();
                        $collection_id = $collection->id;
                        unset($collection);
                    }
            for ($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next()) {
                $collection = $collection_iterator->current();

                $group_count = count($collection->get_groups_array());

                $checked = ($collection_id == $collection->id) ? 'checked="checked"' : '';

                echo '<tr>';
                echo "  <td><input type=\"radio\" name=\"collection_id\" id=\"collection_{$collection->id}\" value=\"{$collection->id}\" $checked /></td>";
                echo "  <td><label class=\"small\" for=\"collection_{$collection->id}\">{$collection->name}</label>";
                echo "  <div style=\"margin-left: 10px; font-size: 84%;\"><div>Number of Groups : $group_count</div></div></td>";
                echo '</tr>';
            } ?>
                </table>
            </div>
            <?php
        }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('collection_id', Common::fetch_POST('collection_id'));
        if (empty($this->wizard->get_field('collection_id'))) {
            $errors[] = 'You must select a collection of groups to use in your new assessment';
        }

        return $errors;
    }
}
