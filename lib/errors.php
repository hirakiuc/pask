<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Error Class File
 *
 * PHP version 5
 *
 * @author hirakiuc <hirakiuc@gmail.com>
 * @copyright 2009 pask project team
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version Git:$Id$
 */

/** */
class ArgumentError extends Exception{}

/** */
class NotFoundError extends Exception{}

/** */
class NotDirectoryError extends Exception{}

/** */
class FileReadError extends Exception{}

/** */ 
class TaskParseError extends Exception{}

/** */
class TaskNotFoundError extends Exception{}

/** */
class InvalidTaskClassError extends Exception{} 

?>
