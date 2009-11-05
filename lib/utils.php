<?php
/**
 *
 */

/**
 * Utility Functions Class
 */
class Utils{

  /**
   * parse task_name to paskfile name, namespace and CamelCased className.
   *
   * @param string $task_name taskname with namespace.(not normalized)
   * @return array
   */
  public static function parse_taskname($task_name){
    $splited = explode(":",$task_name);

    $paskfile_name  = array_pop($splited);

    $className = Utils::to_camelCased($paskfile_name); 

    $namespace = "";
    // TODO resolve namespace problem... 
//    $namespace = implode($splited);

    return array(
      'paskfile_name' => $paskfile_name,
      'task_name'     => $task_name,     // namespace:paskfile_name
      'namespace'     => $namespace,     // namespace separated by ':'
      'className'     => $className      // task class name
    );
  }

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

} 
?>
