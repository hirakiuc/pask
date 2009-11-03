<?php

require_once('Console/CommandLine.php');

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

  /** config value array */
  public $conf = null;

  /** PaskRunner instance */
  private $runner = null;

  /** PaskLoader instance */
  private $loader = null;

  /**
   *
   */
  public function __construct(){ 
  }

  /**
   * call first.
   */
  public function run(){
    // opt parser ??
    $parser = $this->create_optparser();

    try{
      $this->conf = $parser->parse();
    }catch(Exception $err){
      $parser->displayError($err->getMessage());
      exit(-1);
    }

//    $this->loader = new PaskLoader($this->conf['paskdir']); 
//    $this->runner = new PaskRunner($loader, $this->conf); 

  }

  /**
   *
   */
  private function create_optparser(){
    $parser = new Console_CommandLine(array(
      'description' => 'Task Management Framework on PHP',
      'version'     => '0.0.1' 
    ));

    $parser->addOption('paskdir', array(
      'short_name' => '-d',
      'long_name'  => '--paskdir',
      'description'=> 'specify paskfile directory.',
      'help_name'  => 'PASKDIR',
      'action'     => 'StoreString',
      'default'    => '../tasks'
    )); 

    $parser->addOption('verbose', array(
      'short_name' => '-v',
      'long_name'  => '--verbose',
      'description'=> 'verbose mode.',
      'help_name'  => 'VERBOSE',
      'action'     => 'StoreTrue',
      'default'    => FALSE
    ));

    $parser->addOption('quiet', array(
      'short_name' => '-q',
      'long_name'  => '--quiet',
      'description'=> 'quiet mode.',
      'help_name'  => 'QUIET',
      'action'     => 'StoreTrue',
      'default'    => FALSE
    )); 

    $parser->addOption('list', array(
      'short_name' => '-l',
      'long_name'  => '--list',
      'description'=> 'show defined task list.',
      'help_name'  => 'LIST',
      'action'     => 'StoreTrue',
      'default'    => FALSE
    ));

    $parser->addOption('help', array(
      'short_name' => '-h',
      'long_name'  => '--help',
      'description'=> 'show help.',
      'help_name'  => 'HELP',
      'action'     => 'StoreTrue',
      'default'    => FALSE
    ));

    $parser->addArgument('taskname', array(
      'description' => 'taskname want to do.',
      'multiple'    => FALSE,
      'optional'    => FALSE,
      'help_name'   => 'taskName'
    ));

    return $parser;
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

