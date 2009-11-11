<?php

/**
 *
 */
class CommandFailedError extends Exception{}

/**
 *
 */
class Shell{

  /**
   * execute $cmd string by system()
   * Raise CommandFailedError Exception if command failed.
   * 
   */
  public static function sh($cmd){
    $ret = system($cmd, $retval);
    if($ret == FALSE){
      throw new CommandFailedError("command:'".$cmd."' failed.");
    }
  }

}

?>
