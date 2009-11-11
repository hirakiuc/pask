<?php

require_once("shell.php");
require_once("dir.php");

/**  */
class CreateFileFailedError extends Exception{}

/**  */
class FileAlreadyExistError extends Exception{}

/**  */
class InvalidFilePathError extends Exception{}

/**  */
class CantOpenFileError extends Exception{}

/**  */
class FileWriteFailedError extends Exception{}

/**  */
class FileLockFailedError extends Exceotion{}

/**  */
class DeleteFileFailedError extends Exception{}

/**
 *
 */ 
class FileUtil{

  /**
   * create file specified by $fpath.
   *
   * @param $fpath string target file path to create.
   */
  public static function create($fpath){
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
          "Failed to create the file: " 
          . $normalized_fpath 
        );
      }else{
        $writer->puts("Create: " . $normalized_fpath); 
      }
    }
  }

  /**
   *
   */
  public static function delete($fpath){
    $parent_dir = dirname($fpath);

    $normalized_dpath = realpath($parent_dir);
    if($normalized_dpath == null){
      throw new CreateFileFiledError(
        "Failed to create a file: "
        ."parent directory don't exist:".$normalized_dpath
      ); 
    }

    $writer = ConsoleWriter::getInstance();
    $normalized_fpath = $normalized_dpath."/".basename($fpath);

    if(file_exists($normalized_fpath)){
      if(!unlink($normalized_fpath)){
        throw new DeleteFileFailedError(
          "Failed to delete the file: "
          . $normalized_fpath
        );
      }else{
        $writer->puts("Delete: " . $normalized_fpath);
      }
    }else{
      $writer->puts("File don't exists:".$normalized_fpath);
    }
  }

  /**
   * write the $lines to the $fpath with $mode.
   *
   * @param string $fpath file path to write the contents.
   * @param string $mode  file open mode(@see fopen)
   * @param array  $lines simple array contain strings
   */
  public function write($fpath, $mode, $lines){
    $writer = ConsoleWriter::getInstance();

    $normalized_fpath = realpath($fpath);
    if($normalized_fpath == null){
      throw new InvalidFileError("Invalid file path:" . $fpath);
    }

    $fp = fopen($normalized_fpath);

    if($fp){
      if(flock($fp, LOCK_EX)){
        $writer->debug("Write the file:" . $normalized_fpath);

        // write each lines to the file.
        foreach($lines as $line){
          if(fwrite($fp, $line) == FALSE){
            $writer->error("Failed to write the file:".$normalized_fpath);

            throw new FileWriteFailedError(
              "Failed to write the file:"
              .$normalized_fpath
            ); 
          }else{
            $writer->debug("write:" . $line);
          }
        }

      }else{
        throw new FileLockFailedError(
          "Can't lock the file to write:"
          .$normalized_fpath
        );
      }
    }else{
      throw new FileOpenFailedError(
        "Can't open the file:" 
        .$normalized_fpath
      );
    }
  }

}

?>
