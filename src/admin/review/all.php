<?php
/**
 * Generic retrieval code
 *
 * Retrieves information from the database on specific information
 * This page is included within other pages, to allow quick reuse
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use Doctrine\DBAL\ParameterType;
use WebPA\includes\functions\Common;

//build the string for the information to be collected from the database
$dbConn = $DB->getConnection();

if ($type === 'module') {
    if (Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
        $stmt = $dbConn->prepare('SELECT module_id, module_code, module_title FROM ' . APP__DB_TABLE_PREFIX . 'module WHERE source_id = ?');

        $stmt->bindParam(1, $_source_id);
    }
} elseif ($type == APP__USER_TYPE_ADMIN) {
    if (Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
        $stmt = $dbConn->prepare('SELECT u.user_id, u.source_id, u.username AS id, u.lastname, u.forename, u.email, u.id_number AS `id number`, u.date_last_login AS `last login` FROM ' .
        APP__DB_TABLE_PREFIX . 'user u ' .
        'WHERE u.admin = 1 ' .
        'ORDER BY u.lastname, u.forename, u.source_id, u.username');
    }
} elseif (Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    $_module_id = Common::fetch_SESSION('_module_id', null);

    $query =
      'SELECT u.user_id, u.source_id, u.username AS id, u.lastname, u.forename, u.email, u.id_number AS `id number`, u.date_last_login AS `last login` ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'user u ' .
      'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
      'ON u.user_id = um.user_id ' .
      'WHERE um.module_id = ? AND um.user_type = ? ' .
      'ORDER BY u.lastname, u.forename, u.source_id, u.username';

    $stmt = $dbConn->prepare($query);

    $stmt->bindParam(1, $_module_id, ParameterType::INTEGER);
    $stmt->bindParam(2, $type);
}
if (!isset($stmt)) {
    echo ' <p>You need to be logged into the system to see this information.</p>';
} else {
    $rs = $stmt->execute()->fetchAllAssociative();

    echo '<h2>' . $rstitle . '</h2>';
    echo '<div class="obj">';
    echo '<table class="obj" cellpadding="2" cellspacing="2">';

    //work through the recordset if it is not empty
    for ($recordcounter = 0; $recordcounter < count($rs); $recordcounter++) {
        if ($recordcounter == 0) {
            //write the table field headers to the screen
            echo '<tr>';
            foreach ($rs[$recordcounter] as $field_name => $field_value) {
                if ($field_name == 'source_id') {
                } elseif (($field_name != 'user_id') && ($field_name != 'module_id')) {
                    echo "<th>{$field_name}</th>";
                } elseif (($type == 'module') || !$_source_id) {
                    echo '<th class="icon">&nbsp;</th>';
                }
            }
            echo "</tr>\n";
        }
        if (($type != 'module') && ($rs[$recordcounter]['source_id'] != $_user_source_id)) {
            $style = ' style="background-color: #c0c0c0;"';
        } else {
            $style = '';
        }
        echo "<tr{$style}>";
        $consumer_instance_guid = null;
        $uid = '';
        foreach ($rs[$recordcounter] as $field_name => $field_value) {
            if (($field_name == 'id') && ($type != 'module')) {
                $field_value = "<a href=\"../log.php?u={$uid}\" title=\"View activity log\">{$field_value}</a>";
            }
            if ($field_name == 'source_id') {
                $source_id = $field_value;
            } elseif ($field_name == 'user_id') {
                $uid = $field_value;
                if (!$_source_id) {
                    echo '<td class="icon">';
                    echo '<a href="../../edit/index.php?u=' .$field_value . '&amp;t=' . $type . '">';
                    echo '<img src="../../../images/buttons/edit.gif" width="16" height="16" alt="Edit user" title="Edit user" /></a>';
                    if (APP__ENABLE_USER_DELETE) {
                        if ($_SESSION['_user_id'] != $field_value) {
                            echo '&nbsp;<a href="../../delete/index.php?u=' .$field_value . '" onclick="return confirm(\'Delete user; are you sure?\');">';
                            echo '<img src="../../../images/buttons/cross.gif" width="16" height="16" alt="Delete user" title="Delete user" /></a>';
                        } else {
                            echo '<img src="../../../images/buttons/blank.gif" width="16" height="16" alt="" />';
                        }
                    }
                    echo '</td>';
                }
            } elseif ($field_name == 'module_id') {
                echo '<td class="icon">';
                if (!$_source_id) {
                    echo '<a href="../../edit/module.php?m=' .$field_value . '">';
                    echo '<img src="../../../images/buttons/edit.gif" width="16" height="16" alt="Edit module" title="Edit module" /></a>';
                } else {
                    echo '<img src="../../../images/buttons/blank.gif" width="16" height="16" alt="" />';
                }
                if (APP__ENABLE_MODULE_DELETE) {
                    if ($_SESSION['_module_id'] != $field_value) {
                        echo '<a href="../../delete/index.php?m=' .$field_value . '" onclick="return confirm(\'Delete module; are you sure?\');">';
                        echo '<img src="../../../images/buttons/cross.gif" width="16" height="16" alt="Delete module" title="Delete module" /></a>';
                    } else {
                        echo '<img src="../../../images/buttons/blank.gif" width="16" height="16" alt="" />';
                    }
                } else {
                    echo '<img src="../../../images/buttons/blank.gif" width="16" height="16" alt="" />';
                }
                echo '</td>';
            } elseif ($field_name == 'enabled') {
                echo '<td class="obj_info_text">';
                if ($field_value == 1) {
                    echo 'Yes';
                } else {
                    echo 'No';
                }
                echo '</td>';
            } elseif (strlen($field_value) <= 0) {
                echo '<td class="obj_info_text">&nbsp;</td>';
            } elseif (($field_name == 'id') && $style) {
                echo "<td class=\"obj_info_text\"><span title=\"{$source_id}\">{$field_value}</span></td>";
            } elseif ($field_name == 'email') {
                echo "<td class=\"obj_info_text\"><a href=\"mailto:'{$field_value}\">{$field_value}</a></td>";
            } else {
                echo "<td class=\"obj_info_text\">{$field_value}</td>";
            }
        }
        echo "</tr>\n";
    }
    if (count($rs) <= 0) {
        echo "<tr><td>No records</td></tr>\n";
    }

    echo '</table>';
    echo '</div>';
    if ($_source_id == '') {
        if ($type == 'module') {
            echo '<form action="../../edit/module.php?m=" method="GET"><p><input type="submit" value="Create new module" /></p></form>';
        } else {
            echo '<form action="../../edit/index.php" method="GET"><p><input type="hidden" name="u" value="" /><input type="hidden" name="t" value="' . $type . '" /><input type="submit" value="Add new ' . $user_type . '" /></p></form>';
        }
    }
}
