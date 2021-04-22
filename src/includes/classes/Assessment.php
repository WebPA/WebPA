<?php
/**
 * Assessment.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\ParameterType;
use WebPA\includes\functions\Common;

class Assessment
{
    // Public Vars
    public $id;

    public $name = '';

    public $module_id;

    public $open_date;

    public $close_date;

    public $introduction = '';

    public $allow_feedback = false;

    public $assessment_type = 1;

    public $allow_assessment_feedback = false;

    public $feedback_name = 'feedback';

    public $email_opening = false;

    public $email_closing = false;

    // Private Vars
    private DAO $_DAO;

    private $dbConn;

    private $_xml_parser;

    private $_collection;

    private $_collection_id;

    private $_form;

    private $_form_xml = '';

    private $_finished = false;

    private $_locked;

    /**
    * CONSTRUCTOR for the assessment class
    *
    * @param DAO $DAO
    */
    public function __construct(DAO $DAO)
    {
        $this->_DAO =& $DAO;
        $this->dbConn = $this->_DAO->getConnection();
        $this->_locked = null;
    }

    // /->Assessment()

    /**
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /**
    * --------------------------------------------------------------------------------
    * Load/Save Functions
    * --------------------------------------------------------------------------------
    */

    /**
    * Create a new Assessment ID
    */
    public function create()
    {
        // generate a new project_id
        while (true) {
            $new_id = Common::uuid_create();
            $projectIdQuery =
          'SELECT COUNT(assessment_id) ' .
          'FROM ' . APP__DB_TABLE_PREFIX . 'assessment ' .
          'WHERE assessment_id = ?';

            $projectCount = $this->dbConn->fetchOne($projectIdQuery, [$new_id], [ParameterType::STRING]);

            if ($projectCount == 0) {
                break;
            }
        }

        $this->id = $new_id;
    }

    /**
    * Delete this Assessment
    *
    * @return boolean true
    */
    public function delete()
    {
        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'assessment_group_marks WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'assessment_marking WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_mark WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_justification WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_response WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $collectionQuery =
        'SELECT collection_id ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment ' .
        'WHERE assessment_id = ?';

        $collection = $this->dbConn->fetchOne($collectionQuery, [$this->id], [ParameterType::STRING]);

        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'assessment WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $group_handler = new GroupHandler();
        $collection = $group_handler->get_collection($collection);
        $collection->delete();

        return true;
    }

    /**
    * Load the Assessment from the database
    *
    * @param string $id id of Group to load
    *
    * @return boolean did load succeed
    */
    public function load($id)
    {
        $this->_locked = null;

        $assessmentQuery =
        'SELECT * ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment ' .
        'WHERE assessment_id = ? ' .
        'LIMIT 1';

        $row = $this->dbConn->fetchAssociative($assessmentQuery, [$id], [ParameterType::STRING]);

        return $row ? $this->load_from_row($row) : false;
    }

    /**
    * Load the Assessment from the given row
    *
    * @param array $row associative-array of assessment information
    *
    * @return boolean did load sucessed
    */
    public function load_from_row($row)
    {
        $this->id = $row['assessment_id'];
        $this->name = $row['assessment_name'];
        $this->module_id = $row['module_id'];
        $this->_collection_id = $row['collection_id'];
        $this->_form_xml = $row['form_xml'];
        $this->open_date = strtotime($row['open_date']);
        $this->close_date = strtotime($row['close_date']);
        $this->introduction = $row['introduction'];
        $this->allow_feedback = ($row['allow_feedback']==1);
        $this->assessment_type = ($row['assessment_type']); //==1);
        $this->allow_assessment_feedback = ($row['student_feedback']);
        $this->email_opening = ($row['email_opening']);
        $this->email_closing = ($row['email_closing']);
        $this->feedback_name = ($row['feedback_name']);

        return true;
    }

    // /->load_from_row()

    /**
    * Save this Assessment
    *
    * @return boolean did save succeed
    */
    public function save()
    {
        if (!$this->id) {
            return false;
        }

        // check if assessment already exists in the database
        $storedAssessmentId = $this->dbConn->fetchOne(
            'SELECT assessment_id FROM ' . APP__DB_TABLE_PREFIX . 'assessment WHERE assessment_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $queryBuilder = $this->dbConn->createQueryBuilder();

        if (!empty($storedAssessmentId)) {
            // the assessment already exists so we should update it
            $queryBuilder
          ->update(APP__DB_TABLE_PREFIX . 'assessment')
          ->set('assessment_name', '?')
          ->set('module_id', '?')
          ->set('collection_id', '?')
          ->set('form_xml', '?')
          ->set('open_date', '?')
          ->set('close_date', '?')
          ->set('retract_date', '?')
          ->set('introduction', '?')
          ->set('allow_feedback', $this->allow_feedback ? 1 : 0)
          ->set('assessment_type', $this->assessment_type ? 1 : 0)
          ->set('student_feedback', $this->allow_assessment_feedback ? 1 : 0)
          ->set('contact_email', '""')
          ->set('email_opening', $this->email_opening ? 1 : 0)
          ->set('email_closing', $this->email_closing ? 1 : 0)
          ->set('feedback_name', '?')
          ->set('feedback_length', 0)
          ->set('feedback_optional', 0)
          ->where('assessment_id = ?')
          ->setParameter(0, $this->name)
          ->setParameter(1, $this->module_id, ParameterType::INTEGER)
          ->setParameter(2, $this->_collection_id)
          ->setParameter(3, $this->_form_xml)
          ->setParameter(4, date(MYSQL_DATETIME_FORMAT, $this->open_date))
          ->setParameter(5, date(MYSQL_DATETIME_FORMAT, $this->close_date))
          ->setParameter(6, date(MYSQL_DATETIME_FORMAT, $this->close_date))
          ->setParameter(7, $this->introduction)
          ->setParameter(8, $this->feedback_name)
          ->setParameter(9, $this->id);
        } else {
            // the assessment does not exist. Create it
            $queryBuilder
          ->insert(APP__DB_TABLE_PREFIX . 'assessment')
          ->values(
              [
                 'assessment_id' => '?',
                 'assessment_name' => '?',
                 'module_id' => '?',
                 'collection_id' => '?',
                 'form_xml' => '?',
                 'open_date' => '?',
                 'close_date' => '?',
                 'retract_date' => '?',
                 'introduction' => '?',
                 'allow_feedback' => $this->allow_feedback ? 1 : 0,
                 'assessment_type' => $this->assessment_type ? 1 : 0,
                 'student_feedback' => $this->allow_assessment_feedback ? 1 : 0,
                 'contact_email' => '""',
                 'email_opening' => $this->email_opening ? 1 : 0,
                 'email_closing' => $this->email_closing ? 1 : 0,
                 'feedback_name' => '?',
                 'feedback_length' => 0,
                 'feedback_optional' => 0,
              ]
          )
          ->setParameter(0, $this->id)
          ->setParameter(1, $this->name)
          ->setParameter(2, $this->module_id, ParameterType::INTEGER)
          ->setParameter(3, $this->_collection_id)
          ->setParameter(4, $this->_form_xml)
          ->setParameter(5, date(MYSQL_DATETIME_FORMAT, $this->open_date))
          ->setParameter(6, date(MYSQL_DATETIME_FORMAT, $this->close_date))
          ->setParameter(7, date(MYSQL_DATETIME_FORMAT, $this->close_date))
          ->setParameter(8, $this->introduction)
          ->setParameter(9, $this->feedback_name);
        }

        $queryBuilder->execute();

        return true;
    }

    /*
    * --------------------------------------------------------------------------------
    * Other Methods
    * --------------------------------------------------------------------------------
    */

    /**
    * Create a clone of this assessment
    * @return mixed
    */
    public function & get_clone()
    {
        $clone_assessment = new Assessment($this->_DAO);
        $clone_assessment->load($this->id);   // Creates an EXACT clone of this assessment
        $clone_assessment->create();
        return $clone_assessment;
    }

    /*
    * --------------------------------------------------------------------------------
    * Accessor Methods
    * --------------------------------------------------------------------------------
    */

    public function get_db()
    {
        return $this->_DAO;
    }

    public function get_form()
    {
        // Get the number of questions used in this assessment, and create an array of that size
        $form = new Form($this->get_db());
        $form_xml =& $this->_form_xml;
        $form->load_from_xml($form_xml);

        return $form;
    }

    // /->get_form()

    public function get_form_xml()
    {
        return $this->_form_xml;
    }

    // /->get_form_xml()

    /**
     * Get the group marks.
     *
     */
    public function get_group_marks()
    {
        $groups_and_marks = [];

        $groupsAndMarksQuery =
        'SELECT group_mark_xml ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment_group_marks ' .
        'WHERE assessment_id = ?';

        $group_marks_xml = $this->dbConn->fetchOne($groupsAndMarksQuery, [$this->id], [ParameterType::STRING]);

        if ($group_marks_xml) {
            $xml_parser = new XMLParser();
            $xml_array = $xml_parser->parse($group_marks_xml);

            // If there's more than 1 group that's fine, else make it a 0-based array of 1 group
            if (array_key_exists(0, $xml_array['groups']['group'])) {
                $groups = $xml_array['groups']['group'];
            } else {
                $groups[0] = $xml_array['groups']['group'];
            }
            foreach ($groups as $i => $group) {
                $groups_and_marks["{$group['_attributes']['id']}"] = $group['_attributes']['mark'];
            }
        }

        return $groups_and_marks;
    }

    // /->get_group_marks()

    public function set_form_xml($xml)
    {
        $this->_form_xml = $xml;
    }

    // /->set_form_xml()

    public function get_collection_id()
    {
        return $this->_collection_id;
    }

    // /->get_collection_id()

    public function set_collection_id($collection_id)
    {
        $this->_collection_id = $collection_id;
    }

    // /->set_collection_id()

    /*
    * Get the current status of this assessment
    *
    * @return  string  ['pending','open','closed','finished']
    */
    public function get_status()
    {
        $now = time();

        $status = 'unknown';
        if ($this->open_date > $now) {
            $status = 'pending';
        }
        if ($this->open_date < $now) {
            $status = 'open';
        }
        if ($this->close_date < $now) {
            $status = 'closed';
        }
        if ($this->_finished) {
            $status = 'finished';
        }

        return $status;
    }

    // /->get_status

    /**
     * function to get the date string
     * @param date $date
     * @return string formated date
    */
    public function get_date_string($date)
    {
        $date_format = 'D, jS F, Y \a\t G:i';
        if ($date == 'open_date') {
            return date($date_format, $this->open_date);
        }
        if ($date == 'close_date') {
            return date($date_format, $this->close_date);
        }
    }

    // /->get_date_string()

    /**
     * Get all the marksheets available for this assessment.
     *
     * Output of the form: array ( date_created => array ( <params> ) )
     *
     * @return  mixed  An assoc array of marksheets available. On fail, null.
     */
    public function get_all_marking_params()
    {
        $params = null;

        $markSheetsQuery =
      'SELECT date_created, marking_params ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'assessment_marking ' .
      'WHERE assessment_id = ? ' .
      'ORDER BY date_created ASC';

        $mark_sheets = $this->dbConn->fetchAllAssociative($markSheetsQuery, [$this->id], [ParameterType::STRING]);

        if ($mark_sheets) {
            foreach ($mark_sheets as $i => $mark_sheet) {
                $params[$mark_sheet['date_created']] = $this->_parse_marking_params($mark_sheet['marking_params']);
            }
        }

        return $params;
    }

    // /->get_all_marking_params()

    /**
     * Enter description here...
     *
     * @param  datetime  $marksheet_id  The marksheet to load
     *
     * return  mixed  An array of marking parameters. On fail, null.
     */
    public function get_marking_params($marksheet_id)
    {
        $params = null;

        $marking_date_sql = date(MYSQL_DATETIME_FORMAT, $marksheet_id);

        $markingParamsQuery =
        'SELECT marking_params ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'assessment_marking ' .
        'WHERE assessment_id = ? ' .
        'AND date_created = ? ' .
        'LIMIT 1';

        $marking_params = $this->dbConn->fetchOne($markingParamsQuery, [$this->id, $marking_date_sql], [ParameterType::STRING, ParameterType::STRING]);

        if ($marking_params) {
            $params = $this->_parse_marking_params($marking_params);
        }

        return $params;
    }

    // /->get_marking_params()

    /**
    * Is this Assessment locked for editing
    *
    * @return bool lock status
    */
    public function is_locked()
    {
        if (is_null($this->_locked)) {
            $countAssessmentsQuery =
          'SELECT COUNT(assessment_id) ' .
          'FROM ' . APP__DB_TABLE_PREFIX . 'user_mark ' .
          'WHERE assessment_id = ?';

            $result_count = $this->dbConn->fetchOne($countAssessmentsQuery, [$this->id], [ParameterType::STRING]);

            $this->_locked = ($result_count>0);
        }
        return $this->_locked;
    }

    // /->is_locked()

    /**
    * Set database connection
    * @param  object  $db  The database connection object to use
    */
    public function set_db(& $db)
    {
        $this->_DAO =& $db;
    }

    // /->set_db()

    /*
    * --------------------------------------------------------------------------------
    * Methods
    * --------------------------------------------------------------------------------
    */

    // Finish this assessment, save settings and lock from editing/marking
    public function finish()
    {
    }

    // /->finish()

    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */

    protected function _parse_marking_params($marking_params_xml)
    {
        $params = null;

        if (!is_object($this->_xml_parser)) {
            $xml_parser = new XMLParser();
        }

        $xml_array = $xml_parser->parse($marking_params_xml);

        $params['weighting'] = $xml_array['parameters']['weighting']['_attributes']['value'];
        $params['penalty'] = $xml_array['parameters']['penalty']['_attributes']['value'];

        $params['penalty_type'] = (array_key_exists('penalty_type', $xml_array['parameters'])) ? $xml_array['parameters']['penalty_type']['_attributes']['value'] : '%' ;

        $params['tolerance'] = (array_key_exists('tolerance', $xml_array['parameters'])) ? $xml_array['parameters']['tolerance']['_attributes']['value'] : null ;

        $params['grading'] = (array_key_exists('grading', $xml_array['parameters'])) ? $xml_array['parameters']['grading']['_attributes']['value'] : 'numeric' ;

        $params['algorithm'] = (array_key_exists('algorithm', $xml_array['parameters'])) ? $xml_array['parameters']['algorithm']['_attributes']['value'] : 'webpa' ;

        return $params;
    }
}
