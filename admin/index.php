<?php
/**
 * 
 * Landing page for the admin section
 * 
 * This is the landing page for the administration section and acts as a gate way
 * to the other sections within this area of the site.
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 28 Mar 2007
 * 
 */
 
 //get the include file required
 require_once("../include/inc_global.php");
 
 if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}
 
 //set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'admin home';
$UI->breadcrumbs = array ('home' => null);
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();
//build the content to be written to the screen

$page_intro = 'Welcome to the Administration Area for WebPA. In this section you are able to manage the users of the system (both adding new and editing existing) as well as generate basic reports on the usage of WebPA (the metrics).<br/><br/>The admin area contains the following sections:';
$section_name = array(0=>'Upload Data',
					  'View Data',
					  'WebPA Metrics'); 
$section_definition = array(0=>'This is where you can upload the data to the system.', 
							'This area allows you to view the uploaded data as well as search and edit user information.',
							'This section allows you to generate reports on the usage of WebPA locally.'); 
$section_link = array(0=>'./load/',
					  './review/',
					  './metrics/');
?>
<p><?php echo $page_intro; ?></p>



<table class="option_list" style="width: 500px;">
<tr>
	<td><a href="<?php echo $section_link[0]; ?>"><img src="../images/icons/load_data.gif" width="32" height="32" alt="" /></a></td>
	<td>
		<div class="option_list">
			<div class="option_list_title"><a class="hidden" href="<?php echo $section_link[0]; ?>"><?php echo $section_name[0]; ?></a></div>
			<p><?php echo $section_definition[0]; ?></p>
		</div>
	</td>
</tr>
<tr>
	<td><a href="<?php echo $section_link[1]; ?>"><img src="../images/icons/view_data.gif" width="32" height="32" alt="" /></a></td>
	<td>
		<div class="option_list">
			<div class="option_list_title"><a class="hidden" href="<?php echo $section_link[1]; ?>"><?php echo $section_name[1]; ?></a></div>
			<p><?php echo $section_definition[1]; ?></p>		
		</div>
	</td>
</tr>
<tr>
	<td><a href="<?php echo $section_link[3]; ?>"><img src="../images/icons/chart_line.png" width="32" height="32" alt="" /></a></td>
	<td>
		<div class="option_list">
			<div class="option_list_title"><a class="hidden" href="<?php echo $section_link[2]; ?>"><?php echo $section_name[2]; ?></a></div>
			<p><?php echo $section_definition[2]; ?></p>		
		</div>
	</td>
</tr>
</table>

<?php
$UI->content_end();

?>
