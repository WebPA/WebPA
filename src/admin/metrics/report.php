<?php
/**
 * General usage report — optimized with 2 queries instead of 7
 *
 * This page will supply a general report that provides information
 * on the usage of WebPA over the istalled period as well as over the current
 * academic year.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../includes/inc_global.php';

use WebPA\includes\classes\Cache;
use WebPA\includes\functions\AcademicYear;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

$year = (int) Common::fetch_POST('academic_year', Common::fetch_SESSION('year', AcademicYear::get_academic_year()));
$_SESSION['year'] = $year;

$academic_year = strval($year);
if (APP__ACADEMIC_YEAR_START_MONTH > 1) {
    $academic_year .= '/' . substr($year + 1, 2, 2);
}

$this_year = '-';
if (APP__ACADEMIC_YEAR_START_MONTH <= 10) {
    $this_year .= '0';
}
$this_year .= APP__ACADEMIC_YEAR_START_MONTH . '-01 00:00:00';
$next_year = strval($year + 1) . $this_year;
$this_year = strval($year) . $this_year;

//get the format to be used
$format = Common::fetch_post('format');

//get the reports that are to be generated
$assessments_run = Common::fetch_POST('assessments_run');
$assessment_groups = Common::fetch_POST('assessment_groups');
$assessment_students = Common::fetch_POST('assessment_students');

// $assessment_modules = fetch_POST('assessment_modules');
$assessment_feedback = Common::fetch_POST('assessment_feedback');
$assessment_respondents = Common::fetch_POST('assessment_respondents');

$assessment_modules = Common::fetch_POST('assessment_modules');
$assessment_students_thisyear = Common::fetch_POST('assessment_students_thisyear');
// $assessment_tutor_departments = fetch_POST('assessment_tutor_departments');

$this_accademic_year = AcademicYear::get_academic_year() . '-';
if (APP__ACADEMIC_YEAR_START_MONTH <= 10) {
    $this_accademic_year .= '0';
}
$this_accademic_year .= APP__ACADEMIC_YEAR_START_MONTH . '-01 00:00:00';

// formulate all the sql for the reports
// OPTIMIZED: 2 queries instead of 7
$dbConn = $DB->getConnection();

// Query 1: Single query to get ALL grouped metrics per assessment using correlated subqueries
$cache_key = "metrics_report:$year:" . md5($_source_id . $this_year . $next_year);

if (Cache::has($cache_key)) {
    $metrics = Cache::get($cache_key);
} else {
    $metrics_raw = $dbConn->fetchAllAssociative(
        'SELECT ' .
        '  a.assessment_name, ' .
        '  a.assessment_id, ' .
        '  m.module_code, ' .
        '  m.module_title, ' .
        '  a.open_date, ' .
        '  a.close_date, ' .
        '  (SELECT COUNT(*) FROM ' . APP__DB_TABLE_PREFIX . 'user_group g WHERE g.collection_id = a.collection_id) AS group_count, ' .
        '  (SELECT COUNT(DISTINCT ugm.user_id) FROM ' . APP__DB_TABLE_PREFIX . 'user_group ug INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ON ug.group_id = ugm.group_id WHERE ug.collection_id = a.collection_id) AS student_count, ' .
        '  (SELECT COUNT(DISTINCT um.user_id) FROM ' . APP__DB_TABLE_PREFIX . 'user_mark um WHERE um.assessment_id = a.assessment_id) AS respondent_count, ' .
        '  a.allow_feedback ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
        'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
        'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
        'ORDER BY a.close_date, a.open_date, a.assessment_name, a.assessment_id',
        [$_source_id, $this_year, $next_year]
    );

    // Build lookup: [assessment_name] => [all metrics for this assessment]
    $metrics = [];
    foreach ($metrics_raw as $row) {
        $name = $row['assessment_name'];
        if (!isset($metrics[$name])) {
            $metrics[$name] = [
                'assessment_name' => $name,
                'module_code' => $row['module_code'],
                'module_title' => $row['module_title'],
                'open_date' => $row['open_date'],
                'close_date' => $row['close_date'],
                'group_count' => (int) $row['group_count'],
                'student_count' => (int) $row['student_count'],
                'respondent_count' => (int) $row['respondent_count'],
                'allow_feedback' => (int) $row['allow_feedback'],
            ];
        }
    }

    Cache::set($cache_key, $metrics);
}

// Query 2: Total unique students assessed this year
$total_students = Cache::get("total_students:$year:" . md5($_source_id . $this_year . $next_year));
if ($total_students === null) {
    $total_students = $dbConn->fetchOne(
        'SELECT COUNT(DISTINCT ugm.user_id) AS "Total unique students assessed" ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
        'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group ug ON a.collection_id = ug.collection_id ' .
        'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ON ug.group_id = ugm.group_id ' .
        'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
        'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ?',
        [$_source_id, $this_year, $next_year]
    );
    Cache::set("total_students:$year:" . md5($_source_id . $this_year . $next_year), $total_students);
}

// Build distinct modules list for the modules report
$modules_list = Cache::get("modules_list:$year:" . md5($_source_id . $this_year . $next_year));
if ($modules_list === null) {
    $modules_list = $dbConn->fetchAllAssociative(
        'SELECT DISTINCT m.module_code, m.module_title ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
        'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
        'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
        'ORDER BY m.module_code',
        [$_source_id, $this_year, $next_year]
    );
    Cache::set("modules_list:$year:" . md5($_source_id . $this_year . $next_year), $modules_list);
}

// Helper: output a table from $rows with given $columns
function output_table($rows, $columns, $format)
{
    if (!$rows) {
        if ($format === 'html') {
            echo '<p>None</p>';
        }
        return;
    }
    if ($format === 'html') {
        echo '<table class="grid">';
        foreach ($columns as $col) {
            echo '<th>' . htmlspecialchars((string) $col, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($columns as $col) {
                echo '<td>' . htmlspecialchars((string) $row[$col]) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } elseif ($format === 'csv') {
        echo '"' . implode('","', $columns) . "\"\n";
        foreach ($rows as $row) {
            echo '"' . implode('","', array_map(fn($c) => (string) $row[$c], $columns)) . "\"\n";
        }
    } elseif ($format === 'rtf') {
        echo implode("  ", $columns) . "\n";
        foreach ($rows as $row) {
            echo implode("  ", array_map(fn($c) => (string) $row[$c], $columns)) . "\n";
        }
    } elseif ($format === 'xml') {
        echo '<metrics>';
        echo "<description>Table columns: " . implode(", ", $columns) . "</description>";
        foreach ($rows as $row) {
            echo '<metric>';
            $i = 0;
            foreach ($columns as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$row[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }
}

//-------------------------------------------------------
// view on screen
if ($format == 'html') {
    //set the page information
    $UI->page_title = APP__NAME . ' general usage report';
    $UI->menu_selected = 'metrics';
    $UI->breadcrumbs = ['home' => '../../'];
    $UI->help_link = '?q=node/237';
    $UI->head();
    $UI->body();
    $UI->content_start();

    echo '<div class="content_box">';

    if (!empty($assessments_run)) {
        echo "<h2>Assessments run in WebPA ($academic_year)</h2>";
        output_table($metrics, ['assessment_name', 'module_code', 'module_title', 'open_date', 'close_date'], 'html');
    }

    if (!empty($assessment_groups)) {
        $groups_data = [];
        foreach ($metrics as $m) {
            $groups_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'group_count' => $m['group_count'],
            ];
        }
        echo "<h2>Number of groups per assessment ($academic_year)</h2>";
        output_table($groups_data, ['assessment_name', 'module_code', 'module_title', 'group_count'], 'html');
    }

    if (!empty($assessment_students)) {
        $students_data = [];
        foreach ($metrics as $m) {
            $students_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'student_count' => $m['student_count'],
            ];
        }
        echo "<h2>Number of students per assessment ($academic_year)</h2>";
        output_table($students_data, ['assessment_name', 'module_code', 'module_title', 'student_count'], 'html');
    }

    if (!empty($assessment_feedback)) {
        $feedback_data = [];
        foreach ($metrics as $m) {
            if ($m['allow_feedback']) {
                $feedback_data[] = [
                    'assessment_name' => $m['assessment_name'],
                    'module_code' => $m['module_code'],
                    'module_title' => $m['module_title'],
                ];
            }
        }
        echo "<h2>Assessments where feedback has been used ($academic_year)</h2>";
        output_table($feedback_data, ['assessment_name', 'module_code', 'module_title'], 'html');
    }

    if (!empty($assessment_respondents)) {
        $respondents_data = [];
        foreach ($metrics as $m) {
            $respondents_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'respondent_count' => $m['respondent_count'],
            ];
        }
        echo "<h2>Number of Respondents per assessment ($academic_year)</h2>";
        output_table($respondents_data, ['assessment_name', 'module_code', 'module_title', 'respondent_count'], 'html');
    }

    if (!empty($assessment_modules)) {
        echo "<h2>Modules which have run an assessment ($academic_year)</h2>";
        output_table($modules_list, ['module_code', 'module_title'], 'html');
    }

    if (!empty($assessment_students_thisyear)) {
        echo "<h2>Number of students who have carried out an assessment ($academic_year)</h2>";
        if ($total_students) {
            if ($format === 'html') {
                echo '<table class="grid"><tr><td>' . htmlspecialchars((string) $total_students) . '</td></tr></table>';
            } else {
                output_table([$total_students], ['Total unique students assessed'], 'html');
            }
        } else {
            echo '<p>None</p>';
        }
    }

    echo '</div>';
    $UI->content_end();
}

//-------------------------------------------------------------------------------
// output csv
if ($format == 'csv') {
    header('Content-Disposition: attachment; filename="metrics.csv"');
    header('Content-Type: text/csv');

    echo '"WebPA - Metrics report"' . "\n";

    if (!empty($assessments_run)) {
        echo "\n\"Assessments run in WebPA ($academic_year)\"\n";
        output_table($metrics, ['assessment_name', 'module_code', 'module_title', 'open_date', 'close_date'], 'csv');
    }

    if (!empty($assessment_groups)) {
        $groups_data = [];
        foreach ($metrics as $m) {
            $groups_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'group_count' => $m['group_count'],
            ];
        }
        echo "\n\"Number of groups per assessment ($academic_year)\"\n";
        output_table($groups_data, ['assessment_name', 'module_code', 'module_title', 'group_count'], 'csv');
    }

    if (!empty($assessment_students)) {
        $students_data = [];
        foreach ($metrics as $m) {
            $students_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'student_count' => $m['student_count'],
            ];
        }
        echo "\n\"Number of students per assessment ($academic_year)\"\n";
        output_table($students_data, ['assessment_name', 'module_code', 'module_title', 'student_count'], 'csv');
    }

    if (!empty($assessment_feedback)) {
        $feedback_data = [];
        foreach ($metrics as $m) {
            if ($m['allow_feedback']) {
                $feedback_data[] = [
                    'assessment_name' => $m['assessment_name'],
                    'module_code' => $m['module_code'],
                    'module_title' => $m['module_title'],
                ];
            }
        }
        echo "\n\"Assessments where feedback has been used ($academic_year)\"\n";
        output_table($feedback_data, ['assessment_name', 'module_code', 'module_title'], 'csv');
    }

    if (!empty($assessment_respondents)) {
        $respondents_data = [];
        foreach ($metrics as $m) {
            $respondents_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'respondent_count' => $m['respondent_count'],
            ];
        }
        echo "\n\"Number of respondents per assessment ($academic_year)\"\n";
        output_table($respondents_data, ['assessment_name', 'module_code', 'module_title', 'respondent_count'], 'csv');
    }

    if (!empty($assessment_modules)) {
        echo "\n\"Modules which have run an assessment ($academic_year)\"\n";
        output_table($modules_list, ['module_code', 'module_title'], 'csv');
    }

    if (!empty($assessment_students_thisyear)) {
        echo "\n\"Number of students who have carried out an assessment ($academic_year)\"\n";
        if ($total_students) {
            echo '"' . $total_students . "\"\n";
        }
    }
}

//------------------------------------------------------------------------------------
// export as rtf
if ($format == 'rtf') {
    header('Content-Disposition: attachment;filename=student_grades.rtf');
    header("Content-Type: text/enriched\n");

    echo 'WebPA - Metrics report' . "\n\n";

    if (!empty($assessments_run)) {
        echo "\nAssessments run in WebPA ($academic_year)\n\n";
        output_table($metrics, ['assessment_name', 'module_code', 'module_title', 'open_date', 'close_date'], 'rtf');
    }

    if (!empty($assessment_groups)) {
        $groups_data = [];
        foreach ($metrics as $m) {
            $groups_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'group_count' => $m['group_count'],
            ];
        }
        echo "\nNumber of groups per assessment ($academic_year)\n\n";
        output_table($groups_data, ['assessment_name', 'module_code', 'module_title', 'group_count'], 'rtf');
    }

    if (!empty($assessment_students)) {
        $students_data = [];
        foreach ($metrics as $m) {
            $students_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'student_count' => $m['student_count'],
            ];
        }
        echo "\nNumber of students per assessment ($academic_year)\n\n";
        output_table($students_data, ['assessment_name', 'module_code', 'module_title', 'student_count'], 'rtf');
    }

    if (!empty($assessment_feedback)) {
        $feedback_data = [];
        foreach ($metrics as $m) {
            if ($m['allow_feedback']) {
                $feedback_data[] = [
                    'assessment_name' => $m['assessment_name'],
                    'module_code' => $m['module_code'],
                    'module_title' => $m['module_title'],
                ];
            }
        }
        echo "\nAssessments where feedback has been used ($academic_year)\n\n";
        output_table($feedback_data, ['assessment_name', 'module_code', 'module_title'], 'rtf');
    }

    if (!empty($assessment_respondents)) {
        $respondents_data = [];
        foreach ($metrics as $m) {
            $respondents_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'respondent_count' => $m['respondent_count'],
            ];
        }
        echo "\nNumber of Respondents per assessment ($academic_year)\n\n";
        output_table($respondents_data, ['assessment_name', 'module_code', 'module_title', 'respondent_count'], 'rtf');
    }

    if (!empty($assessment_modules)) {
        echo "\nModules which have run an assessment ($academic_year)\n\n";
        output_table($modules_list, ['module_code', 'module_title'], 'rtf');
    }

    if (!empty($assessment_students_thisyear)) {
        echo "\nNumber of students who have carried out an assessment ($academic_year)\n\n";
        if ($total_students) {
            echo $total_students . "\n";
        }
    }
}

//------------------------------------------------------------------------------------
// export as xml
if ($format == 'xml') {
    header('Content-Disposition: attachment; file="webpa_metrics.xml"');
    header('Content-Type: text/xml');

    echo '<?xml version="1.0" ?> ';
    echo '<metrics_report>';

    if (!empty($assessments_run)) {
        echo '<metrics>';
        echo "<description>Assessments run in WebPA ($academic_year)</description>";
        $columns = ['assessment_name', 'module_code', 'module_title', 'open_date', 'close_date'];
        foreach ($metrics as $m) {
            echo '<metric>';
            $i = 0;
            foreach ($columns as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$m[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }

    if (!empty($assessment_groups)) {
        $groups_data = [];
        foreach ($metrics as $m) {
            $groups_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'group_count' => $m['group_count'],
            ];
        }
        echo '<metrics>';
        echo "<description>Number of groups per assessment ($academic_year)</description>";
        $columns = ['assessment_name', 'module_code', 'module_title', 'group_count'];
        foreach ($groups_data as $m) {
            echo '<metric>';
            $i = 0;
            foreach ($columns as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$m[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }

    if (!empty($assessment_students)) {
        $students_data = [];
        foreach ($metrics as $m) {
            $students_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'student_count' => $m['student_count'],
            ];
        }
        echo '<metrics>';
        echo "<description>Number of students per assessment ($academic_year)</description>";
        $columns = ['assessment_name', 'module_code', 'module_title', 'student_count'];
        foreach ($students_data as $m) {
            echo '<metric>';
            $i = 0;
            foreach ($columns as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$m[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }

    if (!empty($assessment_feedback)) {
        $feedback_data = [];
        foreach ($metrics as $m) {
            if ($m['allow_feedback']) {
                $feedback_data[] = [
                    'assessment_name' => $m['assessment_name'],
                    'module_code' => $m['module_code'],
                    'module_title' => $m['module_title'],
                ];
            }
        }
        echo '<metrics>';
        echo "<description>Assessments where feedback has been used ($academic_year)</description>";
        $columns = ['assessment_name', 'module_code', 'module_title'];
        foreach ($feedback_data as $m) {
            echo '<metric>';
            $i = 0;
            foreach ($columns as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$m[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }

    if (!empty($assessment_respondents)) {
        $respondents_data = [];
        foreach ($metrics as $m) {
            $respondents_data[] = [
                'assessment_name' => $m['assessment_name'],
                'module_code' => $m['module_code'],
                'module_title' => $m['module_title'],
                'respondent_count' => $m['respondent_count'],
            ];
        }
        echo '<metrics>';
        echo "<description>Number of Respondents per assessment ($academic_year)</description>";
        $columns = ['assessment_name', 'module_code', 'module_title', 'respondent_count'];
        foreach ($respondents_data as $m) {
            echo '<metric>';
            $i = 0;
            foreach ($columns as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$m[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }

    if (!empty($assessment_modules)) {
        echo '<metrics>';
        echo "<description>Modules which have run an assessment ($academic_year)</description>";
        foreach ($modules_list as $m) {
            echo '<metric>';
            $i = 0;
            foreach (['module_code', 'module_title'] as $col) {
                echo "<field_$i>$col</field_$i>";
                echo "<value_$i>{$m[$col]}</value_$i>";
                $i++;
            }
            echo '</metric>';
        }
        echo '</metrics>';
    }

    if (!empty($assessment_students_thisyear)) {
        echo '<metrics>';
        echo "<description>Number of students who have carried out an assessment ($academic_year)</description>";
        if ($total_students) {
            echo '<metric>';
            echo '<field_0>Total unique students assessed</field_0>';
            echo "<value_0>$total_students</value_0>";
            echo '</metric>';
        }
        echo '</metrics>';
    }

    echo '</metrics_report>';
}
