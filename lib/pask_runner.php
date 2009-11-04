<?php 

require_once('pask.php');
require_once('pask_loader.php');

/**
 * TaskRunner for paskfile.
 */
class PaskRunner{

  /** paskfile loader instance */
  private $loader = null;

  /** system config */
  private $conf = null;

  /** output writer */
  private $writer = null;

  /** map taskname with filepath */
  private $paskfile_map = null;

  /**
   * Constractor
   */
  public function __construct($loader, $conf, $writer){ 
    $this->loader = $loader;
    $this->conf = $conf;
    $this->writer = $writer; 

    $this->paskfile_map = $loader->get_paskfile_map();
  }

  /**
   * Do task 
   */
  public function run_task($task_name, $task_args){ 
    $stack = $loader->create_taskstack($task_name); 

    ($this->writer)->puts("Run    '" . $task_name . "' task.");

    while(count($stack)!=0){
      try{ 
        $task_data = array_pop($stack); 

        ($this->writer)->verbose("Start '" . $task_data['task_name']);

        $task_data['pask']->run();

        ($this->writer)->verbose("End   '" . $task_data['task_name']);

      }catch(Exception $err){
        throw $err;
      }
    } 

    ($this->writer)->puts("Finish '" . $task_name . "' task.");
  } 
} 

?> 
