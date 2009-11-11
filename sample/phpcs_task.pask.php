<?php 
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Sample PHP_CodeSniffer Task File
 *
 * show php_codesniffer summary report.
 * (check out php_codesniffer package already installed and 
 *  phpcs command include your $PATH)
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */


require_once(dirname(__FILE__)."/../lib/include.php");
require_once(dirname(__FILE__)."/../lib/exts/shell.php");

class PhpcsTask extends Pask{

  public $desc = "Create PHPDoc this library.";

  public $before_tasks = array();

  public function run(){ 
    Shell::sh("phpcs --report=summary ../lib");
  }
} 

?>
