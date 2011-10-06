<?php
/**
 * delete directory task
 */

desc("Directory delete task");
task("delete_dir", array("create_dir"), function() {
  rmdir("/tmp/hogehoge");
});

?>
