<?php

declare(strict_types=1);

namespace Lwf\Controller;

use Lwf\Http\Request;
use Lwf\Controller\Exception\BadFunctionCallException;
use Lwf\Controller\Exception\InvalidArgumentException;
use Lwf\Controller\Exception\MissingControllerParameterException;

/**
 * Get a callable object of the Controller from a Request
 */
class ControllerResolver
{
    /**
     * Get the callable object matching from a request
     *
     * @param Request $request The HTTP request
     * @param string  $appDir  The directory where the application is located. it's also the base of the namespace of
     *                         the controllers
     * 
     * @return Callable
     * 
     * @throws MissingControllerParameterException If the parameter _controller is missing in the request
     * @throws \LogicException Si le nom du contrôleur ne peut être parsé. 
     */
    public function getController(Request $request, string $appDir)
    {
        if (!$request->hasAttribute('_controller')) {
            throw new MissingControllerParameterException("Attribute _controller is missing in the Request object");
        }
        $controller = $request->getAttribute('_controller');

        if (is_array($controller) || (is_object($controller)  && method_exists($controller, '__invoke'))) {
            return $controller;
        }

        if (false === strpos($controller, ':') && method_exists($controller, '__invoke') ) {
            $controller = $controller . 'Controller';
            return new $controller;
        }

        if (false === strpos($controller, ':')) {
            throw new InvalidArgumentException(sprintf("Controller format %s is invalid", $controller));
        }

        list($class, $method) = explode(':', $controller);
        $className = ucfirst(trim($appDir, '/')) . '\Controllers\\' . $class . 'Controller';
        return [new $className, $method . 'Action'];
    }
    
    /**
     * Get the parameters of a controller
     * 
     * @param Request $request The current HTTP request instance
     * @param Callable $controller The controller found from the request
     * 
     * @return array
     *
     * @throws BadFunctionCallException If a mandatory parameter of the controller is missing
     */
    public function getParameters(Request $request, $controller)
    {
        $parameters = [];
        $attributes = $request->getAllAttributes();
        if (is_array($controller)) {
            $method = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $method = (new \ReflectionObject($controller))->getMethod('__invoke');
        } else {
            $method = new \ReflectionFunction($controller);
        }

        foreach ($method->getParameters() as $param) {
            if (isset($attributes[$param->getName()])) {
                $value = $attributes[$param->getName()];
                $type = $param->getType();
                // handle scalar type hinting
                if ($type instanceof \ReflectionType)
                {
                    switch ($type->__toString()) {
                        case 'int':
                            $value = (int)$attributes[$param->getName()];
                            break;
                        case 'float':
                            $value = (float)$attributes[$param->getName()];
                            break;
                        case 'bool':
                            $value = (bool)$attributes[$param->getName()];
                            break;
                        case 'string':
                            $value = (string)$attributes[$param->getName()];
                            break;
                    }
                }
                $parameters[] = $value;
            } elseif ($param->isDefaultValueAvailable()) {
                $parameters[] = $param->getDefaultValue();
            } elseif ($param->getName() == 'request') {
                $parameters[] = $request;
            } else {
                $repr = sprintf(
                    '%s::%s()',
                    get_class($controller[0]),
                    $controller[1]
                );
                throw new BadFunctionCallException(
                    sprintf('Missing mandatory parameter %s for controller %s', $param->getName(), $repr)
                );
            }
        }

        return $parameters;
    }
}
