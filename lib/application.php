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
require_once('const.php');

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

  /** PaskLoader instance */
  private $loader = null;

  /**
   * constractor
   */
  public function __construct(){ 
  }

  /**
   * Run Application (call first.)
   */
  public function run(){
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

    $writer = $this->get_writer();

    $loader = new PaskLoader($writer);

    try{ 
      $loader->load($this->conf->options['paskfile'], $this->conf->options['paskdir']);
    }catch(Exception $err){
      $writer->error($err->getMessage());
      exit(Application::$ERROR);
    }
    
    if($this->conf->options['tasks']){
      $loader->show_tasklist();
    }else{
      try{
        $runner = new PaskRunner($this->conf, $writer); 

        $runner->run_task($this->conf->args['taskname'], null);
      }catch(Exception $err){
        $writer->error($err->getMessage());
        exit(Application::$ERROR);
      }
    }

    return 0;
  }

  /**
   *  get writer level (like log level.)
   *
   *  @return Writer level(Writer class constant) from argument. 
   */
  private function get_writer_level(){
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
    return $level;
  }

  /**
   * Create Writer Instance.
   *
   * @return Writer Instance
   */
  private function get_writer(){
    $writer = ConsoleWriter::getInstance();
    $writer->set_level($this->get_writer_level());
    return $writer;
  }

  /**
   * check command arguments combination.
   * throw exception if failed.
   *
   * @throw ArgumentError argument combination was invalid.
   */ 
  private function check_arguments(){
    
    // normalize paskdir path.
    $paskdir = realpath($this->conf->options['paskdir']);
    if(!$paskdir){
      throw new ArgumentError(
        "Directory is not exist: " . $this->conf->options['paskdir']);
    }else{
      $this->conf->options['paskdir'] = $paskdir;
    }

    /* -d : ok(-d,-t,-v,-q,-x,taskname)
     * -t : not(taskname) ok(-d,-v,-q,-x)
     * -v : not(-q,-x)    ok(-d,-t)  must(taskname)
     * -q : not(-v,-x)    ok(-d,-t)  must(taskname)
     * -x : not(-q,-v)    ok(-d,-t)  must(taskname)
     */
    if($this->conf->options['tasks']){
      // do not specify task_name
      if($this->conf->args['taskname'] != null){
        throw new ArgumentError("Can't use task_name when you -t option use.");
      } 
    }else{
      if($this->conf->args['taskname'] == null){ 
        throw new ArgumentError("Must use task_name.");
      } 
    }
    
    $verbose_flag = $this->conf->options['verbose'];
    $quiet_flag   = $this->conf->options['quiet'];
    $debug_flag   = $this->conf->options['debug'];

    if($debug_flag){
      if($quiet_flag || $verbose_flag){
        $this->conf->options['quiet']   = FALSE;
        $this->conf->options['verbose'] = FALSE;
      }
    }

    if($verbose_flag){
      if($quiet_flag){
        // use -v option
        $this->conf->options['quiet'] = FALSE;
      }
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
      'description' => 'Pure PHP Task Management Framework',
      'version'     => '0.1.0' 
    ));

    $parser->addOption('paskdir', array(
      'short_name' => '-d',
      'long_name'  => '--paskdir',
      'description'=> 'specify paskfile directory.',
      'help_name'  => 'PASKDIR',
      'action'     => 'StoreString',
      'default'    => getcwd()
    ));

    $parser->addOption('paskfile', array(
      'short_name' => '-f',
      'long_name'  => '--paskfile',
      'description'=> 'specify paskfile.',
      'help_name'  => 'PASKFILE',
      'action'     => 'StoreString',
      'default'    => getcwd() . PATH_SEPARATOR . DEFAULT_PASKFILE
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
} 
?>
