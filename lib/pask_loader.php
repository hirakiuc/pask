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

require_once("const.php");
require_once("errors.php");

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
   * Constractor
   *
   * @param object $writer 
   */
  public function __construct($writer){
    $this->writer = $writer; 
  }  

  /**
   * load paskfiles.
   *
   * @param string $paskfile_path
   * @param string $paskdir_path
   */ 
  public function load($paskfile_path, $paskdir_path){

    if($paskfile_path != null && mb_strlen($paskfile_path) != 0) {
      $this->loadfile($paskfile_path);
    }

    // check paskdir_path is not null or zero length string.
    if($paskdir_path == null){
      return;
    }

    if(mb_strlen($paskdir_path) == 0){
      throw new ArgumentError("Invalid Argument : Task Directory Path.");
    }

    // check whether paskdir_path is a dir?
    if(!file_exists($paskdir_path)){
      throw new NotFoundError("Invalid Directory path: " . $paskdir_path);
    }else if(!is_dir($paskdir_path)){
      throw new NotDirectoryError("Invalid Directory path: " . $paskdir_path);
    }

    $this->root_dir_path = $paskdir_path;

    try{
      $root_dir = new DirectoryIterator($paskdir_path);

      $this->search_paskfiles($root_dir); 
    }catch(Exception $err){
      throw $err;
    } 
  }

  /**
   * Search paskfile in the taskdir recursively.
   *
   * @access private
   * @param DirectoryIteratorInstance $dir_iterator
   */
  private function search_paskfiles($dir_iterator){
    try{
      foreach($dir_iterator as $item){
        $name = $item->getFilename();
        $path = $item->getPathname();

        if($item->isDir() && !$item->isDot()){
          $next_dir = new DirectoryIterator($item->getPathname());
          $this->search_paskfiles($next_dir); 
          $this->writer->debug("Search into Directory: " . $path);

        }else if($item->isFile()){
          $this->writer->debug("File found:".$path);
          if($item->isReadable()){
            if(mb_ereg_match($this->task_ext_regexp, $path) || $name == DEFAULT_PASKFILE){ 

              $this->loadfile($path);

              $this->writer->debug("File loaded : " . $path);
            }else{
              $this->writer->debug("File Skipped: " . $path);
            } 
          } else {
            $this->writer->debug("File can't readable... skip.");
          }
        }else{
          $this->writer->debug("File Skipped: " . $path);
        }
      }
    }catch(Exception $err){
      throw $err;
    } 
  } 

  /**
   * load a paskfile.
   *
   * @param string @path filepath
   */
  private function loadfile($path){

    $is_default_file = false;
    if (preg_match("/".DEFAULT_PASKFILE . "$/", $path)) {
      $is_default_file = true;
    }

    if(!file_exists($path)){
      if (!$is_default_file){
        throw new FileReadError("file not found: " . $path);
      } else {
        return;
      }
    }

    if (!is_file($path) || !is_readable($path)){ 
      if (!$is_default_file){ 
        throw new FileReadError("not readable file: " . $path);
      } else {
        return;
      }
    }

    include_once($path);
  }

  /**
   *
   */
  public function show_tasklist(){

    $holder = PaskHolder::getInstance();
    $tasks = $holder->get_all_tasks();

    $descs = array();
    foreach($tasks as $task) {
      array_push($descs, 
        array('name' => $task['name'], 'desc' => $task['desc'])
      );
    }

    $writer = TaskListWriter::getInstance();
    $writer->puts_tasks($descs);
  }
} 

?>
