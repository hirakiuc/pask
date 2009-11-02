<?php

/**
 * The Class for Simple use for Command Line Interface
 *
 * - check and analyze arguments.
 * - call PaskRunner/Loader if need.
 */
class Application{

/**
 * -d : user defined paskfile directory path.
 * -t : show defined tasks in paskfiles.
 * -v : vorbose mode.
 * -h : show help.
 */

  /** default arguments array */
  public $conf = array(
    'paskdir' => './tasks' ,  // default paskfile directory path
    'debug'   => FALSE     ,  // -v option
    'help'    => FALSE     ,  // -h option
    'list'    => FALSE        // -t option
  );

  /** PaskRunner instance */
  private $runner = null;

  /** PaskLoader instance */
  private $loader = null;

  /**
   *
   */
  public function __construct($args){ 
    // MEMO something to do ?
    $this->loader = new PaskLoader($this->conf['paskdir']); 
    
    $this->runner = new PaskRunner($loader, $this->conf); 
  }

  /**
   * call first.
   */
  public function run(){
    // opt parser ??

    // if -d specified, overwrite $this->conf['paskdir']
  }


  //------------------------------------------
  /**
   * Run task.
   */
  private function run_tasks($task_name, $task_args){


    $runner->run_tasks($task_name, $task_args);
  }

  //------------------------------------------
  /**
   * show defined tasks.
   */
  private function show_tasklist(){
    $ary = $loader->get_tasks_desc();

    // TODO refine output string...
    foreach($ary as $task){
      echo $task['name'] . ':' . $task['desc'] . '\n'; 
    };
  }

}

?>

