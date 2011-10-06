<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * PaskHolder Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

class PaskHolder
{
  private static $_self = null;

  private $_predefined_desc = null;

  /** 
   * _holder = array(
   *  'taskname' => array(
   *    "desc" => "description",
   *    "name" => "taskname",
   *    "depend" => array("taskname",...),
   *    "callback" => callback function
   *   )
   * )
   */
  private $_holder = null;

  private $_stack = null;

  private function __construct()
  {
    $this->_holder = array();
    $this->_stack = array();
  }

  public function getInstance()
  {
    if (PaskHolder::$_self == null) {
      PaskHolder::$_self = new PaskHolder();
    } 
    return PaskHolder::$_self; 
  }

  /**
   * add task description.
   *
   * @param string $desc task description
   */
  public function add_desc($desc)
  {
    if ($this->_predefined_desc != null) {
      echo "warn: description defined but not used.(" . 
        $this->_predefined_desc . PHP_EOL;
    }

    if (!is_string($desc)) {
      throw new Exception("invalid argument.");
    }

    $this->_predefined_desc = $desc;
  }

  /**
   * add task.
   *
   * @param string   $name taskname or taskname and task dependency.
   * @param array    $depend_tasks tasks depend on this task.
   * @param function $callback task callback function
   */
  public function add_task($name, $depend_tasks, $callback)
  {
    $_task = array();

    if ($this->_predefined_desc != null) {
      $_task['desc'] = $this->_predefined_desc;
      $this->_predefined_desc = null;
    } else {
      $_task['desc'] = null;
    }
    
    $_task['name'] = $name;
    $_task['depend'] = $depend_tasks;
    $_task['callback'] = $callback;

    if (in_array($_task['name'], array_keys($this->_holder))) {
      throw new Exception("already defined taskname:".$_task['name']); 
    }

    $this->_holder[$_task['name']] = $_task;
  } 

  /**
   * check task dependency relation.
   */
  public function check_dependency()
  {
    $tasknames = array_keys($this->_holder);
    foreach($tasknames as $taskname){
      $depend_tasks = $this->_holder[$taskname]['depend'];

      foreach($depend_tasks as $depend_task){
        if (!in_array($depend_task, $tasknames)) {
          throw new TaskDepencyError("'".$taskname."' depends on '".$depend_task."', but '".$depend_task."' not defined.");
        }
      }
    }
  }

  /**
   * return all tasks.
   *
   * @return tasks array sorted by key.
   */
  public function get_all_tasks()
  {
    ksort($this->_holder);
    return $this->_holder;
  }

  /**
   *
   */
  public function create_taskstack($taskname)
  {
    $this->_stack = array();

    $tree = $this->build_tasktree($taskname);

    $ary = $tree->to_a();

    $ret = array();
    foreach($ary as $v){
      array_push($ret, $this->_holder[$v]);
    }
    return $ret;
  }

  private function check_task_exist($taskname)
  {
    $tasknames = array_keys($this->_holder);
    if(!in_array($taskname, $tasknames)){
      throw new TaskNotFoundError("task not found:" . $taskname);
    }
  }

  private function build_tasktree($taskname)
  {
    $root = new TaskNode($taskname);

    $task = $this->_holder[$taskname];
    if (count($task['depend']) > 0) {
      $this->add_dependon_tasknode($root);
    }

    return $root;
  }

  /**
   *
   */
  private function add_dependon_tasknode($node)
  { 
    $depend_tasknames = $this->_holder[$node->name]['depend'];
    foreach($depend_tasknames as $taskname){
      $task = $this->_holder[$taskname];
      $new_node = new TaskNode($taskname);
      $node->add_child($new_node);

      if (count($task['depend']) > 0) {
        $this->add_dependon_tasknode($new_node);
      }
    } 
  }

  private function resolve_dependency($taskname)
  {
    
  } 
}


class TaskNode 
{
  public $parent = null;
  public $name = null;
  public $children = null;

  /**
   *
   */
  public function __construct($name)
  {
    $this->parent = null;
    $this->name = $name;
    $this->children = array();
  }

  /**
   *
   */
  public function add_child($child_node)
  {
    if ($this->check_samename_parent($this, $child_node->name)) {
      throw new TaskDependencyError("recursive task dependency:" . $child_node->name);
    }

    $child_node->parent = $this;
    array_push($this->children, $child_node);
  }

  /**
   *
   */
  private function check_samename_parent($node, $name)
  {
    if ($node->name == $name) {
      return true;
    }

    if (!is_null($node->parent)) {
      return $this->check_samename_parent($node->parent, $name);
    } else {
      return false;
    }
  }

  /**
   *
   */
  public function to_a(){
    return TaskNode::to_a_children($this); 
  }

  /**
   *
   */
  private static function to_a_children($node) {
    if (count($node->children) == 0) {
      echo "childnode:".$node->name."\n";
      return array($node->name);
    }

    $ret = array();
    foreach($node->children as $childnode){
      $ret = array_merge($ret, TaskNode::to_a_children($childnode));
    }

    array_push($ret, $node->name);

    return $ret;
  }
}


?>
