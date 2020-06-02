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

use WebPA\includes\functions\Common;

class Assessment {

  // Public Vars
  public $id = null;
  public $name = '';
  public $module_id = null;

  public $open_date = null;
  public $close_date = null;
  public $introduction = '';

  public $allow_feedback = false;
  public $assessment_type = 1;
  public $allow_assessment_feedback = false;
  public $feedback_name = 'feedback';
  public $email_opening = false;
  public $email_closing = false;

  // Private Vars
  private $_DAO = null;
  private $_xml_parser = null;

  private $_collection = null;
  private $_collection_id = null;

  private $_form = null;
  private $_form_xml = '';

  private $_finished = false;

  private $_locked = null;

  /**
  * CONSTRUCTOR for the assessment class
  *
  * @param string $DAO
  */
  function __construct(&$DAO) {
    $this->_DAO =& $DAO;
    $this->_locked = null;
  }// /->Assessment()

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
  function create() {
    // generate a new project_id
    while (true) {
      $new_id = Common::uuid_create();
      if ($this->_DAO->fetch_value("SELECT COUNT(assessment_id) FROM " . APP__DB_TABLE_PREFIX . "assessment WHERE assessment_id = '$new_id'") == 0) {
        break;
      }
    }
    $this->id = $new_id;
  }// ->create()

  /**
  * Delete this Assessment
  *
  * @return boolean true
  */
  function delete() {
    $this->_DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "assessment_group_marks WHERE assessment_id = '{$this->id}'");
    $this->_DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "assessment_marking WHERE assessment_id = '{$this->id}'");
    $this->_DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_mark WHERE assessment_id = '{$this->id}'");
    $this->_DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_justification WHERE assessment_id = '{$this->id}'");
    $this->_DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_response WHERE assessment_id = '{$this->id}'");
    $collection = $this->_DAO->fetch_value("SELECT collection_id FROM " . APP__DB_TABLE_PREFIX . "assessment WHERE assessment_id = '{$this->id}'");
    $this->_DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "assessment WHERE assessment_id = '{$this->id}'");
    $group_handler = new GroupHandler();
    $collection = $group_handler->get_collection($collection);
    $collection->delete();
    return true;
  }// /->delete()

  /**
  * Load the Assessment from the database
  *
  * @param string $id id of Group to load
  *
  * @return boolean did load succeed
  */
  function load($id) {
    $this->_locked = null;

    $row = $this->_DAO->fetch_row("SELECT * FROM " . APP__DB_TABLE_PREFIX . "assessment WHERE assessment_id = '$id' LIMIT 1");
    return ($row) ? $this->load_from_row($row) : false;
  }// /->load()

  /**
  * Load the Assessment from the given row
  *
  * @param array $row associative-array of assessment information
  *
  * @return boolean did load sucessed
  */
  function load_from_row($row) {
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
  }// /->load_from_row()

  /**
  * Save this Assessment
  *
  * @return boolean did save succeed
  */
  function save() {
    if (!$this->id) {
      return false;
    } else {
      // Save the Form
      $fields = array (
      	       'assessment_id'    => $this->id ,
               'assessment_name'    => $this->name ,
               'module_id'       => $this->module_id ,
               'collection_id'    => $this->_collection_id ,
               'form_xml'       => $this->_form_xml ,
               'open_date'      => date(MYSQL_DATETIME_FORMAT, $this->open_date) ,
               'close_date'     => date(MYSQL_DATETIME_FORMAT, $this->close_date) ,
               'retract_date'     => date(MYSQL_DATETIME_FORMAT, $this->close_date) ,
               'introduction'     => $this->introduction ,
               'allow_feedback'   => ($this->allow_feedback) ? 1 : 0 ,
               'assessment_type'    => ($this->assessment_type)? 1 : 0 ,
               'student_feedback'   => ($this->allow_assessment_feedback)? 1 : 0 ,
               'contact_email'    => '' ,
               'email_opening'    => ($this->email_opening)? 1 : 0 ,
               'email_closing'    =>($this->email_closing)? 1 : 0 ,
               'feedback_name'    => $this->feedback_name ,
               'feedback_length'    => 0 ,
               'feedback_optional'    => 0
      );

      $ass_name = $this->_DAO->escape_str($fields['assessment_name']);
      $ass_intro = $this->_DAO->escape_str($fields['introduction']);
      $xml = $this->_DAO->_prepare_field_value($fields['form_xml']);

      $sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'assessment ({fields}) VALUES ({values}) ';
      $sql .= "ON DUPLICATE KEY UPDATE assessment_name = '{$ass_name}', module_id = {$fields['module_id']}, " .
              "collection_id = '{$fields['collection_id']}', form_xml = {$xml}, " .
              "open_date = '{$fields['open_date']}', close_date = '{$fields['close_date']}', " .
              "retract_date = '{$fields['retract_date']}', " .
              "introduction = '{$ass_intro}', allow_feedback = {$fields['allow_feedback']}, " .
              "assessment_type = {$fields['assessment_type']}, student_feedback = {$fields['student_feedback']}, " .
              "contact_email = '{$fields['contact_email']}', " .
              "email_opening = {$fields['email_opening']}, email_closing = {$fields['email_closing']}, " .
              "feedback_name = '{$fields['feedback_name']}', " .
              "feedback_length = {$fields['feedback_length']}, feedback_optional = {$fields['feedback_optional']}";
      $this->_DAO->do_insert($sql, $fields);

      return true;
    }
  }// /->save()

/*
* --------------------------------------------------------------------------------
* Other Methods
* --------------------------------------------------------------------------------
*/

  /**
  * Create a clone of this assessment
  * @return mixed
  */
  function & get_clone() {
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

  function get_db() {
    return $this->_DAO;
  }// /->get_db

  function get_form() {
    // Get the number of questions used in this assessment, and create an array of that size
    $form = new Form($db);
    $form_xml =& $this->_form_xml;
    $form->load_from_xml($form_xml);

    return $form;
  }// /->get_form()

  function get_form_xml() {
    return $this->_form_xml;
  }// /->get_form_xml()

  /**
   * Get the group marks.
   *
   */
  function get_group_marks() {
    $groups_and_marks = array();

    $group_marks_xml = $this->_DAO->fetch_value("
       SELECT group_mark_xml
       FROM " . APP__DB_TABLE_PREFIX . "assessment_group_marks
       WHERE assessment_id = '{$this->id}'");

    if ($group_marks_xml) {
      $xml_parser = new XMLParser();
      $xml_array = $xml_parser->parse($group_marks_xml);

      // If there's more than 1 group that's fine, else make it a 0-based array of 1 group
      if (array_key_exists(0, $xml_array['groups']['group'])) {
        $groups = $xml_array['groups']['group'];
      } else {
        $groups[0] = $xml_array['groups']['group'];
      }
      foreach($groups as $i => $group) {
        $groups_and_marks["{$group['_attributes']['id']}"] = $group['_attributes']['mark'];
      }
    }

    return $groups_and_marks;
  }// /->get_group_marks()

  function set_form_xml($xml) {
    $this->_form_xml = $xml;
  }// /->set_form_xml()

  function get_collection_id() {
    return $this->_collection_id;
  }// /->get_collection_id()

  function set_collection_id($collection_id) {
    $this->_collection_id = $collection_id;
  }// /->set_collection_id()

  /*
  * Get the current status of this assessment
  *
  * @return  string  ['pending','open','closed','finished']
  */
  function get_status() {
    $now = time();

    $status = 'unknown';
    if ($this->open_date > $now) { $status = 'pending'; }
    if ($this->open_date < $now) { $status = 'open'; }
    if ($this->close_date < $now) { $status = 'closed'; }
    if ($this->_finished) { $status = 'finished'; }

    return $status;
  }// /->get_status

  /**
   * function to get the date string
   * @param date $date
   * @return string formated date
  */
  function get_date_string($date) {
    $date_format = 'D, jS F, Y \a\t G:i';
    if ($date == 'open_date') { return date($date_format,$this->open_date); }
    if ($date == 'close_date') { return date($date_format,$this->close_date); }
  }// /->get_date_string()

  /**
   * Get all the marksheets available for this assessment.
   *
   * Output of the form: array ( date_created => array ( <params> ) )
   *
   * @return  mixed  An assoc array of marksheets available. On fail, null.
   */
  function get_all_marking_params() {
    $params = null;

    $mark_sheets = $this->_DAO->fetch("
      SELECT date_created, marking_params
      FROM " . APP__DB_TABLE_PREFIX . "assessment_marking
      WHERE assessment_id = '{$this->id}'
      ORDER BY date_created ASC
    ");

    if ($mark_sheets) {
      foreach($mark_sheets as $i => $mark_sheet) {
        $params[$mark_sheet['date_created']] = $this->_parse_marking_params($mark_sheet['marking_params']);
      }
    }

    return $params;
  }// /->get_all_marking_params()

  /**
   * Enter description here...
   *
   * @param  datetime  $marksheet_id  The marksheet to load
   *
   * return  mixed  An array of marking parameters. On fail, null.
   */
  function get_marking_params($marksheet_id) {
    $params = null;

    $marking_date_sql = date(MYSQL_DATETIME_FORMAT, $marksheet_id);

    $marking_params = $this->_DAO->fetch_value("SELECT marking_params
                          FROM " . APP__DB_TABLE_PREFIX . "assessment_marking
                          WHERE assessment_id = '{$this->id}'
                            AND date_created='$marking_date_sql'
                          LIMIT 1");
    if ($marking_params) {
      $params = $this->_parse_marking_params($marking_params);
    }

    return $params;
  }// /->get_marking_params()

  /**
  * Is this Assessment locked for editing
  *
  * @return bool lock status
  */
  function is_locked() {
    if (is_null($this->_locked)) {
      $result_count = $this->_DAO->fetch_value("SELECT COUNT(assessment_id)
                           FROM " . APP__DB_TABLE_PREFIX . "user_mark
                           WHERE assessment_id = '{$this->id}'");

      $this->_locked = ($result_count>0);
    }
    return $this->_locked;
  }// /->is_locked()

  /**
  * Set database connection
  * @param  object  $db  The database connection object to use
  */
  function set_db(& $db) {
    $this->_DAO =& $db;
  }// /->set_db()

/*
* --------------------------------------------------------------------------------
* Methods
* --------------------------------------------------------------------------------
*/

  /*
  * Finish this assessment, save settings and lock from editing/marking
  */
  function finish() {
  }// /->finish()

/*
* ================================================================================
* Private Methods
* ================================================================================
*/

  protected function _parse_marking_params($marking_params_xml) {
    $params = null;

    if (!is_object($this->_xml_parser)) { $xml_parser = new XMLParser(); }

    $xml_array = $xml_parser->parse($marking_params_xml);

    $params['weighting'] = $xml_array['parameters']['weighting']['_attributes']['value'];
    $params['penalty'] = $xml_array['parameters']['penalty']['_attributes']['value'];

    $params['penalty_type'] = (array_key_exists('penalty_type', $xml_array['parameters'])) ? $xml_array['parameters']['penalty_type']['_attributes']['value'] : '%' ;

    $params['tolerance'] = (array_key_exists('tolerance', $xml_array['parameters'])) ? $xml_array['parameters']['tolerance']['_attributes']['value'] : null ;

    $params['grading'] = (array_key_exists('grading', $xml_array['parameters'])) ? $xml_array['parameters']['grading']['_attributes']['value'] : 'numeric' ;

    $params['algorithm'] = (array_key_exists('algorithm', $xml_array['parameters'])) ? $xml_array['parameters']['algorithm']['_attributes']['value'] : 'webpa' ;

    return $params;
  }// /->_parse_marking_params()

}// /class: Assessment

?>
