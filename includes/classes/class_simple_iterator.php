<?php
/**
 *
 * Class : SimpleIterator
 *
 * Simple version of an Array Iterator
 * Not much error checking to keep the class light
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * @since 19-08-2005
 *
 */

class SimpleIterator {
  // Public Vars
  public $array = null;
  public $count = 0;

  // Private Vars

  private $_key = null;
  private $_value = null;

  /**
  * CONSTRUCTOR for the simple iterator class
  * @param array $array
  */
  function SimpleIterator(&$array) {
    // sub-classes can override the creator, so all the work is done in _initialise()
    $this->_initialise($array);
  }// /->SimpleIterator()

/*
* ================================================================================
* Public Methods
* ================================================================================
*/

/**
 * function current
 * @return integer
 */
  function &current() {
    return $this->_value;
  }// /->current()

/**
 * function next
 */
  function next() {
    next($this->array);
    $this->_key = key($this->array);
    if ("$this->_key" != '') { $this->_value =& $this->array[$this->_key]; }
    else { $this->_value = null; }
  }// /->next()

/**
 * function reset
 */
  function reset() {
    reset($this->array);
    $this->_key = key($this->array);
    if ("$this->_key" != '') { $this->_value =& $this->array[$this->_key]; }
    else { $this->_value = null; }
  }// /->reset()

/**
 * function size
 * @return integer
 */
  function size() {
    return $this->count;
  }// /->size()

/**
 * function to check validity
 * @return boolean
 */
  function is_valid() {
    return ("$this->_key" != '');
  }// /->is_valid()

/*
* ================================================================================
* Private Methods
* ================================================================================
*/
/**
 * Function to initalise
 * @param array $array
 */
  function _initialise(&$array) {
    $this->array =& $array;
    $this->count = count($array);
    if ($this->count==0) { $this->array = array(); }
    $this->reset();
  }// /->_intialise()

}// /class: SimpleIterator

?>
