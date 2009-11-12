<?php

require_once(dirname(__FILE__)."/../console_writer.php");
require_once(dirname(__FILE__)."/../errors.php");
require_once("dir.php"); 

/**  */
class CreateDirectoryFailedError extends Exception{}

/**  */
class DirectoryAlreadyExistError extends Exception{}

/**  */
class InvalidParentDirectoryError extends Exception{}

/**  */
class FileListingError extends Exception{}

/**  */
class DeleteDirectoryFailedError extends Exception{}


/**
 *
 */
class DirUtil{

  /**
   * create directory recursively.(same 'mkdir -p $dir_path)
   *
   * @param $dir_path string target directory path to create.
   */
  public static function create($dir_path){ 
    $writer = ConsoleWriter::getInstance();

    if(!file_exists($dir_path)){
      // create if not exist dir
      if(!mkdir($dir_path)){
        // create dir failed..
        throw new CreateDirectoryFailedError(
          "Failed to create a directory: " 
          .$dir_path
        );
      }

      $writer->puts("create: " . $dir_path); 
    }else if(!is_dir($dir_path)){
      // same name file exists.
      throw new CreateDirectoryFailedError(
        "Failed to create a directory: "
        ."same name file exists(" .$dir_path .")"
      );
    }else{
      // already exist the $path directory.  
      $writer->puts("Directory already exist: " . $dir_path); 
    } 
  }

  /**
   * delete target directory.
   *
   * @param string $dir_full_path  target directory fullpath.
   * @param bool   $recursive      delete recursively(TRUE) or not(FALSE:default)
   */
  public static function delete($dir_path, $recursive=FALSE){
    $normalized_dpath = realpath($dir_path);

    // TODO privame method 
    // ------------------------------------------------
    if($normalized_dpath == null){
      throw new InvalidArgumentPathError(
        "Invalid Argument path (realpath() failed): "
        .$dir_path
      ); 
    } 
    // ------------------------------------------------

    if($recursive){
      DirUtil::recursive_delete($normalized_dpath);
    }else{
      DirUtil::targetdir_delete($normalized_dpath);
    }
  }

  /**
   * delete recursively target directory.
   * (use RecursiveDirectoryIterator and FileUtil.delete...)
   *
   * @param string $dpath normalized target directory path.
   */
  private static function recursive_delete($dpath){
    try{
      $dir_iter = new RecursiveDirectoryIterator($dpath);
      $iter = new RecursiveIteratorIterator($dir_iter, 
                  RecursiveIteratorIterator::CHILD_FIRST);
      
      foreach($iter as $file){
        if(!$file->isDot()){
          if($file->isFile()){
            // delete file
            FileUtil.delete($file->getPathname); 
          }else{
            // delete directory
            if(!rmdir($file->getPathname())){
              throw new DeleteDirectoryFailedError(
                "Failed to delete the directory: "
                .$file->getPathname()
              );
            } 
          }
        }
      } 
    }catch(Exception $err){
      throw $err;
    } 
  }

  /**
   * delete only target directory.(use rmdir() in php)
   *
   * @param string $dpath normalized target directory path.
   */
  private static function targetdir_delete($dpath){
    try{ 
      if(!rmdir($dpath)){
        throw new DeleteDirectoryFailedError(
          "Failed to delete the directory: "
          .$dpath
        );
      }
    }catch(Exception $err){
      throw $err;
    }
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

    return $ret;
  } 
}

?>
