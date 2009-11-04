<?php

require_once("errors.php");
require_once("pask.php");

/**
 * Paskfile Loader Class
 */
class PaskLoader{

  /** output writer */
  private $writer = null;

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
  public function __construct($path, $writer){
    $this->writer = $writer;

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
            $fpath = $file->getPathname();;

            array_push($this->paskfile_map(), 
              array($this->get_taskname($fpath) => $fpath)
            );
          }

          $writer->debug("Load: " . $file->getPathname());
        }else{
          $writer->debug("skip: " . $fname);
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

    if(count($before_tasks) > 0){
      resolve_dependency($before_tasks);
    }

    $this->push_task($task_data);

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
      throw new TaskNotFoundError();
    }

    // FIXME issue 2 (namespace problem)
    //   this spec may be redefine(or error occur?)  TaskClass 
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
    $task_name = basename($file_path, ".pask.php"); 

    $dir_path = dirname($file_path);

    // TODO delete first root_dir path from file_path
    // TODO change dir separator with ":" (if "/" or "", namespace is "")

    return $namespace . ":" . $task_name;
  }


  /**
   * get ClassName from namespaced task_name.
   *
   * @param string $task_name namespaced task_name
   * @return ClassName
   */
  private function get_classname($task_name){
    $splited_taskname = preg_split(':',$task_name); 
    return array_pop($splited_taskname);
  }

  /**
   * get namespace from namespaced task_name.
   *
   * @param string $task_name namespaced task_name
   * @return namespace(separated by ':') or ""(root namespace)
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
