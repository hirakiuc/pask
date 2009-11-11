<?php

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
  public static function create($dir_full_path){ 
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
   *
   * @param string $dir_full_path  target directory fullpath.
   * @param bool   $recursive      delete recursively(TRUE) or not(FALSE:default)
   */
  public static function delete($dir_full_path, $recursive){
    $normalized_dpath = realpath($dir_full_path);

    // TODO privame method 
    // ------------------------------------------------
    if($normalized_dpath == null){
      throw new InvalidArgumentPathError(
        "Invalid Argument path (realpath() failed): "
        .$dir_full_path
      ); 
    } 
    // ------------------------------------------------

    if($recursive){
      $this->recursive_delete($normalized_dpath);
    }else{
      $this->targetdir_delete($normalized_dpath);
    }
  }

  /**
   *
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
   *  
   */
  private static function targetdir_delete($dpath){
    try{ 
      if(!rmdir($normalized_dpath)){
        throw new DeleteDirectoryFailedError(
          "Failed to delete the directory: "
          .$normalized_dpath
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
  }


}

?>
