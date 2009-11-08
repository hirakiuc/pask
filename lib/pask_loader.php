<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Paskfile Loader Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

require_once("utils.php");
require_once("errors.php");
require_once("pask.php");

/**
 * Paskfile Loader Class
 */
class PaskLoader{

  /** output writer */
  private $writer = null;

  /** regular expression for the paskfile extention */
  private $task_ext_regexp = ".*\.pask\.php$";

  /** target directory to search some paskfiles. */
  private $root_dir_path = null;

  /** 
   * create to search the task file.
   *
   * task_name is a CamelCasedName string 
   * with namespace separated by ':'
   *
   * ( (task_name => filepath), ... )
   */
  private $paskfile_map = array();

  /** 
   * This array is used by TaskRunner Class as Stack.
   *
   * (
   *   ( 
   *     task_name => $camelCasedName , 
   *     pask      => $obj, 
   *     namespace => $str
   *   ),
   *    ....
   * ) 
   */
  private $task_stack = array();

  /**
   * Constractor
   */
  public function __construct($taskdir_path, $writer){
    $this->writer = $writer;

    // check taskdir_path is not null or zero length string.
    if($taskdir_path == null || mb_strlen($taskdir_path) == 0){
      throw new ArgumentError("Invalid Argument : Task Directory Path.");
    }

    // check whether taskdir_path is a dir?
    if(!file_exists($taskdir_path)){
      throw new NotFoundError("Invalid Directory path: " . $taskdir_path);
    }else if(!is_dir($taskdir_path)){
      throw new NotDirectoryError("Invalid Directory path: " . $taskdir_path);
    }

    $this->root_dir_path = $taskdir_path;

    try{
      $root_dir = new DirectoryIterator($taskdir_path);

      $this->search_paskfiles($root_dir); 

    }catch(Exception $err){
      throw $err;
    } 
  }  

  /**
   * Search paskfile in the taskdir recursively.
   */
  private function search_paskfiles($dir_iterator){
    try{
      foreach($dir_iterator as $file){
        $fname = $file->getFilename();
        $fpath = $file->getPathname();

        if($file->isDir() || $file->isDot()){
          /* 
            $next_dir = new DirectoryIterator($file->getPathname());
            $this->search_paskfiles($next_dir); 
           */
          $this->writer->debug("Dir Skipped : " . $fpath);

        }else if($file->isFile()){
          $this->writer->debug("File found.");
          if(mb_ereg_match($this->task_ext_regexp,$fpath)){

            $taskname = $this->get_taskname($fpath); 
            $this->paskfile_map[$taskname] = $fpath; 

            $this->writer->debug("File loaded : " . $fpath);
          }else{
            $this->writer->debug("File Skipped: " . $fpath);
          }

        }else{
          $this->writer->debug("File Skipped: " . $fpath);
        }
      }
    }catch(Exception $err){
      throw $err;
    } 
  }


  //--------------------------------------------
  // pask stack
  /**
   * Create TaskStack in this instance.
   *  
   * @param string $task_name namespaced task_name
   */
  public function create_taskstack($task_name){

    $task_data = $this->load_paskfile($task_name); 

    $before_tasks = $task_data['pask']->before_tasks;

    if($before_tasks != null && count($before_tasks) > 0){
      resolve_dependency($before_tasks);
    }

    $this->push_task($task_data);

    // need to reverse taskarray. (because of sorting order by task order.)
    $this->task_stack = array_reverse($this->task_stack);

    return $this->task_stack;
  }

  /**
   * Check whether specified task depends on any other tasks.
   * if such tasks exists, put in task stack.
   *
   * @param array a array with task_name strings
   */ 
  private function resolve_dependency($before_tasks){ 
    foreach($before_tasks as $task){
      $task_data = $this->load_paskfile($task_name);
      $before_tasks = $task_data['pask']->before_tasks;

      if(count($before_tasks) > 0){
        $this->resolve_dependency($before_tasks);
      }else{ 
        $this->push_task($task_data);
      }
    }
  }

  /**
   * Load a Specified paskfile by namespaced task_name, 
   * and return a array for task stask.
   *
   * @task_name string namespaced task_name
   * @return a array for task stask.
   */
  private function load_paskfile($task_name){ 
    // resolve paskfile path from task_name.
    $fpath = $this->paskfile_map[$task_name];
    if($fpath == null){
      throw new TaskNotFoundError("target taskfile is not exist: " . $fpath);
    }

    // FIXME issue 2 (namespace problem)
    //   this spec may be redefine(or error occur?)  TaskClass 
    //   if other same TaskNameClass exist in other namespace.
    //   (should this use php namespace in 5.3 ??) 
    include_once($fpath);

    $obj = null;
    try{
      $task_classname = $this->get_classname($task_name);
      if(!class_exists($task_classname)){
        throw new TaskNotFoundError("target task is not defined: " . $task_name);
      }

      $ref = new ReflectionClass($task_classname);
      if(!$ref->isSubclassOf("Pask")){
        throw new InvalidTaskClassError(
          $task_classname . " class is not a SubClass of the Pake Class.");
      }

      $obj = $ref->newInstance(); 

      $this->check_taskclass_values($task_classname, $obj);

    }catch(Exception $err){
      throw $err;
    }

    return array(
      'task_name' => $task_name, 
      'pask' => $obj,
      'namespace' => $this->get_namespace($task_name)
    );
  }

  /**
   *
   */
  private function check_taskclass_values($task_classname, $obj){
    // desc variable type check
    if(!is_string($obj->desc)){
      throw new InvalidTaskClassError(
        $task_classname . " class must have a 'desc' string instance value.");
    }

    if($obj->before_tasks != null){
      if(!is_array($obj->before_tasks)){
        throw new InvalidTaskClassError(
          $task_classname
          ." class must have a 'before_tasks' array instance value."
        );
      }
      
      $keys = array_keys($obj->before_tasks);
      foreach($keys as $key){
        if(!is_int($key)){
          throw new InvalidTaskClassError(
            "'before_tasks' in the "
            .$task_classname 
            ." class instance must a simple strings array."
          );
        }
      }

      $values = array_values($obj->before_tasks);
      foreach($values as $value){
        if(!is_string($value)){
          throw new InvalidTaskClassError(
            "'before_tasks' in the "
            .$task_classname 
            ." class instance must a simple strings array."
          );
        }
      } 
    } 
  }

  //--------------------------------------------
  /**
   * load all defined tasks and create a array.
   * 
   * @return array which has all task's name and desc.
   */
  public function get_tasks_desc(){
    // [ { 'task_name' => $taskname, 'desc' => $desc'}, ....] 
    $defined_tasks = array(); 

    foreach($this->paskfile_map as $task){ 
      try{
        $task_data = $this->load_paskfile($fpath); 

        array_push($defined_tasks, array( 
          'name' => $task_data['task_name'],
          'desc' => $task_data['pask']->desc
        ));
        
      }catch(Exception $err){
        throw $err;
      } 
    }
    return $defined_tasks;
  }

  //--------------------------------------------
  /**
   * get a array mapped task_name and paskfile path.
   */
  public function get_paskfile_map(){
    return $this->paskfile_map;
  }

  //--------------------------------------------
  // Util functions
  //

  /**
   * create namespaced task_name from paskfile path.
   *
   * @param string $file_path paskfile fullpath
   * @return namespaced taskname string
   */
  private function get_taskname($file_path){
    $paskfile_name = basename($file_path, ".pask.php"); 

    // root namespace
    $namespace = "";

    // delete paskdir path prefix from path.
    $path_from_rootdir = substr($fiel_path, mb_strlen($this->root_dir_path));

    if(mb_strlen($path_from_rootdir) != 0){ 
      // replace directory separator by ':'
      $namespace = preg_replace("/\//",":",substr($path_from_root_dir,1)); 
    } 

    if(mb_strlen($namespace) == 0){
      return $paskfile_name;
    }else{
      return $namespace . ":" . $paskfile_name;
    }
  }


  /**
   * get ClassName from namespaced task_name.
   *
   * @param string $task_name namespaced task_name
   * @return ClassName
   */
  private function get_classname($task_name){ 
    $ary = Utils::parse_taskname($task_name);
    return $ary['className'];
  }

  /**
   * get namespace from namespaced task_name.
   *
   * @param string $task_name namespaced task_name
   * @return namespace(separated by ':') or ""(root namespace)
   */
  private function get_namespace($task_name){
    $ary = Utils::parse_taskname($task_name);
    return $ary['namespace']; 
  }

  //--------------------------------------------
  /**
   * push a specified array object into the task stack.
   *
   * @param array a array contain the task stack object.
   */
  private function push_task($task_data){
    array_push($this->task_stack, $task_data);
  }

  /**
   * pop the next task stack object from task stack.
   */
  public function pop_task(){
    array_pop($this->task_stack);
  }

} 
?>
