<?php
/**
 * directory task
 */

desc("Directory Create task");
task("create_dir", function() {
  mkdir("/tmp/hogehoge");
});

?>
