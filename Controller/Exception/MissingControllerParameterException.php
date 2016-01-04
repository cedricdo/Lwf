<?php

declare(strict_types=1);

namespace Lwf\Controller\Exception;

/**
 * This exception will be thrown if the parameter _controller doesn't exist in the Request object
 */
class MissingControllerParameterException extends \InvalidArgumentException implements Exception
{
    
}
