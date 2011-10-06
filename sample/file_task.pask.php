<?php
/**
 * file create task
 */

desc("File create task");
task("create_file", function() {
  touch("/tmp/hogehoge");
});

desc("File delete task");
task("delete_file", array("create_file"), function() {
  unlink("/tmp/hogehoge");
});


?>
