<?php
/**
 *
 * class Full_Iterator
 *
 * Fully featured, heavy-duty ArrayIterator - Use the simpler iterator for most things!
 * Not much error checking to keep the class light
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class Full_Iterator /* implements abstract_FullIterator */ {
  public $array = null;
  public $count = 0;

  /* Private */

  private $_key = null;
  private $_value = null;

  /**
   *  CONSTRUCTOR for the class full interator
   * @param array $array
  */
  function __construct(&$array) {
    // sub-classes can override the creator, so all the work is done in _initialize()
    $this->_initialize($array);
  }// /->Full_Iterator()

  /*
  ========================================
  PUBLIC
  ========================================
  */
  /**
   * Function current
   * @return integer
   */
  function &current() {
    return $this->_value;
  }// /->current()

/**
 * Function end
 */
  function end() {
    end($this->array);
    $this->_key = key($this->array);
    $this->_value = ("$this->_key" != '') ? $this->array[$this->_key] : null ;
  }// /->end()

/**
 * function next
 */
  function next() {
    next($this->array);
    $this->_key = key($this->array);
    $this->_value = ("$this->_key" != '') ? $this->array[$this->_key] : null ;
  }// /->next()

/**
 * function position
 */
  function position() {
    return $this->_key;
  }// /->position()

/**
 * function previous
 */
  function prev() {
    prev($this->array);
    $this->_key = key($this->array);
    $this->_value = ("$this->_key" != '') ? $this->array[$this->_key] : null ;
  }// /->prev()

/**
 * function reset
 */
  function reset() {
    reset($this->array);
    $this->_key = key($this->array);
    $this->_value = ("$this->_key" != '') ? $this->array[$this->_key] : null ;
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
  ========================================
  PRIVATE
  ========================================
  */
/**
 * function to initalise
 * @param array $array
 */
  function _initialize(&$array) {
    $this->array = $array;
    $this->count = count($array);
    $this->reset();
  }// /->_intialize()

}// /class: FullIterator

?>
