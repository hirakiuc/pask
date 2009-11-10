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
   *
   */
  protected function __construct($output, $err_output){
    $this->level = Writer::$NORMAL;
    $this->output   = $output;
    $this->err_output = $err_output;
  }

  /**
   *
   */
  public function set_level($level){
    $this->level = $level;
  }

  /**
   *
   */
  abstract public static function getInstance();

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
    fprintf($this->err_output, $this->error_format($str) . "\n");
  } 


  /**
   *
   */
  protected function output_msg($target_level, $msg){
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
 */
class TaskListWriter extends ConsoleWriter{
  /** this class value */
  private static $instance = null;

  /**
   * factory method
   */
  public static function getInstance(){
    if(TaskListWriter::$instance == null){
      TaskListWriter::$instance = new TaskListWriter(STDOUT, STDERR); 
      TaskListWriter::$instance->level = Writer::$NORMAL;
    }
    return TaskListWriter::$instance;
  }

  /**  
   * output task list 
   *
   * @param array $ary ( ('name'=>$task_name, 'desc'=>$desc),....)
   */
  public function puts_tasks($ary, $pask_fpath){ 
    $out = array( "(in ". $pask_fpath .")" );

    // Create task output format.
    $longest_size = $this->get_longest_taskname_length($ary); 
    $format_string = "% -".$longest_size."s # %s";

    // sort by strnatcmp in tasknames.
    $ordered_ary = $this->sort_output_tasks($ary);

    // push output array
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
   *  get longest task_name
   */
  private function get_longest_taskname_length($ary){
    $task_names = $this->get_tasknames($ary);

    // TODO if usort return false
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
   *
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
   */
  private function sort_output_tasks($ary){
    $target_ary = $ary;

    // TODO if usort return false
    usort($target_ary, 
      create_function('$task_data1, $task_data2','
        return strnatcmp($task_data1["name"], $task_data2["name"]);
      ')
    );

    return $target_ary; 
  }

}


?>
