<?php

declare(strict_types=1);

namespace Lwf\Routing\Exception;

/**
 * This exception will be thrown where a mandatory parameter is missing in a route
 */
class MissingMandatoryParametersException extends \InvalidArgumentException implements Exception
{
    
}
