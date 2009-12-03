<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Writer, ConsoleWriter Function Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
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
   * constractor 
   *
   * @param mixed $output destination where normal messagse put.
   * @param mixed $err_output destination where error message put.
   */
  protected function __construct($output, $err_output){
    $this->level = Writer::$NORMAL;
    $this->output   = $output;
    $this->err_output = $err_output;
  }

  /**
   * set writer level.
   *
   * @param integer $level Writer class constants.
   */
  public function set_level($level){
    $this->level = $level;
  }

  /**
   * getter method for Writer Instance.
   *
   * @abstract
   * @return Writer Instance.
   */
  abstract public static function getInstance();

  //----------------------------------------
  /**  
   * write verbose message
   *
   * @param string $str verbose message.
   */
  public function verbose($str){ 
    $this->output_msg(Writer::$VERBOSE, $this->format($str));
  }

  /**  
   * write debug message
   *
   * @param string $str debug message.
   */
  public function debug($str){
    $this->output_msg(Writer::$DEBUG, $this->debug_format($str));
  }

  /**  
   * write normal message
   *
   * @param string $str message
   */
  public function puts($str){ 
    $this->output_msg(Writer::$NORMAL, $this->format($str));
  }

  /**
   * write error message
   *
   * @param string $str error message.
   */
  public function error($str){ 
    fprintf($this->err_output, $this->error_format($str) . "\n");
  } 


  /**
   * puts message if write level was higher than target level.
   *
   * @access protected
   * @param integer $target_level
   * @param string  $msg
   */
  protected function output_msg($target_level, $msg){
    if($this->level >= $target_level){
      fprintf($this->output, $msg . "\n");
    }
  }

  //----------------------------------------
  /**
   * format output string for Writer::Debug Level.
   *
   * @access protected
   * @param string $str debug message
   * @return formatted debug message
   */
  protected function debug_format($str){
    return "DEBUG: " . $str;
  }

  /**
   * format output string.
   *
   * @access protected
   * @param string $str normal message
   * @return formatted normal message
   */
  protected function format($str){
    return "*** " . $str;
  }

  /**
   * format error output string.
   *
   * @access protected
   * @param string $str error message
   * @return formatted error message
   */
  protected function error_format($str){
    return "ERROR: " . $str;
  } 
}


/**
 * Console Output Writer Class
 */
class ConsoleWriter extends Writer{

  /** instance object */
  private static $instance = null;

  /**
   * Get ConsoleWriter Instance
   * (Write output message to STDOUT/STDERR)
   *
   * @return ConsoleWriter Instance.
   */
  public static function getInstance(){
    if(ConsoleWriter::$instance == null){
      ConsoleWriter::$instance = new ConsoleWriter(STDOUT, STDERR);
      ConsoleWriter::$instance->set_level(Writer::$NORMAL);
    } 
    return ConsoleWriter::$instance;
  } 

} 

/**
 * TaskList ConsoleWriter Class
 *
 * custom class for output tasklist message.(-t option)
 */
class TaskListWriter extends ConsoleWriter{
  /** class instance */
  private static $instance = null;

  /**
   * instance getter factory method
   *
   * @return TaskListWriter Instance
   */
  public static function getInstance(){
    if(TaskListWriter::$instance == null){
      TaskListWriter::$instance = new TaskListWriter(STDOUT, STDERR); 
      TaskListWriter::$instance->level = Writer::$NORMAL;
    }
    return TaskListWriter::$instance;
  }

  /**  
   * format and output task list message.
   *
   * @param array $ary ( ('name'=>$task_name, 'desc'=>$desc),....)
   * @param string $pask_fpath paskfile directory.
   */
  public function puts_tasks($ary, $pask_fpath){ 
    $out = array( "(in ". $pask_fpath .")" );

    // Create task output format.
    $longest_size = $this->get_longest_taskname_length($ary); 
    $format_string = "% -".$longest_size."s # %s";

    $ordered_ary = $this->sort_output_tasks($ary);

    foreach($ordered_ary as $task_data){
      array_push($out, 
        sprintf($format_string, $task_data['name'],$task_data['desc'])
      ); 
    } 

    // output each lines.
    foreach($out as $line){
      $this->output_msg(Writer::$NORMAL, $line);
    } 
  }


  /**
   * get longest task_name
   *
   * @access private
   * @param array $ary
   * @return longest task_name length 
   */
  private function get_longest_taskname_length($ary){
    $task_names = $this->get_tasknames($ary);

    usort($task_names, create_function('$str1, $str2','
        $length_str1 = mb_strlen($str1);
        $length_str2 = mb_strlen($str2);

        if($length_str1 > $length_str2){
          return 1;
        }else if($length_str1 == $length_str2){
          return 0;
        }else{
          return -1;
        } 
      ')
    );

    return mb_strlen(array_pop($task_names));
  }

  /**
   * Get task_name array
   *
   * @access private
   * @param array $ary
   * @return task_name strings array
   */
  private function get_tasknames($ary){
    $ret = array();
    foreach($ary as $task){
      array_push($ret, $task['name']);
    }
    return $ret;
  }

  /**
   * sort tasks in alphabetical order
   *
   * @access private
   * @param array $ary
   * @return sorted task object array
   */
  private function sort_output_tasks($ary){
    $target_ary = $ary;

    usort($target_ary, 
      create_function('$task_data1, $task_data2','
        return strnatcmp($task_data1["name"], $task_data2["name"]);
      ')
    );

    return $target_ary; 
  }

}


?>
