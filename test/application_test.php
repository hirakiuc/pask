<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
require_once(dirname(__FILE__)."/../lib/include.php");
/**
 * Application Test Class File
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

class ApplicationTest extends PHPUnit_Framework_TestCase{

  public function testExitCode(){
    $ref = new ReflectionClass("Application");
    $static_values = $ref->getStaticProperties();
    
//    $success_code = $ref->getStaticPropertyValue("SUCCESS"); 
    $this->assertEquals(0, $static_values["SUCCESS"]);

//    $error_code = $ref->getStaticPropertyValue("ERROR");
    $this->assertEquals(1, $static_values["ERROR"]); 
  }

}


?>
