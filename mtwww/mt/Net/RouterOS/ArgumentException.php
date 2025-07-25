<?php

/**
 * RouterOS API client implementation.
 * 
 * This package allows you to read and write information from a RouterOS host using MikroTik's RouterOS API.
 * 
 * PHP version 5
 * 
 * @link http://netrouteros.sourceforge.net/
 * @category Net
 * @package Net_RouterOS
 * @version 1.0.1
 * @author Vasil Rangelov <boen.robot@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @copyright 2011 Vasil Rangelov
 */
/**
 * The namespace declaration.
 */
namespace Net\RouterOS;

/**
 * Exception thrown when there's something wrong with message parts.
 * @package Net_RouterOS
 */
class ArgumentException extends Exception
{
    
}