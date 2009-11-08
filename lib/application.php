<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Application Class File
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

require_once('Console/CommandLine.php');

/**
 * The Class for Simple use for Command Line Interface
 *
 * - check and analyze arguments.
 * - call PaskRunner/Loader if need.
 */
class Application{

  /** application exitcode if succeeded. */
  private static $SUCCESS = 0;
  /** application exitcode if error occured. */
  private static $ERROR   = 1;

/**
 * -d : user defined paskfile directory path.
 * -t : show defined tasks in paskfiles.
 * -v : vorbose mode.
 * -q : quiet mode.
 * -x : debug mode.
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
      exit(Application::$SUCCESS);
    }

    try{
      $this->check_arguments();
    }catch(Exception $err){
      $writer = $this->get_writer();
      $writer->error($err->getMessage());
      exit(Application::$ERROR);
    }

    // create Loader and Runner instance
    $writer = $this->get_writer();

    try{ 
      $this->loader = new PaskLoader($this->conf->options['paskdir'], $writer); 
    }catch(Exception $err){
      // error occured  searching paskfile
      $writer->error($err->getMessage());
      exit(Application::$ERROR);
    }
    
    $this->runner = new PaskRunner($this->loader, $this->conf, $writer); 

    if($this->conf->options['tasks']){
       $this->show_tasklist($writer);
    }else{
      try{
        // TODO can't use task argument now...
        $this->run_task($this->conf->args['taskname'] ,null);
      }catch(Exception $err){
        $writer->error($err->getMessage());
        exit(Application::$ERROR);
      }
    }

    return 0;
  }

  /**
   * Create Writer Instance.
   *
   * @return Writer Instance
   */
  private function get_writer(){
    // define writer level
    $level = Writer::$NORMAL;

    if($this->conf == null){
      $level = Writer::$NORMAL;
    }else if($this->conf->options['quiet']){
      $level = Writer::$QUIET;
    }else if($this->conf->options['verbose']){
      $level = Writer::$VERBOSE;
    }else if($this->conf->options['debug']){
      $level = Writer::$DEBUG;
    } 

    // create and return writer
    return ConsoleWriter::getInstance($level); 
  }

  /**
   * check command arguments combination.
   */ 
  private function check_arguments(){
    // TODO implement (throw Exception if error
    
    /* -d : ok(-d,-t,-v,-q,-x,taskname)
     * -t : not(taskname) ok(-d,-v,-q,-x)
     * -v : not(-q,-x)    ok(-d,-t)  must(taskname)
     * -q : not(-v,-x)    ok(-d,-t)  must(taskname)
     * -x : not(-q,-v)    ok(-d,-t)  must(taskname)
     */



    $paskdir = realpath($this->conf->options['paskdir']);
    if(!$paskdir){
      throw new ArgumentError(
        "Directory is not exist: " . $this->conf->options['paskdir']);
    }else{
      $this->conf->options['paskdir'] = $paskdir;
    }

    return;
  }
  
  /**
   * Create Console_CommandLine Parser Object.
   *
   * @return Console_CommandLine Object
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

    $parser->addOption('tasks', array(
      'short_name' => '-t',
      'long_name'  => '--tasks',
      'description'=> 'show defined task list.',
      'help_name'  => 'TASKS',
      'action'     => 'StoreTrue',
      'default'    => FALSE
    ));

    $parser->addArgument('taskname', array(
      'description' => 'taskname want to do.',
      'multiple'    => FALSE,
      'optional'    => TRUE,
      'help_name'   => 'taskName'
    ));

    //----- for developer mode ----------
    $parser->addOption('debug', array(
      'short_name' => '-x',
      'long_name'  => '--debug',
      'description'=> 'show debug information.',
      'help_name'  => 'DEBUG',
      'action'     => 'StoreTrue',
      'default'    => FALSE
    ));
    //-----------------------------------

    return $parser;
  } 

  //------------------------------------------
  /**
   * Run task.
   */
  private function run_task($task_name, $task_args){
    try{ 
      $this->runner->run_task($task_name, $task_args);
    }catch(Exception $err){
      throw $err;
    }
  }

  //------------------------------------------
  /**
   * show defined tasks.
   */
  private function show_tasklist($writer){
    $ary = $this->loader->get_tasks_desc();

    // TODO refine output string...
    foreach($ary as $task){
      $writer->puts($task['name'] . ' : ' . $task['desc']); 
    };
  }

} 
?>
