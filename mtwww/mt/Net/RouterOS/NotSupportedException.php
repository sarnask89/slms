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
 * Exception thrown when encountering something not supported by RouterOS or
 * this package.
 * @package Net_RouterOS
 */
class NotSupportedException extends Exception
{

    /**
     *
     * @var mixed The unsuppported value.
     */
    private $_value = null;

    /**
     * Creates a new NotSupportedException.
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param Exception $previous The previous exception used for the exception
     * chaining.
     * @param mixed $value The unsupported value.
     */
    public function __construct($message, $code = 0, $previous = null,
                                $value = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->_value = $value;
    }

    /**
     * Gets the unsupported value.
     * @return mixed The unsupported value.
     */
    public function getValue()
    {
        return $this->_value;
    }

    // @codeCoverageIgnoreStart
    // String representation is not reliable in testing

    /**
     * Returns a string representation of the exception.
     * @return string The exception as a string.
     */
    public function __toString()
    {
        return parent::__toString() . "\nValue:{$this->_value}";
    }

    // @codeCoverageIgnoreEnd
}