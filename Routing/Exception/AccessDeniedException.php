<?php

declare(strict_types=1);

namespace Lwf\Routing\Exception;

/**
 * This exception will be thrown if a route has been found but the user has not sufficient role to access
 */
class AccessDeniedException extends \RuntimeException implements Exception
{
    
}
