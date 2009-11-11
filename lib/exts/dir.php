<?php

/**  */
class CreateDirectoryFailedError extends Exception{}

/**  */
class DirectoryAlreadyExistError extends Exception{}

/**  */
class InvalidParentDirectoryError extends Exception{}

/**  */
class FileListingError extends Exception{}

/**
 *
 */
class DirUtil{

  /**
   * create directory recursively.(same 'mkdir -p $dir_path)
   *
   * @param $dir_path string target directory path to create.
   */
  public static function create_dir($dir_full_path){ 
    $dirs = implode("/", $dir_full_path);

    $path = "/";
    foreach($dirs as $dir){
      $path = $path . "/" . $dir;
      if(!file_exist($path)){
        // create if not exist dir
        if(mkdir($path)){
          // create dir failed..
          throw new CreateDirectoryFailedError(
            "Failed to create a directory: " 
            .$path
          );
        }
      }else if(!is_dir($path)){
        // same name file exists.
        throw new CreateDirectoryFailedError(
          "Failed to create a directory: "
          ."same name file exists(" .$path .")"
        );
      }else{
        // already exist the $path directory.
      } 
    } 

    $writer = ConsoleWriter::getInstance();
    $writer->puts("create: " . $dir_full_path);
  }

  /**
   * Get files and directories list
   *
   * @param string $dir_path target_directory path
   * @param function $filter filter function (argument is a DirectoryIterator object)
   * @return array( fpath1, fpath2, ... ) (fpath specify file or directory.)
   */
  public static function lists($dir_path, $filter=null){
    $normalized_dpath = realpath($dir_path);
    if($normalized_dpath == null){
      throw new FileListingError(
        "Failed File listing: failed to normalize " . $dir_path
      );
    }

    $ret = array();

    try{
      $iter = new DirectoryIterator($normalized_dpath);
      foreach($iter as $fileinfo){
        if(!$fileinfo->isDot()){
          array_push($ret, $fileinfo->getPathname());
        }
      }

      if($filter != null){
        array_walk($ret, $filter);
      }

    }catch(Exception $err){
      throw $err;
    } 
  }


}

?>
