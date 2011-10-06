<?php
/**
 * file delete task
 */

desc("File delete task");
task("delete_file", function() {
  unlink("/tmp/hogehoge");
});

?>
