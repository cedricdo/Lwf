<?php

declare(strict_types = 1);

namespace Lwf\Debug;

/**
 * ErrorHandler converts a PHP error into a \ErrorException
 */
class ErrorHandler
{
    /**
     * Handle a PHP error and throw a \ErrorException
     *
     * @param int    $severity The severity of the error
     * @param string $message  The message of the error
     * @param string $file     The filename where the error has been triggered
     * @param int    $line     The line number in the file at wich the error has been triggered
     *
     * @throws \ErrorException
     *
     * @see http://php.net/manual/en/function.set-error-handler.php
     */
    public function handle(int $severity, string $message, string $file, int $line)
    {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}