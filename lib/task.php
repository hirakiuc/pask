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

require_once 'lib/pask_holder.php';
require_once 'lib/check_util.php';


function task()
{
  $holder = PaskHolder::getInstance();

  $num_args = func_num_args();
  switch($num_args) {
    case 2:
      $name = func_get_arg(0);
      CheckUtil::check_string($name, "Invalid argument.");
      
      $callback = func_get_arg(1);
      CheckUtil::check_callable($callback, "Invalid argument.");

      $holder->add_task($name, array(), $callback);
      break;

    case 3:
      $name = func_get_arg(0);
      CheckUtil::check_string($name, "Invalid argument.");

      $depend_tasks = func_get_arg(1);
      CheckUtil::check_array_with_strings($depend_tasks, "Invalid argument.");

      $callback = func_get_arg(2);
      CheckUtil::check_callable($callback, "Invalid argument.");

      $holder->add_task($name, $depend_tasks, $callback);
      break;
    default:
      throw new Exception("Invalid argument."); 
  } 
}

function desc($desc)
{
  CheckUtil::check_string($desc, "Invalid argument.");

  $holder = PaskHolder::getInstance();
  $holder = $holder->add_desc($desc);
}

?>
