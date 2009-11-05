<?php

/**
 * Abstract Message Writer Class
 */
abstract class Writer{
  //---------------------------------
  /** level:verbose */
  public static $VERBOSE = "verbose";
  /** level:normal */
  public static $NORMAL  = "normal";
  /** level:quiet */
  public static $QUIET   = "quiet"; 
  /** level:debug (for developer) */
  public static $DEBUG   = "debug"; 
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
    $this->puts($str);
  }

  /**  
   *
   */
  public function debug($str){
    $this->puts($str);
  }

  /**  
   *
   */
  public function puts($str){ 
    switch($this->level){
      case Writer::$DEBUG:
        fprintf($this->output, $this->debug_format($str) . "\n");
        return;
      case Writer::$VERBOSE:
      case Writer::$NORMAL:
        fprintf($this->output, $this->format($str) . "\n");
        return;
      case Writer::$QUIET:
      default: 
        return;
    }
  }

  /**
   *
   */
  public function error($str){
    fprintf($this->err_output, $this->error_format($str) . "\n"); 
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
    return $str;
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
