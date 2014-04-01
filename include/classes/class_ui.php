<?php
/**
 *
 * Class :  UI
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.2
 * @link http://
 * @since 24/05/2005
 *
 */

//include main global file so that the session can be used
 function rel7($struc, &$file) {
 	return file_exists( ( $file = ( dirname($struc).'/'.$file ) ) );
 }

 function relativetome7($structure, $filetoget){
 	return rel7($structure,$filetoget) ? require_once($filetoget) : null;
 }

relativetome7(__FILE__, 'inc_global.php');

class UI {
	// Public Vars
	public $page_title = '';
	public $menu_selected = '';
	public $breadcrumbs = null;
	public $help_link = '';

	// Private Vars
	private $_user = null;
	private $_menu = null;
	private $_page_bar_buttons = null;

	/**
	* CONSTRUCTOR for the UI
	* @param string $_user
	*/
	function UI( $_user = null) {
		$this->_user =& $_user;

		$helper_link = APP__HELP_LINK;


		// Initialise the menu - sets either staff or student menu items
		if ( ($this->_user) && ($this->_user->is_staff()) ) {
			// Staff menu
			$this->set_menu('Tutors', array ('home'				=> APP__WWW . '/tutors/index.php' ,
											 'my forms'			=> APP__WWW . '/tutors/forms/' ,
											 'my groups'		=> APP__WWW . '/tutors/groups/' ,
											 'my assessments'	=> APP__WWW . '/tutors/assessments/' ,)	);// /$this->set_menu()

			$this->set_menu('Support', array ('help'		=>  $helper_link,	//this is a link set in each page / area to link to the approriate help
											  'contact'   => APP__WWW . '/contact/') );// /$this->set_menu();

			//Admin menu
			if ($_SESSION['_admin'] == '1'){
				$this->set_menu('Admin', array('admin home'		=> APP__WWW .'/admin/index.php',
										       'upload data'	=>	APP__WWW . '/admin/load/index.php',
										       'view data'		=>	APP__WWW . '/admin/review/index.php',
										       'metrics'		=>	APP__WWW . '/admin/metrics/index.php'));
			}
		} else  {
			// Student menu
			$this->set_menu('Students', array ('home'			=> APP__WWW . '/students/index.php' ,
											   'my groups'		=> APP__WWW . '/students/groups/' ,
											   'my assessments'	=> APP__WWW . '/students/assessments/' ) );// /$this->set_menu()

			$this->set_menu('Support', array ('help'		=> $helper_link,	//this is a link set in each page / area to link to the approriate help
											  'contact'	=> APP__WWW . '/contact/') );// /$this->set_menu();
		}


		$this->set_menu(' ', array ('logout'	=> APP__WWW .'/logout.php') );// /$this->set_menu();
	}// /->UI()


	// --------------------------------------------------------------------------------
	// Public Methods

	/**
	* Send the expiry headers.
	* Leave $expiry_date empty to force the browser to page refresh
	* @param string $expire_date
	* @param string $modified_date
	*/
	function headers_expire($expire_date = null, $modified_date = null) {
		// If no expiry date, expire at 00:00:01 today
		if (!$expire_date) { $expire_date = mktime(0,0,1,date('m'),date('d'),date('Y')); }

		// If no modified date, modified today
		if (!$modified_date) { $modified_date = mktime(); }

		header('Expires: '. gmdate('D, d M Y H:i:s', $expire_date ) .' GMT');
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', $modified_date) .' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');		// HTTP/1.1
		header('Cache-Control: post-check=0, pre-check=0', false);		// HTTP/1.1
		header("Cache-control: private", false);
		header('Pragma: no-cache');		// HTTP/1.0
	} // /-headers_expire()


	/**
	 * Function to generate the header
	*/
  function head () {
		/*
		Commented out until the day IE can show a full XHTML page without entering quirks mode
		echo('<?xml version="1.0" encoding="UTF-8"?>'."\n");
		*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-language" content="EN" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link href="<?php echo(APP__WWW) ?>/css/webpa.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="<?php echo(APP__WWW) ?>/css/webpa_print.css" media="print" rel="stylesheet" type="text/css" />
	<title><?php echo(APP__NAME ) ?></title>

	<style type="text/css">
 		#app_bar {
 			height: <?php echo APP__INST_MARGIN;?>px;
 		}


  		#app_bar #inst_logo {
  			width: <?php echo APP__INST_WIDTH;?>px;
 		}

  		#main {
  			top:<?php echo APP__INST_HEIGHT - 20; ?>px;
 		}

 	</style>
<?php
  } // /->head()


	/**
	 * function to close the body area of the page
	 * @param string $extra_attributes
	*/
	function body($extra_attributes = '') {
		echo("\n</head>\n<body $extra_attributes>\n\n");

	} // /->body()


	/**
	* render page header
	*/
	function header() {
		?>
	<div id="header">
		<div id="app_bar">
			<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td width="175"><div id="title_logo"><a href=""><img src="<?php echo APP__WWW; ?>/images/tool/appbar_webpa_logo.png" alt="<?php echo APP__NAME; ?>" /></a></div></td>
				<?php
					if ($this->_user) {
						echo("<td>{$this->_user->forename} {$this->_user->surname}</td>");
					} else {
						echo('<td>&nbsp;</td>');
					}
				?>
				<td align="right"><div id="inst_logo"><img src="<?php echo APP__INST_LOGO; ?>" alt="<?php echo APP__INST_LOGO_ALT; ?>" /></div></td>
			</tr>
			</table>
		</div>
		<div id="breadcrumb_bar">
			You are in: <?php
				if (is_array($this->breadcrumbs)) {
					$num_crumbs = count($this->breadcrumbs);
					foreach( $this->breadcrumbs as $k => $v ) {
						--$num_crumbs;
						if (!is_null($v)) {
							echo("<a class=\"breadcrumb\" href=\"$v\">$k</a>");
							if ($num_crumbs>0) { echo(' &gt; '); }
						} else { echo($k); }
					}
				}
			?>
		</div>
	</div>
<?php
	}// /->header()


	/**
	* Set the given section name to the given assoc-array of links
	* Does NO checking of $section_array
	* @param string $section_name
	* @param array $section_array
	*/
	function set_menu($section_name, $section_array) {
		$this->_menu["$section_name"] = $section_array;
	}

	/**
	* Draw the menu
	*/
	function menu() {
		// If there's a menu, draw it
		if ($this->_menu) {
			$menu_html = '<div id="menu">';

			foreach($this->_menu as $menu_section => $menu_links) {
				$menu_html .= ($menu_section==' ') ? '<div class="menu_section"><ul class="menu_list">' : '<div class="menu_section"><div class="menu_title">'. $menu_section .'</div><ul class="menu_list">';

				foreach($menu_links as $menu_name => $menu_link ) {
					if ($menu_name == 'help'){
						$link_class = ($this->menu_selected == $menu_name) ? 'menu_selected' : 'menu';
						$menu_html .= '<li><a class="'. $link_class .'" href="'. $menu_link . $this->help_link . '" target="_blank">'. $menu_name .'</a></li>';
					}else{
						$link_class = ($this->menu_selected == $menu_name) ? 'menu_selected' : 'menu';
						$menu_html .= '<li><a class="'. $link_class .'" href="'. $menu_link .'">'. $menu_name .'</a></li>';
					}
				}// /for

				$menu_html .= '</ul></div>';
			}// /for

			$menu_html .= '</div>';
			echo($menu_html);
		}
	}// /->menu()


	/**
	* Set a page bar button
	* @param string $text
	* @param string $img
	* @param string  $link
	* @param string $side
	*/
	function set_page_bar_button($text, $img, $link, $side = 'left') {
		$this->_page_bar_buttons[$side][$text] = array ('img' => "../images/buttons/$img", 'link' => $link);

	}// /->set_page_bar_button()


	/**
	* Draw the page toolbar
	*/
	function page_bar() {
		if (is_array($this->_page_bar_buttons)) {
			?>
			<div id="page_bar">
				<table cellpadding="0" cellspacing="0">
				<tr>
					<?php
						if (array_key_exists('left',$this->_page_bar_buttons)) {
							foreach($this->_page_bar_buttons['left'] as $text => $button) {
								echo("<td><a class=\"page_bar_link\" href=\"{$button['link']}\" title=\"$text\"><img src=\"{$button['img']}\" alt=\"$text\" height=\"50\" /></a></td>");
							}
						}
					?>
					<td width="100%">&nbsp;</td>
					<?php
						// right-hand buttons are automatically set to target="_blank"
						if (array_key_exists('right',$this->_page_bar_buttons)) {
							foreach($this->_page_bar_buttons['right'] as $text => $button) {
								echo("<td><a class=\"page_bar_link\" href=\"{$button['link']}\" target=\"$text\" title=\"$text\"><img src=\"{$button['img']}\" alt=\"$text\" height=\"50\" /></a></td>");
							}
						}
					?>
				</tr>
				</table>
			</div>
			<?php
		}
	}// /->page_bar()


	/**
	* Footer
	*/
	function footer() {
	?>
	<div id="footer">
		<div style="margin-top: 50px; float: right;">
			<a href="http://webpa.ac.uk/" title="WebPA Project" target="_blank"><img src="<?php echo APP__WWW; ?>/images/partners.gif" alt="WebPA Project partner logos" /></a>
			<br />&copy; Loughborough University, 2005 -  <?php echo date('Y');?>
		</div>



		<iframe src="<?php echo  APP__WWW;; ?>/keep_alive.php" height="1" width="1" style="display: none;">keep alive</iframe>
	</div>
<?php
	}// /->footer()


	/**
	* Start main page content
	*/
	function content_start() {
		echo('<div id="main">');
		$this->page_bar();
		echo('<div id="content">');
		if ($this->page_title) { echo("<h1>{$this->page_title}</h1>\n\n"); }
	}// /content_start()


	/**
	* End main page content
	* @param boolean $render_menu
	* @param boolean $render_header
	* @param boolean $renders_footer
	*/
	function content_end($render_menu = true, $render_header = true, $render_footer = true) {
		?>
	</div>
</div>

<div id="side_bar">
<?php
if ($render_menu) {
	$this->menu();
	?>
		<div class="alert_box" style="margin: 40px 8px 8px 8px; font-size: 0.7em;">
			<p><strong>Technical Problem?</strong></p>
			<p>If you have a problem, find a bug or discover a technical problem in the system, <a href="<?php echo APP__WWW ?>/contact/index.php?q=bug">contact us</a> to report it!</p>
		</div>
	<?php
} else {
	?>
		<div class="alert_box" style="margin: 40px 8px 8px 8px; font-size: 0.7em;">
			<p><strong>Technical Problem?</strong></p>
			<p>If you have a problem, find a bug or discover a technical problem in the system, please <a href="mailto:<?php echo(APP__EMAIL_HELP); ?>" title="(email: <?php echo(APP__EMAIL__HELP); ?>)">email us</a> to report it!</p>
		</div>
	<?php
}
?>
</div>
<?php
	if ($render_header) { $this->header(); }
	if ($render_footer) { $this->footer(); }
?>
</body>
</html>
<?php
	}// /content_end()


	/**
	* function to draw the boxed list
	* @param string $list
	* @param string $box_class
	* @param string $header_text
	* @param string $footer_text
	*/
	function draw_boxed_list($list, $box_class, $header_text, $footer_text) {
		if (is_array($list)) {
			echo("<div class=\"$box_class\"><p style=\"font-weight: bold;\">$header_text</p><ul class=\"spaced\">");
			foreach($list as $item) { echo("<li>$item</li>");	}
			echo("</ul><p>$footer_text</p></div>");
		}
	}// ->draw_boxed_list()


	// --------------------------------------------------------------------------------
	// Private Methods

}// /class: UI


?>