<?php

/**
 * Abstract Task Class
 */
class Pask{
  /** task description strings. */
  public $desc = "";

  /** Ths tasks depend on this task in ordered array.*/
  public $before_tasks = array(); 

  /** need to implement task script in pure php scripts. */
  public function run(){}
}

?> 

