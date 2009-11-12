<?php
/**
 *
 */

require_once(dirname(__FILE__)."/../lib/include.php");
require_once(dirname(__FILE__)."/../lib/exts/dir.php");

class DirDeleteTask extends Pask{

  public $desc = "Directory delete Task.";

  public $before_tasks = array("dir_create_task");

  public function run(){
    // full path 
    DirUtil::delete("/tmp/hogehoge");

    // relative path
    DirUtil::delete("../hoge");
  }
} 
?>
