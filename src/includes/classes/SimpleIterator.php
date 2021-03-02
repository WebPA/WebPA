<?php
/**
 * Class : SimpleIterator
 *
 * Simple version of an Array Iterator
 * Not much error checking to keep the class light
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

class SimpleIterator
{
    // Public Vars
    public $array;

    public $count = 0;

    // Private Vars

    private $_key;

    private $_value;

    /**
    * CONSTRUCTOR for the simple iterator class
    * @param array $array
    */
    public function __construct(&$array = [])
    {
        // sub-classes can override the creator, so all the work is done in _initialise()
        $this->_initialise($array);
    }

    // /->SimpleIterator()

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /**
     * function current
     * @return integer
     */
    public function &current()
    {
        return $this->_value;
    }

    // /->current()

    /**
     * function next
     */
    public function next()
    {
        next($this->array);
        $this->_key = key($this->array);
        if ("$this->_key" != '') {
            $this->_value =& $this->array[$this->_key];
        } else {
            $this->_value = null;
        }
    }

    // /->next()

    /**
     * function reset
     */
    public function reset()
    {
        reset($this->array);
        $this->_key = key($this->array);
        if ("$this->_key" != '') {
            $this->_value =& $this->array[$this->_key];
        } else {
            $this->_value = null;
        }
    }

    // /->reset()

    /**
     * function size
     * @return integer
     */
    public function size()
    {
        return $this->count;
    }

    // /->size()

    /**
     * function to check validity
     * @return boolean
     */
    public function is_valid()
    {
        return "$this->_key" != '';
    }

    // /->is_valid()

    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */

    /**
     * Function to initalise
     * @param array $array
     */
    public function _initialise(&$array = [])
    {
        $this->array =& $array;
        $this->count = count($array);

        $this->reset();
    }

    // /->_intialise()
}// /class: SimpleIterator
