<?php

declare(strict_types=1);

namespace Lwf\Http\Exception;

/**
 * This exception will be thrown if the user is not allowed to access a HTTP resource
 */
class AccessDeniedHttpException extends HttpException
{    
    /**
     * Constructor.
     *
     * @param string     $message       The exception error message.
     * @param int        $exceptionCode The exception error code.
     * @param \Throwable $previous      the previous exception.
     */
    public function __construct(string $message = null, int $exceptionCode = null, \Throwable $previous = null)
    {
        parent::__construct(403, $message, [], $exceptionCode, $previous);
    }
}
