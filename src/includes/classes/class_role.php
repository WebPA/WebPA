<?php
/**
 * class Role
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

class Role {
  public $id = null;
  public $name = '';
  public $desc = '';
  public $flags = 0;

  /**
   *  CONSTRUCTOR for the role
  */
  function __construct() {
    $this->id = null;
    $this->name = '';
    $this->desc = '';
    $flags = 0;
  }// /->Role()

  /*
  ========================================
  PUBLIC
  ========================================
  */
  /**
   * Function Load role
   */
  function load($role_id) {} // /->load()

  /**
   * Function save
   */
  function save() { } // /->save()

  /**
   * function delete
   */
  function delete() { } // /->delete()

  /**
   *  Get all available flag constants
   * @return array
  */
  function available_flags() {
    return array  ('protected'  => 1024 );
  } // /->available_flags()

  /**
   *  Check if this (or the given $check_flags) have the flag named $flag set
   * @param string $flag
   * @param array $check_flags
   * @return array
  */
  function has_flag($flag = '', $check_flags = null) {
    if (!$check_flags) {
      $check_flags = (isset($this)) ? $check_flags = $this->flags : 0;
    }

    $role_flags = Role::available_flags();

    return check_bits($check_flags,$role_flags[$flag]);
  } // /->has_flag()

}
?>
