<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Abstract Task file Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

/**
 * Abstract Task Class
 * @access abstract
 */
abstract class Pask{
  /** task description strings. */
  public $desc = "";

  /** Ths tasks depend on this task in ordered array.*/
  public $before_tasks = array(); 

  /** need to implement task script in pure php scripts. */
  abstract public function run();
} 
?>
