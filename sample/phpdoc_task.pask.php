<?php 
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Sample PhpDoc Creation Task File
 *
 * create phpdoc this library to ../doc directory.
 * (check out phpdocumentor package already installed and 
 *  phpdoc command include your $PATH)
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

class PhpdocTask extends Pask{

  public $desc = "Create PHPDoc this library.";

  public $before_tasks = array();

  public function run(){ 
    Shell::sh("phpdoc -t ../doc -d ../lib -o HTML:frames:l0l33t"); 
  }
} 

?>
