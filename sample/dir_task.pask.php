<?php
/**
 * directory task
 */

desc("Directory Create task");
task("create_dir", function() {
  mkdir("/tmp/hogehoge");
});

desc("Directory delete task");
task("delete_dir", array("create_dir"), function() {
  rmdir("/tmp/hogehoge");
}); 

?>
