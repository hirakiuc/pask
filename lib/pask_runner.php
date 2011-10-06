<?php 
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Pask Runner Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

require_once('pask_holder.php');

/**
 * PaskRunner for paskfile.
 */
class PaskRunner{

  /** system config */
  private $conf = null;

  /** output writer */
  private $writer = null;

  /**
   * Constractor
   *
   * @param mixed $conf 
   * @param Writer Instance $writer
   */
  public function __construct($conf, $writer){ 
    $this->conf = $conf;
    $this->writer = $writer; 
  }

  /**
   * Do specified task 
   *
   * @param string $task_name 
   * @param mixed $task_args
   */
  public function run_task($task_name, $task_args){ 
    try{
      $holder = PaskHolder::getInstance();
      $stack = $holder->create_taskstack($task_name); 
    }catch(Exception $err){
      throw $err;
    }

    $this->writer->puts("Run    '" . $task_name . "' task.");
    $total_start_time = Utils::get_time();

    while(count($stack)!=0){
      try{ 
        $task_data = array_shift($stack); 

        $this->writer->verbose("Start '".$task_data['name']);
        $start_time = Utils::get_time();

        $task_data['callback']();

        $process_time = Utils::get_process_time($start_time);
        $this->writer->verbose(
          "End   '".$task_data['name']."(".$process_time."[msec])");

      }catch(Exception $err){
        throw $err;
      }
    } 

    $total_process_time = Utils::get_process_time($total_start_time);
    $this->writer->puts(
      "Finish '".$task_name."' task.(".$total_process_time."[msec])");
  } 
} 
?>
