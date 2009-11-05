<?php
/**
 *
 */

/**
 * Abstract Message Writer Class
 */
abstract class Writer{
  //--------------------------------- 
  //error >> debug > verbose > normal > quiet 
  /** level:error */
  private static $ERROR  = 100; 
  /** level:debug (for developer) */
  public static $DEBUG   = 4; 
  /** level:verbose */
  public static $VERBOSE = 3;
  /** level:normal */
  public static $NORMAL  = 2;
  /** level:quiet */
  public static $QUIET   = 1; 
  //---------------------------------

  /** output level */
  protected $level = null;

  /** output destination (level:verbose,normal,debug) */
  protected $output = null;

  /** output destination (if error occured) */
  protected $err_output = null;

  /**
   *
   */
  protected function __construct($output, $err_output, $level){
    $this->level = $level;
    $this->output   = $output;
    $this->err_output = $err_output;
  }

  /**
   *
   */
  abstract public static function getInstance($conf);

  //----------------------------------------
  /**  
   *
   */
  public function verbose($str){ 
    $this->output_msg(Writer::$VERBOSE, $this->format($str));
  }

  /**  
   *
   */
  public function debug($str){
    $this->output_msg(Writer::$DEBUG, $this->debug_format($str));
  }

  /**  
   *
   */
  public function puts($str){ 
    $this->output_msg(Writer::$NORMAL, $this->format($str));
  }

  /**
   *
   */
  public function error($str){ 
    $this->output_msg(Writer::$ERROR, $this->error_format($str));
  } 


  /**
   *
   */
  private function output_msg($target_level, $msg){
    if($this->level >= $target_level){
      fprintf($this->output, $msg . "\n");
    }
  }

  //----------------------------------------
  /**
   * format the output string in Writer::Debug Level.
   */
  protected function debug_format($str){
    return "DEBUG: " . $str;
  }

  /**
   * format the output string.
   *
   * @param $str formated string.
   */
  protected function format($str){
    return "*** " . $str;
  }

  /**
   * format the error output string.
   *
   * @param $str formated string.
   */
  protected function error_format($str){
    return "ERROR: " . $str;
  } 
}


/**
 * Console Output Writer Class
 */
class ConsoleWriter extends Writer{

  /**  */
  private static $instance = null;

  /**
   * Get ConsoleWriter Instance
   *
   * @param array $conf array('level' => Writer::XXXX)
   * @return ConsoleWriter Instance.
   */
  public static function getInstance($level){
    if(ConsoleWriter::$instance == null){
      ConsoleWriter::$instance = new ConsoleWriter(STDOUT, STDERR, $level);
    } 
    return ConsoleWriter::$instance;
  } 
} 
?>
