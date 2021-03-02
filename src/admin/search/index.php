<?php
/**
 * Search area for looking up people within the system
 *
 * This is the landing page for the search section and acts as a gate way
 * to the other sections within this area of the site.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

//-------------------------------------------------------------------------
//process the form

$post_firstname = Common::fetch_GET('firstname');
$post_lastname = Common::fetch_GET('lastname');
$post_username = Common::fetch_GET('username');
$post_id_number = Common::fetch_GET('id_number');
$post_search = Common::fetch_GET('search');

$whereClauseSet = false;
$sMessage = '';

$dbConn = $DB->getConnection();

// set up the query builder
$queryBuilder = $dbConn->createQueryBuilder();

// write the base query
$queryBuilder->select(
    'u.user_id',
    'u.username AS id',
    'u.lastname',
    'u.forename',
    'u.email',
    'u.id_number AS id_number',
    'u.date_last_login AS last_login',
    'CASE u.admin WHEN 0 THEN COUNT(um.module_id) ELSE NULL END as modules'
)
        ->from(APP__DB_TABLE_PREFIX . 'user', 'u')
        ->leftJoin('u', APP__DB_TABLE_PREFIX . 'user_module', 'um', 'u.user_id = um.user_id')
        ->where('u.source_id = :source_id')
        ->setParameter('source_id', $_source_id);

if (!empty($post_search)) {
    //build the search string dependant on the data entered
    if (!empty($post_lastname)) {
        $queryBuilder->andWhere($queryBuilder->expr()->like('u.lastname', ':last_name'));
        $queryBuilder->addOrderBy('u.lastname');
        $queryBuilder->setParameter('last_name', $post_lastname . '%');

        $whereClauseSet = true;
    }

    if (!empty($post_firstname)) {
        $queryBuilder->andWhere($queryBuilder->expr()->like('u.forename', ':forename'));
        $queryBuilder->addOrderBy('u.forename');
        $queryBuilder->setParameter('forename', $post_firstname . '%');

        $whereClauseSet = true;
    }

    if (!empty($post_username)) {
        $queryBuilder->andWhere($queryBuilder->expr()->like('u.username', ':username'));
        $queryBuilder->addOrderBy('u.username');
        $queryBuilder->setParameter('username', $post_username . '%');

        $whereClauseSet = true;
    }

    if (!empty($post_id_number)) {
        $queryBuilder->andWhere($queryBuilder->expr()->like('u.id_number', ':id_number'));
        $queryBuilder->addOrderBy('u.id_number');
        $queryBuilder->setParameter('id_number', $post_id_number . '%');

        $whereClauseSet = true;
    }

    if ($whereClauseSet === true) {
        if (!Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
            $queryBuilder->andWhere('u.admin = 0');
        }

        // add the groupBy clause
        $queryBuilder->groupBy('u.user_id');
        $queryBuilder->addGroupBy('u.username');
        $queryBuilder->addGroupBy('u.lastname');
        $queryBuilder->addGroupBy('u.forename');
        $queryBuilder->addGroupBy('u.email');
        $queryBuilder->addGroupBy('u.id_number');
        $queryBuilder->addGroupBy('u.date_last_login');

        $rs = $queryBuilder->execute()->fetchAllAssociative();
    } else {
        //nothing has been entered that can be searched for
        $sMessage = '<p>You have not entered any information for the search<br/>Please check and re-try.</p>';
    }
}

//------------------------------------------------------------------------

//set the page information
$UI->page_title = APP__NAME . '  search for a user';
$UI->menu_selected = 'view data';
$UI->breadcrumbs = ['home' => '../review/', 'search'=>null];
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
if (Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    $UI->set_page_bar_button('View Admin Data', '../../../images/buttons/button_admin_user.png', '../review/admin/index.php');
    $UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
}
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', 'index.php');

$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();
//build the content to be written to the screen

$page_intro = '<p>Search the WebPA system for a user within the system</p>';
$page_description = '<p>Enter the any combination of the information below for the individual that you would like to locate in the WebPA system. The person being searched for can be a student or staff member. When you are ready click the "Search Button".</p>';
$rstitle = 'Search results';
?>
<?php echo $page_intro; ?>

<div class="content_box">
<?php
if (empty($sMessage)) {
    echo  '<h2>' . $rstitle . '</h2>';

    if (!empty($rs)) {
        echo '<div class="obj">';
        echo '<table class="obj" cellpadding="2" cellspacing="2">';
        //work through the recordset if it is not empty
        $recordcounter = 0;
        while ($recordcounter<=count($rs)-1) {
            if ($recordcounter == 0) {
                //write the table field headers to the screen
                echo '<tr>';
                foreach ($rs[$recordcounter] as $field_name => $field_value) {
                    if ($field_name == 'source_id') {
                    } elseif (($field_name != 'user_id') && ($field_name != 'module_id')) {
                        echo "<th>{$field_name}</th>";
                    } elseif (!$_source_id) {
                        echo '<th class="icon">&nbsp;</th>';
                    }
                }
                echo "</tr>\n";
            }
            echo '<tr>';
            foreach ($rs[$recordcounter] as $field_index => $field_name) {
                if ($field_index=='user_id') {
                    echo '<td class="icon" width="16">';
                    echo "<a href='../edit/index.php?u=" . $field_name . "'>";
                    echo '<img src="../../images/buttons/edit.gif" width="16" height="16" alt="Edit user" /></a>';
                    echo '</td>';
                } else {
                    echo '<td class="obj_info_text">'.$field_name.'</td>';
                }
            }
            echo '</tr>';
            $recordcounter++;
        }
        echo '</table>';
        echo '</div>';
    } else {
        if (!empty($post_search)) {
            echo '<div class="warning_box">The search has not found any matching information.</div>';
        }
        echo $page_description;
        echo '<form method="get" name="search" action="index.php">' ;
        echo '<table class="option_list" style="width: 500px;">';
        echo  '<tr><td><label for="firstname">First name</label></td><td><input type="text" id="firstname" name="firstname" ></td></tr>';
        echo  '<tr><td><label for="lastname">Last name</label></td><td><input type="text" id="lastname" name="lastname"></td></tr>';
        echo  '<tr><td><label for="username">Username</label></td><td><input type="text" id="username" name="username"></td></tr>';
        echo  '<tr><td><label for="id_number">ID number</label></td><td><input type="text" id="id_number" name="id_number"></td></tr>';
        echo  '<tr><td><input type="hidden" id="search" name="search" value="search"></td><td><input type="Submit" value="Search" id="Search"></td></tr>';
        echo '</table>';
        echo '</form>';
    }
} else {
    if (!empty($sMessage)) {
        echo "<div class=\"warning_box\">{$sMessage}</div>";
    }
    echo $page_description;
    echo '<form method="get" name="search" action="index.php">' ;
    echo '<table class="option_list" style="width: 500px;">';
    echo  '<tr><td><label for="lastname">Last name</label></td><td><input type="text" id="lastname" name="lastname"></td></tr>';
    echo  '<tr><td><label for="name">First name</label></td><td><input type="text" id="name" name="name" ></td></tr>';
    echo  '<tr><td><label for="username">Username</label></td><td><input type="text" id="username" name="username"></td></tr>';
    echo  '<tr><td><label for="id_number">ID number</label></td><td><input type="text" id="id_number" name="id_number"></td></tr>';
    echo  '<tr><td><input type="hidden" id="search" name="search" value="search"></td><td><input type="Submit" value="Search" id="Search"></td></tr>';
    echo '</table>';
    echo '</form>';
}
?>
</div>
<?php

$UI->content_end();

?>
