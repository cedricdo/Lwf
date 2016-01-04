<?php

declare(strict_types=1);

namespace Lwf\Routing\Exception;

/**
 * This exception will be thrown if a least one route has been matched but there's a mismatch between route HTTP
 * method and request HTTP method.
 */
class MethodNotAllowedException extends \RuntimeException implements Exception
{
    /** @var string[]  */
    protected $methods;

    /**
     * Constructor
     * 
     * @param string[] $methods The allowed HTTP method for the path
     * @param string $message The message of the exception
     * @param int $exceptionCode The code of the exception
     * @param \Throwable $previous The previous exception in the stack
     */
    public function __construct(
        array $methods, string $message = null, int $exceptionCode = 0, \Throwable $previous = null
    ) {
        $this->methods = array_map('strtoupper', $methods);
        parent::__construct($message, $exceptionCode, $previous);
    }

    /**
     * Get the allowed HTTP method for the path
     * 
     * @return string[]
     */
    public function getAllowedMethods()
    {
        return $this->methods;
    }
}
