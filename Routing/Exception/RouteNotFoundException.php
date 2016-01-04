<?php

declare(strict_types=1);

namespace Lwf\Routing\Exception;

/**
 * This exception will be thrown when the router can't match any route with the request
 */
class RouteNotFoundException extends \InvalidArgumentException implements Exception
{
    
}
