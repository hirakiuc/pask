<?php
/**
 *
 */

require_once(dirname(__FILE__)."/../lib/include.php");

class HelloPask extends Pask{

  public $desc = "Hello Pask!";

  public $before_tasks = array("hello_world_task");

  public function run(){
    echo "Hello Pask !\n"; 
  }
} 
?>
