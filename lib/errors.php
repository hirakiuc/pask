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

/** Argument Error Class */
class ArgumentError extends Exception{}

/** Target Not Found Error Class */
class NotFoundError extends Exception{}

/** TargetDirectory Not Found Error Class */
class NotDirectoryError extends Exception{}

/** File can't Read Error Class */
class FileReadError extends Exception{}

/** Target Task Not Found Error Class */
class TaskNotFoundError extends Exception{}

/** Task Dependency Error Class */
class TaskDependencyError extends Exception{}

?>
