<?php

require_once("errors.php");
require_once("pask.php");

/**
 * Paskfile Loader Class
 */
class PaskLoader{

  /** regular expression for paskfile extention */
  private $task_ext_regexp = "*\.task\.php$";

  /** target directory to search some paskfiles. */
  private $root_dir_path = null;

  /** 
   * create to search the task file.
   *
   * task_name is a CamelCasedName string 
   * with namespace separated by ':'
   *
   * [ [task_name => filepath], ... ]
   */
  private $paskfile_map = array();

  /** 
   * This array is used by TaskRunner Class as Stack.
   *
   * [
   *   [ 
   *     task_name => $camelCasedName , 
   *     pask      => $obj, 
   *     namespace => $str
   *   ],
   *    ....
   * ] 
   */
  private $task_stack = array();

  /**
   * Constractor
   */
  public function __construct($path){
    // check path is not null or zero length string.
    if($target_dir == null || mbstrlen($target_dir) == 0){
      throw new ArgumentError();
    }

    // check whether path is a dir?
    if(!file_exists($path)){
      throw new NotFoundError();
    }else if(!is_dir($path)){
      throw new NotDirectoryError();
    }

    $this->root_dir_path = $path;

    try{
      $root_dir = new DirectoryIterator($path);

      $this->search_paskfiles($root_dir); 

    }catch(Exception $err){
      throw $err;
    }

    $this->load_tasks($target_dir);
  }  

  /**
   * Search paskfile in the taskdir recursively.
   */
  private function search_paskfiles($target_dir){
    try{
      foreach($target_dir as $file){
        $fname = $file.getFilename();

        if($file.isDir() && !$file.isDot()){
          $this->search_paskfiles($file);

        }else if($file.is_file()){
          if(mb_ereg_match($this->task_ext_regexp,$fname)){
            $fpath = $file->getPathname;

            array_push($this->paskfile_map, 
              array($this->get_taskname($fpath) => $fpath)
            );
          }

        }else{
          // TODO logger.debug("skip: " . $fname);
        }
      }
    }catch(Exception $err){
      throw $err;
    } 
  }

  /**
   * create camel cased taskname separated by ':'.
   */
  private function get_taskname($file_path){
    // TODO implement in Util function?
    return "";
  }

  //--------------------------------------------
  // pask stack
  /**
   *
   */
  public function create_taskstack($task_name){

    $task_data = $this->load_paskfile($task_name);
    $before_tasks = $task_data['pask']->before_tasks;

    if(count($before_tasks) > 0){
      resolve_dependency($before_tasks);
    }

    $this->push_task($task_data);

    return $this->task_stack;
  }

  /**
   *
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
   *
   */
  private function load_paskfile($task_name){
    // resolve paskfile path from task_name.
    $fpath = $this->paskfile_map[$task_name];
    if($fpath == null){
      throw new TaskNotFoundError();
    }

    $namespace = $this->get_namespace($task_name);

    // TODO BUG 
    //   this spec may be override TaskClass 
    //   if other same TaskNameClass exist in other namespace.
    //   (should this use php namespace in 5.3 ??) 
    include($fpath);

    $task_classname = $this->get_classname($task_name);
    if(!class_define($task_classname)){
      throw new TaskNotFoundError();
    }else if(!ReflectionClass::isSubclassOf("Pake")){
      throw new InvalidTaskClassError();
    }

    // 1. ReflectionClass::newInstance($task_classname)
    $obj = null;
    try{
      $ref = new ReflectionClass($task_classname);
      $obj = $ref->newInstance(); 
    }catch(Exception $err){
      throw $err;
    }

    return array(
      'task_name' => $task_name, 
      'pask' => $obj,
      'namespace' => $this->get_namespace($task_name)
    );
  }

  //--------------------------------------------
  /**
   * get task and desc array.
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
   *
   */
  public function get_paskfile_map(){
    return $this->paskfile_map;
  }

  //--------------------------------------------
  // Util functions
  /**
   *
   */
  private function get_classname($task_name){
    $splited_taskname = preg_split(':',$task_name); 
    return array_pop($splited_taskname);
  }

  /**
   *
   */
  private function get_namespace($task_name){
    $splited_taskname = preg_split(':',$task_name);
    array_pop($splited_taskname);
    if(count($splited_taskname) == 0){
      return "";
    }else{
      return explode(':',$splited_taskname);
    }
  }

  /**
   *
   */
  private function push_task($array){
    array_push($this->task_stack, $array);
  }

  /**
   *
   */
  public function pop_task(){
    array_pop($this->task_stack);
  }

} 

?>

