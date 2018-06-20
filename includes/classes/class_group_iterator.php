<?php
/**
 *
 * Class : GroupIterator
 *
 * Group Object version of the SimpleObjectIterator
 * Not much error checking to keep the class light
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * @since 14-06-2005
 *
 */

require_once('class_simple_object_iterator.php');
require_once('class_group.php');

class GroupIterator extends SimpleObjectIterator {
  // Private Vars
  private $_DAO = null;
  private $_groupset = null;

  /**
  * CONSTRUCTOR for the group iterator
  * @param object $groups
  * @param object $DAO
  * @param object $collection
  */
  function __construct($groups, &$DAO, &$collection) {
    $this->_initialise($groups);
    $this->_DAO =& $DAO;
    $this->_collection =& $collection;
    $this->class_name = 'Group';
    $this->class_constructor_args = null;
  }// /->GroupIterator()

/*
* --------------------------------------------------------------------------------
* Public Methods
* --------------------------------------------------------------------------------
*/

  /**
  * Get object at current pointer position
  * return Object
  */
  function & current() {
    $temp = new Group();
    $temp->set_dao_object($this->_DAO);
    $temp->set_collection_object($this->_collection);
    $temp->load_from_row($this->_value);
    return $temp;
  }// /->current()

/*
* --------------------------------------------------------------------------------
* Private Methods
* --------------------------------------------------------------------------------
*/

}// /class: GroupIterator

?>
