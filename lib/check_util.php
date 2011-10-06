<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * task define function
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */ 

class CheckUtil 
{
  public static function check_string($arg, $msg)
  {
    if (!is_string($arg)) {
      throw new Exception($msg);
    }
  }

  public static function check_callable($arg, $msg)
  {
    if (!is_callable($arg)) {
      throw new Exception($msg);
    } 
  }

  public static function check_array_with_strings($arg, $msg)
  {
    if (!is_array($arg)) {
      throw new Exception($msg);
    }

    foreach($arg as $key => $value) {
      if (!is_string($value)) {
        throw new Exception($msg);
      }
    }
  }
}

?>
