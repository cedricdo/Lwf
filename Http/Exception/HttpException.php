<?php

declare(strict_types=1);

namespace Lwf\Http\Exception;

use Lwf\Http\Response;

/**
 * Base exception of this HTTP Module.
 */
class HttpException extends \RuntimeException implements Exception
{
    /** @var string[]  */
    private $headers;
    /** @var int  */
    private $httpCode;

    /**
     * Constructor.
     *
     * @param int        $httpCode      The exception HTTP code.
     * @param string     $message       The exception error message.
     * @param string[]   $headers       The HTTP headers to send.
     * @param int        $exceptionCode The exception code.
     * @param \Throwable $previous      The previous exception in the stack.
     *
     * @throws OutOfBoundsException If the HTTP code is invalid
     */
    public function __construct(int $httpCode, string $message, array $headers = [], int $exceptionCode = null,
        \Throwable $previous = null
    ) {
        if (false === array_key_exists($httpCode, Response::STATUS_TEXTS)) {
            throw new OutOfBoundsException(sprintf("The HTTP code %d is invalid", $httpCode));
        }

        $this->headers = $headers;
        $this->httpCode = $httpCode;
        
        parent::__construct($message, $exceptionCode, $previous);
    }
    
    /**
     * Get the HTTP code of the exception
     * 
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
    
    /**
     * Get the HTTP headers of the exception
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
