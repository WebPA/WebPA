<?php
/**
 * Clone example form
 *
 * This page allows the user to clone the example form for their own use.
 * If the user was left to access the form, they or others using the system
 * could potentially change information that is used in live assessments.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Form;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

//get the form ID
$formid = Common::fetch_GET('f', Common::fetch_POST('form_id'));
$new_form_name = Common::fetch_POST('n');
$form_id = Common::fetch_POST('f');

if (empty($form_id)) {
    $form_id = $formid;
}

if (empty($formid)) {
    $formid = $form_id;
}

//get the form information
$form = new Form($DB);
$form->load($form_id);

if (!empty($new_form_name)) {
    //we are going to clone the form and then pass the user to the view area
    $clone = $form->get_clone();
    $clone->name = $new_form_name;
    $clone->owner_id = Common::fetch_SESSION('_user_id', null);
    $clone->save();

    header("Location: ../edit/edit_form.php?f={$clone->id}") ;
    exit;
}

  // Begin Page

    $UI->page_title = APP__NAME . ' Copy ' . $form->name .' form';
    $UI->menu_selected = 'my forms';
    $UI->help_link = '?q=node/244';
    $UI->breadcrumbs = ['home'           => '../../',
               'my forms'       => '../',
               'copy form'  => null, ];

    $UI->set_page_bar_button('List Forms', '../../../../images/buttons/button_form_list.gif', '../');
    $UI->set_page_bar_button('Create Form', '../../../../images/buttons/button_form_create.gif', '../create/');
    $UI->set_page_bar_button('Clone a Form', '../../../../images/buttons/button_form_clone.gif', '../clone/');
    $UI->set_page_bar_button('Import a Form', '../../../../images/buttons/button_form_import.gif', 'import/');

    $UI->head();
    $UI->body('onload="body_onload()"');
    $UI->content_start(); ?>
    <p>You have chosen to take a copy of the : <em><?php echo $form->name; ?></em> form.</p>

      <p>Now enter a name for your copy of the form.</p>
<form action="clone_example.php" method="POST">
      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
          <tr>
            <th><label for="clone_form_name">Name for new form</label></th>
            <td><input type="text" name="n" id="clone_form_name" size="50" maxlength="100" value="<?php echo $form->name; ?>" /></td>
          </tr>
        </table>

  <input type="hidden" name="f" value="<?php echo $formid; ?>" size="40" maxlength="40"/>


 <!-- <td class="button"><a href="<?php echo "clone_example.php?f={$formid}&n="; ?>">OK</a></td> -->

  <input type="submit" name="ok" value="ok"/>



      </div>
</form>
<?php
  $UI->content_end();

?>
