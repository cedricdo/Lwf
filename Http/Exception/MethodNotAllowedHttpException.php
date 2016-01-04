<?php

declare(strict_types=1);

namespace Lwf\Http\Exception;

/**
 * This exception will be thrown if the user access a resource with a method which is not allowed
 */
class MethodNotAllowedHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string[]   $allowed       An array of the allowed HTTP method to access the resource
     * @param string     $message       The exception error message.
     * @param int        $exceptionCode The exception error code.
     * @param \Throwable $previous      the previous exception.
     */
    public function __construct(array $allowed, string $message = null, int $exceptionCode = null,
    \Throwable $previous = null
    ) {
        $headers = ['Allow' => strtoupper(implode(', ', $allowed))];
        parent::__construct(405, $message, $headers, $exceptionCode, $previous);
    }
}
