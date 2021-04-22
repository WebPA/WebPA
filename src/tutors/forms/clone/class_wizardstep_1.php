<?php
/**
 * Class : WizardStep1  (Clone a form wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep1
{
    public $wizard;

    public $step = 1;

    private $moduleId;

    private $user;

    private $sourceId;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->moduleId = $this->wizard->get_var('moduleId');
        $this->user = $this->wizard->get_var('user');
        $this->sourceId = $this->wizard->get_var('sourceId');


        $this->wizard->back_button = null;
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep1()

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
        $DB = $this->wizard->get_var('db');
        $user = $this->wizard->get_var('user');

        $form_id = $this->wizard->get_field('form_id');

        $forms = [];

        if (!$this->user->is_admin()) {
            $sql =
                'SELECT f.*, m.module_id, m.module_title ' .
                'FROM ' . APP__DB_TABLE_PREFIX . 'form f ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'form_module fm ' .
                'ON f.form_id = fm.form_id ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
                'ON fm.module_id = um.module_id ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ' .
                'ON um.module_id = m.module_id ' .
                'WHERE um.user_id = ? ' .
                'ORDER BY f.form_name ASC';

            $forms = $DB->getConnection()->fetchAllAssociative($sql, [$user->id], [ParameterType::INTEGER]);
        } else {
            $sql =
                'SELECT f.*, m.module_id, m.module_title ' .
                'FROM ' . APP__DB_TABLE_PREFIX . 'form f ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'form_module fm ' .
                'ON f.form_id = fm.form_id ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ' .
                'ON fm.module_id = m.module_id ' .
                'WHERE m.source_id = ? ' .
                'ORDER BY f.form_name ASC';

            $forms = $DB->getConnection()->fetchAllAssociative($sql, [$this->sourceId], [ParameterType::STRING]);
        }

        if (!$forms) {
            $this->wizard->next_button = null; ?>
            <p>You have not created any forms yet, so you cannot select one to clone.</p>
            <p>Please <a href="../create/">create a new form</a> instead.</p>
            <?php
        } else {
            ?>
            <p>To create a clone you must first select which assessment form you wish to copy. Please choose one from
                the list below.</p>

            <h2>Choose a form to clone</h2>
            <div class="form_section">
                <table class="form" cellpadding="2" cellspacing="2">
                    <?php

                    foreach ($forms as $i => $form) {
                        $checked_str = ($form['form_id'] == $form_id) ? ' checked="checked"' : '';
                        $title_str = ($form['module_id'] == $this->moduleId) ? '' : " [{$form['module_title']}]"; ?>
                        <tr>
                            <td><input type="radio" name="form_id" id="form_id_<?php echo $form['form_id']; ?>"
                                       value="<?php echo $form['form_id']; ?>"<?php echo $checked_str; ?>/></td>
                            <th style="text-align: left"><label class="small"
                                                                for="form_id_<?php echo $form['form_id']; ?>"><?php echo "{$form['form_name']}{$title_str}"; ?></label>
                            </th>
                        </tr>
                        <?php
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

        $this->wizard->set_field('form_id', Common::fetch_POST('form_id'));
        if (empty($this->wizard->get_field('form_id'))) {
            $errors[] = 'You must select which assessment form you wish to clone.';
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep1

?>
