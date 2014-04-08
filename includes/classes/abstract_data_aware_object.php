<?php
/**
 *
 * Contains : abstract class DataAwareObject
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class DataAwareObject {

  // Public
  public $DAO = null;
  public $id = null;

  /**
  * CONSTRUCTOR for the DataAwareObject
  * @param object $DAO
  */
  function DataAwareObject(&$DAO) {
    $this->DAO =& $DAO;
  }// /->DataAwareObject()

  /*
  ========================================
  PUBLIC
  ========================================
  */

  /**
  * Delete the object's from the database (using current id)
  */
  function delete() {
  }// /->delete()

  /**
  * Load object attributes from the database using the given id
  * @param mixed $id
  */
  function load($id = null) {
  }// /->load()

  /**
  * Load object attributes from the given $row
  * @param array  $row associative-array of key => values
  */
  function load_from_row($row) {
  }// /->load_from_row()

  /**
  *Save the object to the database (using current id)
  *if $this->id is null, then create a new database entry
  */
  function save() {
  }// /->save()

}

?>
