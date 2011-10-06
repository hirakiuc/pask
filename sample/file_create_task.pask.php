<?php
/**
 * file create task
 */

desc("File create task");
task("create_file", function() {
  touch("/tmp/hogehoge");
});

?>
