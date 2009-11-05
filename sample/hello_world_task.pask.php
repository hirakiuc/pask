<?php

require_once(dirname(__FILE__)."/../lib/include.php");

class HelloWorldTask extends Pask{

  public $desc = "Hello World Task!";

  public $before_tasks = array();

  public function run(){
    echo "Hello World !\n"; 
  }
}

?>
