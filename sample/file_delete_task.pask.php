<?php
/**
 *
 */

require_once(dirname(__FILE__)."/../lib/include.php");
require_once(dirname(__FILE__)."/../lib/exts/file.php");

class FileDeleteTask extends Pask{

  public $desc = "File delete Task.";

  public $before_tasks = array("file_create_task");

  public function run(){
    // full path 
    FileUtil::delete("/tmp/hogehoge");

    // relative path
    FileUtil::delete("../hoge");
  }
} 
?>
