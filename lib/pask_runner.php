<?php 

require_once('pask_loader.php');

/**
 *
 */
class PaskRunner{

  /** */
  private $task_loader = null;

  /**
   *
   */
  public function __construct($loader){ 
    $this->loader = $loader;
  }

  /**
   *
   */
  public function run_task($task_name){ 
    $stack = $loader->create_taskstack($task_name); 

    while(count($stack)!=0){
      $task_data = array_pop($stack);

      $task_data['pask']->run();
    } 
  }
}

?> 

