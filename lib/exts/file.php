<?php

require_once("shell.php");

/**  */
class CreateFileFiledError extends Exception{}

/**  */
class CreateDirectoryFailedError extends Exception{}

/**  */
class FileAlreadyExistError extends Exception{}

/**  */
class DirectoryAlreadyExistError extends Exception{}

/**  */
class InvalidParentDirectoryError extends Exception{}

/**  */
class FileListingError extends Exception{}

/**
 *
 */ 
class FileUtil{

  /**
   * create file specified by $fpath.
   *
   * @param $fpath string target file path to create.
   */
  public static function create_file($fpath){
    $parent_dir = dirname($fpath);

    $normalized_dpath = realpath($parent_dir);
    if($normalized_dpath == null){
      throw new CreateFileFiledError(
        "Failed to create a file: "
        ."parent directory don't exist:".$normalized_dpath
      ); 
    }

    if(!file_exists($parent_dir) || !is_dir($parent_dir)){ 
      // parent path is not exist or not directory.
      throw new InvalidParentDirectoryError(
        $parent_dir . " is not exist or not directory."
      );
    }

    $writer = ConsoleWriter::getInstance();

    $normalized_fpath = $normalized_dpath."/".basename($fpath);

    if(file_exists($normalized_fpath)){
      $writer->puts("Already exists the file: ".$normalized_fpath);
    }else{
      if(!touch($normalized_fpath)){
        throw new CreateFileFailedError(
          "Failed to create a file: " 
          . $normalized_fpath 
        );
      }else{
        $writer->puts("Create: " . $normalized_fpath); 
      }
    }
  }

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
