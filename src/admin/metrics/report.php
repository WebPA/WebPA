<?php
/**
 * General usage report
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
//assessments which have been run
$dbConn = $DB->getConnection();

$runAssessmentsStmt = $dbConn->prepare(
    'SELECT a.assessment_name, m.module_code, m.module_title, a.open_date, a.close_date ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
   'ORDER BY a.close_date, a.open_date, a.assessment_name, a.assessment_id'
);

$runAssessmentsStmt->bindValue(1, $_source_id);
$runAssessmentsStmt->bindValue(2, $this_year);
$runAssessmentsStmt->bindValue(3, $next_year);

//number of groups per assessment
$runGroupsPerAssessmentStmt = $dbConn->prepare('SELECT a.assessment_name, m.module_code, m.module_title, COUNT(g.group_id) as group_count ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group g ON a.collection_id = g.collection_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
   'GROUP BY a.assessment_name, m.module_code, m.module_title ' .
   'ORDER BY a.assessment_name');

$runGroupsPerAssessmentStmt->bindValue(1, $_source_id);
$runGroupsPerAssessmentStmt->bindValue(2, $this_year);
$runGroupsPerAssessmentStmt->bindValue(3, $next_year);

//number of students per assessment
$runStudentsPerAssessmentStmt = $dbConn->prepare('SELECT a.assessment_name, m.module_code, m.module_title, COUNT(ugm.user_id) as student_count ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group g ON a.collection_id = g.collection_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ON g.group_id = ugm.group_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
   'GROUP BY a.assessment_name, m.module_code, m.module_title ' .
   'ORDER BY a.assessment_name, m.module_code');

$runStudentsPerAssessmentStmt->bindValue(1, $_source_id);
$runStudentsPerAssessmentStmt->bindValue(2, $this_year);
$runStudentsPerAssessmentStmt->bindValue(3, $next_year);

//assessments where feedback has been used
$runFeedbackStmt = $dbConn->prepare('SELECT a.assessment_name, m.module_code, m.module_title ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
   'AND a.allow_feedback = 1 ' .
   'ORDER BY a.assessment_name, a.assessment_id');

$runFeedbackStmt->bindValue(1, $_source_id);
$runFeedbackStmt->bindValue(2, $this_year);
$runFeedbackStmt->bindValue(3, $next_year);


//number of respondents per assessment
$runRespondentsStmt = $dbConn->prepare('SELECT a.assessment_name, m.module_code, m.module_title, COUNT(DISTINCT um.user_id) as response_count ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_mark um ON a.assessment_id=um.assessment_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
   'GROUP BY a.assessment_name, m.module_code, m.module_title ' .
   'ORDER BY a.assessment_name, m.module_code');

$runRespondentsStmt->bindValue(1, $_source_id);
$runRespondentsStmt->bindValue(2, $this_year);
$runRespondentsStmt->bindValue(3, $next_year);

//who has run an assessment in the current academic year
$runModulesPerAssessmentsStmt = $dbConn->prepare('SELECT DISTINCT m.module_code, m.module_title ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ? ' .
   'ORDER BY m.module_code');

$runModulesPerAssessmentsStmt->bindValue(1, $_source_id);
$runModulesPerAssessmentsStmt->bindValue(2, $this_year);
$runModulesPerAssessmentsStmt->bindValue(3, $next_year);

//number of students who has carried out an assessment this year
$runStudentsAssessedStmt = $dbConn->prepare('SELECT COUNT(DISTINCT ugm.user_id) as \'Total unique students assessed\' ' .
   'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group ug ON a.collection_id = ug.collection_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_group_member ugm ON ug.group_id = ugm.group_id ' .
   'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
   'WHERE m.source_id = ? AND a.open_date >= ? AND a.open_date < ?');

$runStudentsAssessedStmt->bindValue(1, $_source_id);
$runStudentsAssessedStmt->bindValue(2, $this_year);
$runStudentsAssessedStmt->bindValue(3, $next_year);

//-------------------------------------------------------
//view on screen
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
        // This returns a Result object... what can we do with it?
        $runAssessmentsResult = $runAssessmentsStmt->execute();

        $rs_assessments = $runAssessmentsResult->fetchAllAssociative();

        echo "<h2>Assessments run in WebPA ({$academic_year})</h2>";

        if ($rs_assessments) {
            echo '<table class="grid">';
            $icounter = 0;

            //loop round the initial array
            foreach ($rs_assessments as $assessment) {
                if ($icounter==0) {
                    //get an array of the key to the $assessment array
                    $field_names = array_keys($assessment);

                    foreach ($field_names as $row) {
                        echo "<th>{$row}</th>";
                    }
                }

                echo '<tr>';
                foreach ($assessment as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
                $icounter ++;
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }

    if (!empty($assessment_groups)) {
        $runGroupsPerAssessmentResult = $runGroupsPerAssessmentStmt->execute();

        $rs_groups = $runGroupsPerAssessmentResult->fetchAllAssociative();

        echo "<h2>Number of groups per assessment ({$academic_year})</h2>";

        if ($rs_groups) {
            echo '<table class="grid">';

            $icounter = 0;
            //loop round the initial array
            foreach ($rs_groups as $groups) {
                if ($icounter==0) {
                    $field_names = array_keys($groups);

                    foreach ($field_names as $row) {
                        echo "<th>{$row}</th>";
                    }
                }

                echo '<tr>';
                foreach ($groups as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
                $icounter ++;
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }

    if (!empty($assessment_students)) {
        $runStudentsPerAssessmentResult = $runStudentsPerAssessmentStmt->execute();

        $rs_students = $runStudentsPerAssessmentResult->fetchAllAssociative();

        echo "<h2>Number of students per assessment ({$academic_year})</h2>";

        if ($rs_students) {
            echo '<table class="grid">';

            $icounter = 0;
            //loop round the initial array
            foreach ($rs_students as $students) {
                if ($icounter==0) {
                    $field_names = array_keys($students);

                    foreach ($field_names as $row) {
                        echo "<th>{$row}</th>";
                    }
                }
                echo '<tr>';
                foreach ($students as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
                $icounter++;
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }
    if (!empty($assessment_feedback)) {
        $runFeedbackResult = $runFeedbackStmt->execute();

        $rs_feedback = $runFeedbackResult->fetchAllAssociative();

        echo "<h2>Assessments where feedback has been used ({$academic_year})</h2>";

        if ($rs_feedback) {
            echo '<table class="grid">';

            $icounter = 0;

            //loop round the initial array
            foreach ($rs_feedback as $feedback) {
                if ($icounter==0) {
                    $field_names = array_keys($feedback);
                    foreach ($field_names as $row) {
                        echo"<th>{$row}</th>";
                    }
                }

                echo '<tr>';
                foreach ($feedback as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
                $icounter++;
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }

    if (!empty($assessment_respondents)) {
        $runRespondentsResult = $runRespondentsStmt->execute();

        $rs_respondents = $runRespondentsResult->fetchAllAssociative();

        echo "<h2>Number of Respondents per assessment ({$academic_year})</h2>";

        if ($rs_respondents) {
            echo '<table class="grid">';

            $icounter=0;
            //loop round the initial array
            foreach ($rs_respondents as $responses) {
                if ($icounter == 0) {
                    $field_names = array_keys($responses);
                    foreach ($field_names as $row) {
                        echo"<th>{$row}</th>";
                    }
                }

                echo '<tr>';
                foreach ($responses as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
                $icounter++;
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }

    if (!empty($assessment_modules)) {
        $runModulesPerAssessmentsResult = $runModulesPerAssessmentsStmt->execute();

        $rs_runners = $runModulesPerAssessmentsResult->fetchAllAssociative();

        echo "<h2>Modules which have run an assessment ({$academic_year})</h2>";

        if ($rs_runners) {
            echo '<table class="grid">';

            $icounter = 0;
            //loop round the initial array
            foreach ($rs_runners as $runner) {
                if ($icounter==0) {
                    $field_names = array_keys($runner);
                    foreach ($field_names as $row) {
                        echo "<th>{$row}</th>";
                    }
                }

                echo '<tr>';
                foreach ($runner as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
                $icounter++;
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }

    if (!empty($assessment_students_thisyear)) {
        $runStudentsAssessedResult = $runStudentsAssessedStmt->execute();

        $rs_students = $runStudentsAssessedResult->fetchAllAssociative();

        echo "<h2>Number of students who have carried out an assessment ({$academic_year})</h2>";

        if ($rs_students) {
            echo '<table class="grid">';
            //loop round the initial array
            foreach ($rs_students as $student) {
                echo '<tr>';
                foreach ($student as $row) {
                    echo "<td>{$row}</td>";
                }
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>None</p>';
        }
    }

    echo '</div>';
    $UI->content_end();
}

//-------------------------------------------------------------------------------
//output csv
if ($format == 'csv') {
    header('Content-Disposition: attachment; filename="metrics.csv"');
    header('Content-Type: text/csv');

    echo '"WebPA - Metrics report"'."\n";


    if (!empty($assessments_run)) {
        $runAssessmentsResult = $runAssessmentsStmt->execute();

        $rs_assessments = $runAssessmentsResult->fetchAllAssociative();

        echo "\n\"Assessments run in WebPA ({$academic_year})\"\n";
        if ($rs_assessments) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_assessments as $assessment) {
                if ($icounter==0) {
                    //get an array of the key to the $assessment array
                    echo "\n";
                    $field_names = array_keys($assessment);
                    foreach ($field_names as $row) {
                        echo "\"{$row}\",";
                    }
                    echo "\n";
                }
                foreach ($assessment as $row) {
                    echo "\"{$row}\",";
                }
                echo "\n";
                $icounter ++;
            }
        }
    }

    if (!empty($assessment_groups)) {
        $runGroupsPerAssessmentResult = $runGroupsPerAssessmentStmt->execute();

        $rs_groups = $runGroupsPerAssessmentResult->fetchAllAssociative();

        echo "\n\"Number of groups per assessment ({$academic_year})\"\n";
        if ($rs_groups) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_groups as $groups) {
                if ($icounter==0) {
                    echo "\n";
                    $field_names = array_keys($groups);
                    foreach ($field_names as $row) {
                        echo "\"{$row}\",";
                    }
                    echo "\n";
                }
                foreach ($groups as $row) {
                    echo "\"{$row}\",";
                }
                echo "\n";
                $icounter ++;
            }
        }
    }

    if (!empty($assessment_students)) {
        $runStudentsPerAssessmentResult = $runStudentsPerAssessmentStmt->execute();

        $rs_students = $runStudentsPerAssessmentResult->fetchAllAssociative();

        echo "\n\"Number of students per assessment ({$academic_year})\"\n";
        if ($rs_students) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_students as $students) {
                if ($icounter==0) {
                    echo "\n";
                    $field_names = array_keys($students);
                    foreach ($field_names as $row) {
                        echo "\"{$row}\",";
                    }
                    echo "\n";
                }
                foreach ($students as $row) {
                    echo "\"{$row}\",";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_feedback)) {
        $runFeedbackResult = $runFeedbackStmt->execute;

        $rs_feedback = $runFeedbackResult->fetchAllAssociative();

        echo "\n\"Assessments where feedback has been used ({$academic_year})\"\n";
        if ($rs_feedback) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_feedback as $feedback) {
                if ($icounter==0) {
                    echo "\n";
                    $field_names = array_keys($feedback);
                    foreach ($field_names as $row) {
                        echo"\"{$row}\",";
                    }
                    echo "\n";
                }
                foreach ($feedback as $row) {
                    echo "\"{$row}\",";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_respondents)) {
        $runRespondentsResult = $runRespondentsStmt->execute();

        $rs_respondents = $runRespondentsResult->fetchAllAssociative();

        echo "\n\"Number of respondents per assessment ({$academic_year})\"\n";

        if ($rs_respondents) {
            $icounter=0;
            //loop round the initial array
            foreach ($rs_respondents as $responses) {
                if ($icounter == 0) {
                    echo "\n";
                    $field_names = array_keys($responses);
                    foreach ($field_names as $row) {
                        echo"\"{$row}\",";
                    }
                    echo "\n";
                }
                foreach ($responses as $row) {
                    echo "\"{$row}\",";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_modules)) {
        $runModulesPerAssessmentsResult = $runModulesPerAssessmentsStmt->execute();

        $rs_runners = $runModulesPerAssessmentsResult->fetchAllAssociative();

        echo "\n\"Modules which have run an assessment ({$academic_year})\"\n";

        if ($rs_runners) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_runners as $runner) {
                if ($icounter==0) {
                    echo "\n";
                    $field_names = array_keys($runner);
                    foreach ($field_names as $row) {
                        echo "\"{$row}\",";
                    }
                    echo "\n";
                }
                foreach ($runner as $row) {
                    echo "\"{$row}\",";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_students_thisyear)) {
        $runStudentsAssessedResult = $runStudentsAssessedStmt->execute();

        $rs_students = $runStudentsAssessedResult->fetchAllAssociative();
        echo "\n\"Number of students who have carried out an assessment ({$academic_year})\"\n";
        if ($rs_students) {
            //loop round the initial array
            echo "\n";
            foreach ($rs_students as $student) {
                foreach ($student as $row) {
                    echo"\"{$row}\",";
                }
            }
            echo"\n";
        }
    }
}

//------------------------------------------------------------------------------------
//export as rtf
if ($format == 'rtf') {
    header('Content-Disposition: attachment;filename=student_grades.rtf');
    header("Content-Type: text/enriched\n");

    echo 'WebPA - Metrics report'."\n\n";

    if (!empty($assessments_run)) {
        $runAssessmentsResult = $runAssessmentsStmt->execute();

        $rs_assessments = $runAssessmentsResult->fetchAllAssociative();

        echo "\nAssessments run in WebPA ({$academic_year})\n\n";
        $icounter = 0;
        //loop round the initial array
        foreach ($rs_assessments as $assessment) {
            if ($icounter==0) {
                //get an array of the key to the $assessment array
                $field_names = array_keys($assessment);
                foreach ($field_names as $row) {
                    echo " {$row}  ";
                }
            }
            echo "\n";
            foreach ($assessment as $row) {
                echo " {$row}  ";
            }
            $icounter ++;
        }
    }

    if (!empty($assessment_groups)) {
        $runGroupsPerAssessmentResult = $runGroupsPerAssessmentStmt->execute();

        $rs_groups = $runGroupsPerAssessmentResult->fetchAllAssociative();

        echo "\nNumber of groups per assessment ({$academic_year})\n\n";

        if ($rs_groups) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_groups as $groups) {
                if ($icounter==0) {
                    $field_names = array_keys($groups);
                    foreach ($field_names as $row) {
                        echo " {$row}  ";
                    }
                }
                echo "\n";
                foreach ($groups as $row) {
                    echo " {$row}  ";
                }
                $icounter ++;
            }
        }
    }

    if (!empty($assessment_students)) {
        $runStudentsPerAssessmentResult = $runStudentsPerAssessmentStmt->execute();

        $rs_students = $runStudentsPerAssessmentResult->fetchAllAssociative();

        echo "\nNumber of students per assessment ({$academic_year})\n\n";
        if ($rs_students) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_students as $students) {
                if ($icounter==0) {
                    $field_names = array_keys($students);
                    foreach ($field_names as $row) {
                        echo " {$row}  ";
                    }
                }
                echo "\n";
                foreach ($students as $row) {
                    echo " {$row}  ";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_feedback)) {
        $runFeedbackResult = $runFeedbackStmt->execute;

        $rs_feedback = $runFeedbackResult->fetchAllAssociative();

        echo "\nAssessments where feedback has been used ({$academic_year})\n\n";
        if ($rs_feedback) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_feedback as $feedback) {
                if ($icounter==0) {
                    $field_names = array_keys($feedback);
                    foreach ($field_names as $row) {
                        echo"{$row}  ";
                    }
                }
                echo "\n";
                foreach ($feedback as $row) {
                    echo "{$row}  ";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_respondents)) {
        $runRespondentsResult = $runRespondentsStmt->execute();

        $rs_respondents = $runRespondentsResult->fetchAllAssociative();

        echo "\nNumber of Respondents per assessment ({$academic_year})\n\n";

        if ($rs_respondents) {
            $icounter=0;
            //loop round the initial array
            foreach ($rs_respondents as $responses) {
                if ($icounter == 0) {
                    $field_names = array_keys($responses);
                    foreach ($field_names as $row) {
                        echo"{$row}  ";
                    }
                }
                echo "\n";
                foreach ($responses as $row) {
                    echo "{$row}  ";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_modules)) {
        $runModulesPerAssessmentsResult = $runModulesPerAssessmentsStmt->execute();

        $rs_runners = $runModulesPerAssessmentsResult->fetchAllAssociative();

        echo "\nModules which have run an assessment ({$academic_year})\n\n";

        if ($rs_runners) {
            $icounter = 0;
            //loop round the initial array
            foreach ($rs_runners as $runner) {
                if ($icounter==0) {
                    $field_names = array_keys($runner);
                    foreach ($field_names as $row) {
                        echo "{$row}  ";
                    }
                }
                echo "\n";
                foreach ($runner as $row) {
                    echo "{$row}  ";
                }
                echo "\n";
                $icounter++;
            }
        }
    }

    if (!empty($assessment_students_thisyear)) {
        $runStudentsAssessedResult = $runStudentsAssessedStmt->execute();

        $rs_students = $runStudentsAssessedResult->fetchAllAssociative();

        echo "\nNumber of students who have carried out an assessment ({$academic_year})\n\n";
        if ($rs_students) {
            //loop round the initial array
            foreach ($rs_students as $student) {
                foreach ($student as $row) {
                    echo"{$row}  ";
                }
            }
            echo"\n";
        }
    }
}

//------------------------------------------------------------------------------------
//export as xml
if ($format == 'xml') {
    header('Content-Disposition: attachment; file="webpa_metrics.xml"');
    header('Content-Type: text/xml');

    echo '<?xml version="1.0" ?> ';
    echo'<metrics_report>';

    if (!empty($assessments_run)) {
        $runAssessmentsResult = $runAssessmentsStmt->execute();

        $rs_assessments = $runAssessmentsResult->fetchAllAssociative();

        echo '<metrics>';
        echo "<description>Assessments run in WebPA ({$academic_year})</description>";
        if ($rs_assessments) {
            //loop round the initial array
            foreach ($rs_assessments as $assessment) {
                //get an array of the key to the $assessment array
                $field_names = array_keys($assessment);
                $field_content = array_values($assessment);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    if (!empty($assessment_groups)) {
        $runGroupsPerAssessmentResult = $runGroupsPerAssessmentStmt->execute();

        $rs_groups = $runGroupsPerAssessmentResult->fetchAllAssociative();

        echo '<metrics>';
        echo "<description>Number of groups per assessment ({$academic_year})</description>";
        if ($rs_groups) {
            foreach ($rs_groups as $groups) {
                //get an array of the key to the $groups array
                $field_names = array_keys($groups);
                $field_content = array_values($groups);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    if (!empty($assessment_students)) {
        $runStudentsPerAssessmentResult = $runStudentsPerAssessmentStmt->execute();

        $rs_students = $runStudentsPerAssessmentResult->fetchAllAssociative();

        echo '<metrics>';
        echo "<description>Number of students per assessment ({$academic_year})</description>";

        if ($rs_students) {
            //loop round the initial array
            foreach ($rs_students as $students) {
                //get an array of the key to the $students array
                $field_names = array_keys($students);
                $field_content = array_values($students);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    if (!empty($assessment_feedback)) {
        $runFeedbackResult = $runFeedbackStmt->execute;

        $rs_feedback = $$runFeedbackResult->fetchAllAssociative();

        echo '<metrics>';
        echo "<description>Assessments where feedback has been used ({$academic_year})</description>";
        if ($rs_feedback) {
            //loop round the initial array
            foreach ($rs_feedback as $feedback) {
                //get an array of the key to the $feedback array
                $field_names = array_keys($feedback);
                $field_content = array_values($feedback);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    if (!empty($assessment_respondents)) {
        $runRespondentsResult = $runRespondentsStmt->execute();

        $rs_respondents = $runRespondentsResult->fetchAllAssociative();

        echo '<metrics>';

        echo "<description>Number of Respondents per assessment ({$academic_year})</description>";

        if ($rs_respondents) {
            //loop round the initial array
            foreach ($rs_respondents as $responses) {
                //get an array of the key to the $responses array
                $field_names = array_keys($responses);
                $field_content = array_values($responses);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    if (!empty($assessment_modules)) {
        $runModulesPerAssessmentsResult = $runModulesPerAssessmentsStmt->execute();

        $rs_runners = $runModulesPerAssessmentsResult->fetchAllAssociative();

        echo '<metrics>';
        echo "<description>Modules which have run an assessment ({$academic_year})</description>";

        if ($rs_runners) {
            //loop round the initial array
            foreach ($rs_runners as $runner) {
                //get an array of the key to the $runner array
                $field_names = array_keys($runner);
                $field_content = array_values($runner);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    if (!empty($assessment_students_thisyear)) {
        $runStudentsAssessedResult = $runStudentsAssessedStmt->execute();

        $rs_students = $runStudentsAssessedResult->fetchAllAssociative();

        echo '<metrics>';
        echo "<description>Number of students who have carried out an assessment ({$academic_year})</description>";
        if ($rs_students) {
            //loop round the initial array
            foreach ($rs_students as $student) {
                //get an array of the key to the $student array
                $field_names = array_keys($student);
                $field_content = array_values($student);

                //get the number of elements in the arrays
                $array_count = count($field_names);
                echo '<metric>';
                for ($count=0; $count<$array_count; $count++) {
                    echo "<field_{$count}>{$field_names[$count]}</field_{$count}>";
                    echo "<value_{$count}>{$field_content[$count]}</value_{$count}>";
                }
                echo '</metric>';
            }
        }
        echo '</metrics>';
    }

    echo'</metrics_report>';
}
