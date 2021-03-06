<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Utility Function Class
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

/**
 * Utility Functions Class
 */
class Utils{

  /**
   * return camelCasedString.
   *
   * @param string $str target string to change a camelCased one.
   * @return camelCasedString
   */
  public static function to_camelCased($str){
    $val = str_replace('_',' ',strtolower($str));
    return str_replace(' ','',ucwords($val));
  }

  /**
   * return under_scored_string.
   *
   * @param string $str target string to change a under_scored one.
   * @return under_scored_string
   */
  public static function to_under_scored($str){
    return strtolower(preg_replace('/([a-z])([A-Z])/',"$1_$2",$str));
  }


  /**
   * get microsec (float value)
   */
  public static function get_time(){
    return microtime(TRUE);
  }

  /**
   * calc microsec time diff and return formated string
   *
   * @param microtime $start_time
   * return formatted msec time string.
   */
  public static function get_process_time($start_time){
    $diff = round(microtime(TRUE) - $start_time); 
    return sprintf("%0.2f",$diff);
  }
} 
?>
