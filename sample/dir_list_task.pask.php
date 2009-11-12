<?php
/**
 *
 */

require_once(dirname(__FILE__)."/../lib/include.php");
require_once(dirname(__FILE__)."/../lib/exts/dir.php");

class DirListTask extends Pask{

  public $desc = "Directory Listing Task.";

  public $before_tasks = array();

  public function run(){
    $ary = DirUtil::lists("../lib");
    print_r($ary);
  }
} 
?>
