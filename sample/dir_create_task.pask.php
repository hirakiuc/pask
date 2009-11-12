<?php
/**
 *
 */

require_once(dirname(__FILE__)."/../lib/include.php");
require_once(dirname(__FILE__)."/../lib/exts/dir.php");

class DirCreateTask extends Pask{

  public $desc = "Directory Create Task.";

  public $before_tasks = array();

  public function run(){
    // full path 
    DirUtil::create("/tmp/hogehoge");

    // relative path
    DirUtil::create("../hoge"); 
  }
} 
?>
