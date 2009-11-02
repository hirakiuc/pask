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

  /** map taskname with filepath */
  private $paskfile_map = null;

  /**
   * Constractor
   */
  public function __construct($loader, $conf){ 
    $this->loader = $loader;
    $this->paskfile_map = $loader->get_paskfile_map();
    $this->conf = $conf;
  }

  /**
   * Do task 
   */
  public function run_task($task_name, $task_args){ 
    $stack = $loader->create_taskstack($task_name); 

    echo "Run    '" . $task_name . "' task.\n";

    while(count($stack)!=0){
      try{ 
        $task_data = array_pop($stack); 

        if($this->conf['debug']){
          echo "Start '" . $task_data['task_name'] . "'\n";
        }

        $task_data['pask']->run();

        if($this->conf['debug']){ 
          echo "End   '" . $task_data['task_name'] . "'\n";
        }

      }catch(Exception $err){
        throw $err;
      }
    } 

    echo "Finish '" . $task_name . "' task.\n";
  } 
} 

?> 

