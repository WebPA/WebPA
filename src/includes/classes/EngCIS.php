<?php
/**
 * engCIS local version
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\ParameterType;
use WebPA\includes\functions\AcademicYear;
use WebPA\includes\functions\ArrayFunctions;
use WebPA\includes\functions\Common;

class EngCIS
{
    private $_DAO;

    private $_ordering_types;

    private $user;

    private $sourceId;

    private $moduleId;

    private $dbConn;

    /**
     * CONSTRUCTOR
     */
    public function __construct($sourceId, $moduleId)
    {
        $this->sourceId = $sourceId;
        $this->moduleId = $moduleId;

        $this->_DAO = new DAO(APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE, APP__DB_PORT);

        $this->dbConn = $this->_DAO->getConnection();
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /*
    * --------------------------------------------------------------------------------
    * Module Methods
    * --------------------------------------------------------------------------------
    */

    /**
     * Get module info as an array
     *
     * @param string/array $module_id module ID(s) to search for
     * @param string $ordering ordering mode
     *
     * @return array  either an assoc-array of module info or an array of assoc-arrays, containing many modules' info
     */
    public function get_module($modules = null, $ordering = 'id')
    {
        $queryBuilder = $this->dbConn->createQueryBuilder();

        if ($ordering === 'name') {
            $queryBuilder->orderBy('lcm.module_id');
        } else {
            $queryBuilder->orderBy('lcm.module_title');
        }

        // If there's more than one module to search for, get all the rows
        if (is_array($modules)) {
            // get all modules
            $queryBuilder
                ->select('lcm.module_id', 'lcm.module_title', 'lcm.module_code')
                ->from(APP__DB_TABLE_PREFIX . 'module', 'lcm')
                ->where('lcm.source_id = :source_id')
                ->andWhere('module_id IN (:modules)')
                ->setParameter(':source_id', $this->sourceId)
                ->setParameter(':modules', $modules, $this->dbConn::PARAM_INT_ARRAY);

            return $queryBuilder->execute()->fetchAllAssociative();
        }
        if (!empty($modules)) {  // else, just return one row
            $moduleQuery = 'SELECT module_id, module_title, module_code FROM ' . APP__DB_TABLE_PREFIX . 'module WHERE source_id = ? AND module_id = ? LIMIT 1';

            return $this->dbConn->fetchAssociative(
                $moduleQuery,
                [$this->sourceId, $modules],
                [ParameterType::STRING, ParameterType::INTEGER]
            );
        }
        if ($this->user->is_admin()) {
            $queryBuilder
                ->select('lcm.module_id', 'lcm.module_title', 'lcm.module_code')
                ->from(APP__DB_TABLE_PREFIX . 'module', 'lcm')
                ->where('lcm.source_id = ?')
                ->setParameter(0, $this->sourceId, ParameterType::STRING);

            return $queryBuilder->execute()->fetchAllAssociative();
        }
        $queryBuilder
                ->select('lcm.module_id', 'lcm.module_title', 'lcm.module_code')
                ->from(APP__DB_TABLE_PREFIX . 'module', 'lcm')
                ->innerJoin('lcm', APP__DB_TABLE_PREFIX . 'user_module', 'lcsm', 'lcm.module_id = lcsm.module_id')
                ->where('lcsm.user_type = ?')
                ->andWhere('user_id = ?')
                ->andWhere('lcm.source_id = ?')
                ->setParameter(0, APP__USER_TYPE_TUTOR, ParameterType::STRING)
                ->setParameter(1, $this->user->id, ParameterType::INTEGER)
                ->setParameter(2, $this->sourceId, ParameterType::STRING);

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Get all the module info as an array
     *
     * @return array
     */
    public function get_all_modules()
    {
        return $this->dbConn->fetchAllAssociative('SELECT lcm.module_id, lcm.module_title FROM ' . APP__DB_TABLE_PREFIX . 'module lcm');
    }

    /**
     * Get array of staff for module
     * @param integer $module_id
     * @param string $ordering
     * @return array
     */
    public function get_module_staff($module_id, $ordering)
    {
        $queryBuilder = $this->dbConn->createQueryBuilder();

        if ($ordering === 'id') {
            $queryBuilder->orderBy('lcs.user_id');
        } else {
            $queryBuilder->orderBy('lcs.lastname');
            $queryBuilder->addOrderBy('lcs.forename');
        }

        $queryBuilder
            ->select('lcs.*')
            ->from(APP__DB_TABLE_PREFIX . 'user lcs')
            ->innerJoin('lcs', APP__DB_TABLE_PREFIX . 'user_module', 'lcsm', 'lcs.user_id = lcsm.user_id')
            ->where('lcsm.user_type = ?')
            ->andWhere('module_id = ?')
            ->setParameter(0, APP__USER_TYPE_TUTOR, ParameterType::STRING)
            ->setParameter(1, $module_id, ParameterType::INTEGER);

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Get array of students for one or more modules
     * @param integer $modules
     * @param string $ordering
     * @return array
     */
    public function get_module_students($module, $ordering = 'name')
    {
        $queryBuilder = $this->dbConn->createQueryBuilder();

        if ($ordering === 'id') {
            $queryBuilder->orderBy('lcs.user_id');
        } else {
            $queryBuilder->orderBy('lcs.lastname');
            $queryBuilder->addOrderBy('lcs.forename');
        }

        $queryBuilder
            ->select('lcs.*', 'lcs.id_number as student_id')
            ->distinct()
            ->from(APP__DB_TABLE_PREFIX . 'user', 'lcs')
            ->innerJoin('lcs', APP__DB_TABLE_PREFIX . 'user_module', 'lcsm', 'lcs.user_id = lcsm.user_id AND lcsm.module_id = ?')
            ->where('lcsm.user_type = ?')
            ->setParameter(0, $module, ParameterType::INTEGER)
            ->setParameter(1, APP__USER_TYPE_STUDENT, ParameterType::STRING);

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Get total number of students on one or more modules
     *
     * @param int $moduleId module to count students for
     *
     * @return int
     */
    public function get_module_students_count($moduleId)
    {
        $studentsCountQuery =
            'SELECT COUNT(DISTINCT u.user_id) AS user_count ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'user u ' .
            'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
            'ON u.user_id = um.user_id ' .
            'WHERE um.module_id = ? ' .
            'AND um.user_type = ?';

        return $this->dbConn->fetchOne($studentsCountQuery, [$moduleId, APP__USER_TYPE_STUDENT], [ParameterType::INTEGER, ParameterType::STRING]);
    }

    /**
     * Get an array of user IDs for students on the given modules (user_id = 'student_{studentID}'
     *
     * @param array $modules modules to count students for
     *
     * @return array|void
     */
    public function get_module_students_user_id($modules)
    {
        if (empty($modules)) {
            return;
        }

        $sql =
            'SELECT DISTINCT u.user_id ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'user u ' .
            'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
            'ON u.user_id = um.user_id ' .
            'WHERE um.module_id IN (?) ' .
            'AND um.user_type = ? ' .
            'ORDER BY u.user_id ASC';

        return $this->dbConn->fetchFirstColumn($sql, [$modules, APP__USER_TYPE_STUDENT], [$this->dbConn::PARAM_STR_ARRAY, ParameterType::STRING]);
    }

    /**
     * Get number of students on individual multiple modules, grouped by module
     *
     * @param array $modules modules to count students for
     * @return array
     */
    public function get_module_grouped_students_count($modules)
    {
        $queryBuilder = $this->_DAO->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('module_id', 'COUNT(user_id')
            ->from(APP__DB_TABLE_PREFIX . 'user_module', 'lcsm')
            ->where(
                $queryBuilder->expr()->in('module_id', '?')
            )
            ->groupBy('module_id')
            ->orderBy('module_id');

        $queryBuilder->setParameter(0, $modules, $this->_DAO->getConnection()::PARAM_INT_ARRAY);

        return $queryBuilder->execute()->fetchAllKeyValue();
    }

    /*
    * --------------------------------------------------------------------------------
    * Staff Methods
    * --------------------------------------------------------------------------------
    */

    /**
     * Get array of modules for the given staff member(s)
     * Can work with either staff_id or staff_username alone (staff_id takes precedent)
     *
     * @param string/array $staff_id staff ID(s) to search for (use NULL if searching on username)
     * @param string/array $staff_username staff username(s) to search for
     * @param string $ordering ordering mode
     *
     * @return array  an array of assoc-arrays, containting many module info
     */
    public function get_staff_modules($staff_id, $staff_username = null, $ordering = 'id')
    {
        return $this->get_user_modules($staff_id, $staff_username, $ordering);
    }

    // /->get_staff_modules

    /**
     * Is the given staff member associated with the given modules?
     *
     * @param string $staff_id staff id of member being checked
     * @param string/array $module_id  either a single module_id, or an array of module_ids
     * @return integer
     */
    public function staff_has_module($staff_id, $module_id)
    {
        $module_id = (array) $module_id;
        $staff_modules = $this->get_staff_modules($staff_id);
        if (!$staff_modules) {
            return false;
        }
        $arr_module_id = ArrayFunctions::array_extract_column($staff_modules, 'module_id');
        $diff = array_diff($module_id, $arr_module_id);

        // If the array is empty, then the staff member has those modules
        return count(array_diff($module_id, $arr_module_id)) === 0;
    }

    // /->staff_has_module()

    /*
    * --------------------------------------------------------------------------------
    * Student Methods
    * --------------------------------------------------------------------------------
    */

    /**
     * Get array of modules for the given student(s)
     * Can work with either student_id or student_username alone (student_id takes precedent)
     *
     * @param string/array $student_id student ID(s) to search for (use NULL if searching on username)
     * @param string/array $student_username student username(s) to search for
     * @param string $ordering ordering mode
     *
     * @return array an array of module info arrays
     */
    public function get_student_modules($student_id, $student_username = null, $ordering = 'id')
    {
        return $this->get_user_modules($student_id, $student_username, $ordering);
    }

    // /->get_student_modules()

    /*
    * --------------------------------------------------------------------------------
    * User Methods
    * --------------------------------------------------------------------------------
    */

    /**
     * Get a user's info.
     *
     * @param string|array $user_id
     * @param string $ordering
     *
     * @return object
     */
    public function get_user($user_id, $ordering = 'name')
    {
        $queryBuilder = $this->dbConn->createQueryBuilder();

        if (is_array($user_id)) {
            if ($ordering === 'id') {
                $queryBuilder->orderBy('u.user_id');
            } else {
                $queryBuilder->orderBy('u.lastname');
                $queryBuilder->addOrderBy('u.forename');
            }

            $queryBuilder
                ->select('u.*', 'um.user_type')
                ->from(APP__DB_TABLE_PREFIX . 'user', 'u')
                ->leftJoin('u', APP__DB_TABLE_PREFIX . 'user_module', 'um', 'u.user_id = um.user_id')
                ->where('u.user_id IN (?)')
                ->andWhere('um.module_id = ?')
                ->setParameter(0, $user_id, $this->dbConn::PARAM_INT_ARRAY)
                ->setParameter(1, $this->moduleId, ParameterType::INTEGER);

            return $queryBuilder->execute()->fetchAllAssociative();
        }
        $query = 'SELECT u.*, um.user_type FROM ' . APP__DB_TABLE_PREFIX . 'user u '
                   . 'LEFT OUTER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um '
                   . 'ON u.user_id = um.user_id '
                   . 'WHERE u.user_id = ? '
                   . 'AND (um.module_id = ? OR u.admin = 1) '
                   . 'LIMIT 1';

        return $this->dbConn->fetchAssociative($query, [$user_id, $this->moduleId], [ParameterType::INTEGER, ParameterType::INTEGER]);
    }

    /**
     * Get a user's info by searching on email address
     *
     * @param string $email email address to search for
     *
     * Returns : an assoc-array of user info
     */
    public function get_user_for_email($email)
    {
        $query = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . 'user WHERE email = ? LIMIT 1';

        return $this->dbConn->fetchAssociative($query, [$email], [ParameterType::STRING]);
    }

    /**
     * Get a user's info by searching on username
     *
     * @param string $username username to search for
     *
     * Returns : an assoc-array of user info
     */
    public function get_user_for_username($username, $source_id = null)
    {
        if (is_null($source_id) && isset($_SESSION['_source_id'])) {
            $source_id = $_SESSION['_source_id'];
        } elseif (is_null($source_id)) {
            $source_id = '';
        }
        $this->moduleId = Common::fetch_SESSION('_module_id', null);

        $query = 'SELECT u.*, um.user_type FROM ' . APP__DB_TABLE_PREFIX . 'user u '
               . 'LEFT OUTER JOIN ( '
               . 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . 'user_module WHERE module_id = ? '
               . ') um '
               . 'ON u.user_id = um.user_id '
               . 'WHERE username = ? '
               . 'AND source_id = ?';

        return $this->dbConn->fetchAssociative($query, [$this->moduleId, $username, $source_id], [ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]);
    }

    /**
     * Get array of modules for the given user(s)
     * Can work with either user_id or username alone (user_id takes precedent)
     *
     * @param string/array $user_id user ID(s) to search for (use NULL if searching on username)
     * @param string/array $username username(s) to search for (use NULL for admin)
     * @param string $ordering ordering mode
     *
     * @return array an array of module info arrays
     */
    public function get_user_modules($user_id, $username = null, $ordering = 'id', $source_id = null)
    {
        $dbConn = $this->_DAO->getConnection();
        $queryBuilder = $dbConn->createQueryBuilder();

        if (is_null($source_id) && isset($_SESSION['_source_id'])) {
            $source_id = $_SESSION['_source_id'];
        } elseif (is_null($source_id)) {
            $source_id = '';
        }

        $queryBuilder->orderBy($ordering === 'name' ? 'lcm.module_title' : 'lcm.module_id');

        if ($user_id) {
            if (!is_array($user_id)) {
                $user_id = [$user_id];
            }

            $queryBuilder
                ->select('lcm.module_id', 'lcm.module_title', 'lcm.module_code', 'lcsm.user_type')
                ->from(APP__DB_TABLE_PREFIX . 'module', 'lcm')
                ->innerJoin('lcm', APP__DB_TABLE_PREFIX . 'user_module', 'lcsm', 'lcm.module_id = lcsm.module_id')
                ->innerJoin('lcsm', APP__DB_TABLE_PREFIX . 'user', 'u', 'lcsm.user_id = u.user_id')
                ->where(
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->or(
                            $queryBuilder->expr()->eq('lcm.source_id', '?'),
                            $queryBuilder->expr()->neq('u.source_id', '""')
                        ),
                        $queryBuilder->expr()->in('lcsm.user_id', '?')
                    )
                );

            $queryBuilder->setParameter(0, $source_id, ParameterType::STRING);
            $queryBuilder->setParameter(1, $user_id, $dbConn::PARAM_INT_ARRAY);
        } elseif ($username) {
            if (!is_array($username)) {
                $username = [$username];
            }

            $queryBuilder
                ->select('lcm.module_id', 'lcm.module_title', 'lcm.module_code', 'lcsm.user_type')
                ->from(APP__DB_TABLE_PREFIX . 'module lcm')
                ->innerJoin('lcm', APP__DB_TABLE_PREFIX . 'user_module', 'lcsm', 'lcm.module_id = lcsm.module_id')
                ->innerJoin('lcsm', APP__DB_TABLE_PREFIX . 'user', 'u', 'lcsm.user_id = u.user_id')
                ->where('u.source_id = ?')
                ->andWhere('u.username IN (?)');

            $queryBuilder->setParameter(0, $source_id);
            $queryBuilder->setParameter(1, $username, $dbConn::PARAM_INT_ARRAY);
        } else {
            $queryBuilder
                ->select('lcm.module_id', 'lcm.module_title', 'lcm.module_code', '"' . APP__USER_TYPE_ADMIN . '" as user_type')
                ->from(APP__DB_TABLE_PREFIX . 'module lcm')
                ->where('lcm.source_id = ?');

            $queryBuilder->setParameter(0, $source_id);
        }

        return $queryBuilder->execute()->fetchAllAssociativeIndexed();
    }

    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */

    public function get_user_academic_years($user_id = null)
    {
        if (!empty($user_id)) {
            $query = 'SELECT MIN(a.open_date) first, MAX(a.open_date) last ' .
                'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
                'WHERE m.source_id = ? AND m.module_id = ?';

            $dates = $this->dbConn->fetchAssociative($query, [$this->sourceId, $this->moduleId], [ParameterType::STRING, ParameterType::INTEGER]);
        } else {
            $query = 'SELECT MIN(a.open_date) first, MAX(a.open_date) last ' .
                'FROM ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
                'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ON a.module_id = m.module_id ' .
                'WHERE m.source_id = ?';

            $dates = $this->dbConn->fetchAssociative($query, [$this->sourceId], [ParameterType::STRING]);
        }

        // Ensure that the first record contains some dates as we could return a null record
        if (!empty($dates['first'])) {
            $years[] = AcademicYear::dateToYear(strtotime($dates['first']));
            $years[] = AcademicYear::dateToYear(strtotime($dates['last']));
        } else {
            $years[] = AcademicYear::dateToYear(time());
            $years[] = $years[0];
        }

        return $years;
    }
}
