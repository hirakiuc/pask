<?php
/**
 *
 */

require_once(dirname(__FILE__)."/../lib/include.php");
require_once(dirname(__FILE__)."/../lib/exts/file.php");

class FileCreateTask extends Pask{

  public $desc = "File Create Task.";

  public $before_tasks = array();

  public function run(){
    // full path 
    FileUtil::create("/tmp/hogehoge");

    // relative path
    FileUtil::create("../hoge"); 
  }
} 
?>
