<?php
/**
 *
 *  Class : SimpleObjectIterator
 *
 * Simple version of an Array Object Iterator
 * Objects being iterated MUST support ->load_from_row() method
 * Not much error checking to keep the class light
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * @since 24-06-2005
 *
 * Updates:
 * 24-06-05 : Fixed a bug when trying to iterate a non-array
 *
 */

class SimpleObjectIterator {
  // Public Vars
  public $array;
  public $class_name;
  public $class_constructor_args;
  public $count;

  // Private Vars
  private $_key;
  private $_value;

  /**
  * CONSTRUCTOR for the simple object iterator
  * @param array $array
  * @param string $class_name
  * @param string $constructor_args
  */
  function SimpleObjectIterator(&$array, $class_name = '', $constructor_args = '') {
    $this->_initialise($array);
    $this->class_name = $class_name;
    $this->class_constructor_args = $constructor_args;
  }// /->SimpleObjectIterator()

/*
* --------------------------------------------------------------------------------
* Public Methods
* --------------------------------------------------------------------------------
*/

  /**
  * Get object at current pointer position
  * @return integer
  */
  function &current() {
    $temp = null;
    eval("\$temp = new {$this->class_name}({$this->class_constructor_args});");   // eval allows us to include arguments for the constructor
    $temp->load_from_row($this->_value);
    return $temp;
  }// /->current()

  /**
  * Move pointer to the next object in the list
  */
  function next() {
    next($this->array);
    $this->_key = key($this->array);
    $this->_value = ("$this->_key" != '') ? $this->array[$this->_key] : null ;
  }// /->next()

  /**
  * Reset pointer to the start of the list
  */
  function reset() {
    reset($this->array);
    $this->_key = key($this->array);
    $this->_value = ("$this->_key" != '') ? $this->array[$this->_key] : null ;
  }// /->reset()

  /**
  * Get the number of objects in the list
  *
  * @return integer size of the object list
  */
  function size() {
    return $this->count;
  }// /->size()

  /*
  * Is the current pointer position valid?
  * @return boolean
  */
  function is_valid() {
    return ("$this->_key" != '');
  }// /->is_valid()

/*
* --------------------------------------------------------------------------------
* Private Methods
* --------------------------------------------------------------------------------
*/

  /**
  * Initialise the object iterator
  * @param array $array
  */
  function _initialise(&$array) {
    $this->array =& $array;
    $this->count = count($array);
    if ($this->count==0) { $this->array = array(); }
    $this->reset();

    $this->class_name = '';
    $this->constructor_args = '';
  }// /->_intialise()

}// /class: SimpleObjectIterator

?>
