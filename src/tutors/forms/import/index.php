<?php
/**
 * Interface that allows the user to import a XML form
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

$UI->page_title = APP__NAME . ' load form';
$UI->menu_selected = 'my forms';
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = ['home'      => '../../',
            'my forms'  => '../',
            'load form' => null, ];

$UI->set_page_bar_button('List Forms', '../../../../images/buttons/button_form_list.gif', '../');
$UI->set_page_bar_button('Create a new Form', '../../../../images/buttons/button_form_create.gif', '../create/');
$UI->set_page_bar_button('Clone a Form', '../../../../images/buttons/button_form_clone.gif', '../clone/');
$UI->set_page_bar_button('Import a Form', '../../../../images/buttons/button_form_import.gif', '../import/');

$UI->head();
$UI->body();

$UI->content_start();
?>
<p>Here you can import forms which have been exported from the WebPA system.</p>
<div class="content_box">
  <h2>load form by 'cut and paste'</h2>
  <p>
    Please copy and paste the contents of the form file into the text area below and click the <i>load</i> button.
  </p>

  <form name="frmXml" action="import.php" method="get" enctype="text/plain">

    <textarea cols="80" rows="20" name="txtXml"></textarea><br/>
    <input type="submit" name="btnLoad" value="load"/>

  </form>
</div>

<div class="content_box">
  <h2>load form by XML file</h2>
  <p>
    Browse for the XML file on your computer and click the <i>load</i> button.
  </p>


  <form name="frmXml" enctype="multipart/form-data" action="xml_file.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
    <input name="uploadedfile" type="file" /><br/>
    <input type="submit" name="btnLoad" value="load"/>

  </form>
</div>
<?php

$UI->content_end();

?>
