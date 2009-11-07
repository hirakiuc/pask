<?php 
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Task Runner Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

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

  /** output writer */
  private $writer = null;

  /** map taskname with filepath */
  private $paskfile_map = null;

  /**
   * Constractor
   */
  public function __construct($loader, $conf, $writer){ 
    $this->loader = $loader;
    $this->conf = $conf;
    $this->writer = $writer; 

    $this->paskfile_map = $loader->get_paskfile_map();
  }

  /**
   * Do task 
   */
  public function run_task($task_name, $task_args){ 
    // split $task_name to namespace and camelCasedTaskName
//    $ary = Utils::parse_taskname($task_name);

    $stack = $this->loader->create_taskstack($task_name); 

    $this->writer->puts("Run    '" . $task_name . "' task.");
    $total_start_time = Utils::get_time();

    while(count($stack)!=0){
      try{ 
        $task_data = array_pop($stack); 

        $this->writer->verbose("Start '".$task_data['task_name']);
        $start_time = Utils::get_time();

        $task_data['pask']->run();

        $process_time = Utils::get_process_time($start_time);
        $this->writer->verbose(
          "End   '".$task_data['task_name']."(".$process_time."[msec])");

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
