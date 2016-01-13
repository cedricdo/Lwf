<?php

declare(strict_types=1);

namespace Lwf\Http;

use Lwf\Controller\Controller;
use Lwf\Debug\ErrorHandler;
use Lwf\Debug\ExceptionHandler;
use Lwf\Http\Exception\InvalidArgumentException;
use Lwf\Http\Exception\OutOfBoundsException;
use Lwf\Routing\Exception\MethodNotAllowedException;
use Lwf\Routing\Exception\AccessDeniedException;
use Lwf\Routing\Exception\RouteNotFoundException;
use Lwf\Http\Exception\MethodNotAllowedHttpException;
use Lwf\Http\Exception\AccessDeniedHttpException;
use Lwf\Http\Exception\NotFoundHttpException;
use Lwf\Http\Exception\HttpException;

/**
 * Represent the Kernel of an HTTP application
 */
class Kernel
{
    const DIR = __DIR__;
    const DEBUG = true;
    const NO_DEBUG = false;

    /** @var mixed[]  */
    private $loadedServices;
    /** @var Callable[]  */
    private $unloadedServices;
    /** @var bool */
    private $debug;
    /** @var string */
    private $confDir;
    /** @var string */
    private $appDir;
    /** @var string */
    private $publicDir;

    /**
     * Constructor
     *
     * @param bool   $debug     True if the kernel has to run in debug mode
     * @param string $confDir   The directory where the configuration files are located
     * @param string $appDir    The directory where the application files are located
     * @param string $publicDir The directory where the public files are located
     */
    public function __construct(
        bool $debug = self::NO_DEBUG, string $confDir = 'conf', string $appDir = 'app', string $publicDir = 'public'
    ) {
        $this->loadedServices = [];
        $this->unloadedServices = [];
        $this->debug = $debug;

        $this->confDir = rtrim($confDir, '/') . '/';
        $this->appDir = rtrim($appDir, '/') . '/';
        $this->publicDir = rtrim($publicDir, '/') . '/';

        // We load the configuration provided
        $conf = require $this->confDir . 'config.php';
        // We add the services
        $this->addServices(require $this->confDir . 'services.php');
        /** @var \Lwf\Config\Config $config */
        $config = $this->getService('config');
        $config->merge($conf);
        $config->set('config.directory', $this->confDir);
        $config->set('app.directory', $this->appDir);
        $config->set('public.directory', $this->publicDir);
        // If error/exception handler are defined, we use them
        if ($this->hasService('handler.error')) {
            $this->setErrorHandler($this->getService('handler.error'));
        }
        if ($this->hasService('handler.exception')) {
            $this->setExceptionHandler($this->getService('handler.exception'));
        }
        // If a timezone is provided, we use it
        if ($config->has('timezone')) {
            date_default_timezone_set($config->get('timezone'));
        }
        // Finally let's add the routes
        /** @var \Lwf\Routing\Router $router */
        $router = $this->getService('routing.router');
        $router->addRoutes(require $this->confDir . 'routes.php');
    }

    /**
     * Get the path to the configuration directory
     *
     * @return string
     */
    public function getConfDir(): string
    {
        return $this->confDir;
    }

    /**
     * Get the path to the application directory
     * @return string
     */
    public function getAppDir(): string
    {
        return $this->appDir;
    }

    /**
     * Get the path to the public directory
     *
     * @return string
     */
    public function getPublicDir(): string
    {
        return $this->publicDir;
    }

    /**
     * Define the error handler of the application
     *
     * @see http://php.net/manual/fr/function.set-error-handler.php
     *
     * @param ErrorHandler $handler The error handler
     */
    public function setErrorHandler(ErrorHandler $handler)
    {
        set_error_handler([$handler, 'handle']);
    }

    /**
     * Define the exception handler of the application
     *
     * @see http://php.net/manual/fr/function.set-exception-handler.php
     *
     * @param ExceptionHandler $handler The exception handler
     */
    public function setExceptionHandler(ExceptionHandler $handler)
    {
        set_exception_handler([$handler, 'handle']);
    }

    /**
     * Tells if the kernel is running in debug mode
     *
     * @return bool
     */
    public function isInDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Define if the kernel is running in debug mode
     *
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * Test if a service exists
     *
     * @param string $name The name of the service
     *
     * @return bool
     */
    public function hasService(string $name)
    {
        return isset($this->loadedServices[$name]) || isset($this->unloadedServices[$name]);
    }
    
    /**
     * Get a service
     * 
     * @param string $name The name of the service
     *
     * @return mixed
     *
     * @throws OutOfBoundsException If the service is not defined
     */
    public function getService(string $name)
    {
        if(isset($this->loadedServices[$name]))
        {
            return $this->loadedServices[$name];
        }
        
        if(isset($this->unloadedServices[$name]))
        {
            $this->loadedServices[$name] = $this->unloadedServices[$name]($this);
            unset($this->unloadedServices[$name]);
            return $this->loadedServices[$name];
        }
        
        throw new OutOfBoundsException(sprintf("Service %s not found", $name));
    }

    /**
     * Add a service
     *
     * @param string   $name   The name of the service
     * @param callable $loader A callable which will return the service
     *
     * @throws InvalidArgumentException If $loader isn't a valid callable
     */
    public function addService(string $name, $loader)
    {
        if (!is_callable($loader)) {
            throw new InvalidArgumentException();
        }
        $this->unloadedServices[$name] = $loader;
    }
    
    /**
     * Add many services
     * 
     * @param array $services
     */
    public function addServices(array $services)
    {
        foreach($services as $name => $service)
        {
            $this->addService($name, $service);
        }
    }
    
    /**
     * Remove a service
     * 
     * @param string $name The name of the service
     *
     * @throws OutOfBoundsException If the service is not defined
     */
    public function removeService(string $name)
    {
        if (!isset($this->unloadedServices[$name]) && !isset($this->loadedServices[$name])) {
            throw new OutOfBoundsException(sprintf("Service %s not found", $name));
        }
        unset($this->unloadedServices[$name]);
        unset($this->loadedServices[$name]);
    }
    
    /**
     * Remove every services
     */
    public function clear()
    {
        $this->unloadedServices = [];
        $this->loadedServices = [];
    }
    
    /**
     * Handle a request and return the according Response
     *
     * This method will try to match a route and to call the matching controller
     *
     * @param Request $request The HTTP request
     *
     * @return Response
     *
     * @throws \Throwable If an exception occurs during the process and the kernel is in debug mode
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->handleRaw($request);
        } catch (\Throwable $e) {
            if ($e instanceof HttpException) {
                $code = $e->getHttpCode();
                $headers = $e->getHeaders();
            } else {
                $code = 500;
                $headers = [];
            }

            if ($this->isInDebug()) {
                throw $e;
            }

            $templateCollection = [
                $this->appDir . 'Templates/' . $code . '.php',
                $this->appDir . 'Templates/error.php',
                __DIR__ . '/Templates/' . $code . '.php',
                __DIR__ . '/Templates/error.php'
            ];
            foreach ($templateCollection as $template) {
                if (is_file($template)) {
                    $templatePath = $template;
                    break;
                }
            }

            /** @var \Lwf\Template\Renderer $renderer */
            $renderer = $this->getService('template.renderer');
            $renderer->addVar('baseUrl', $request->getBaseUrl());
            $renderer->addVar('code', $code);
            $renderer->addVar('text', $e->getMessage());

            return new Response(
                $renderer->render($templatePath),
                $code,
                $headers
            );
        }
    }

    /**
     * Helper for the handle() method
     *
     * @param Request $request The HTTP request to handle
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException If the user can not access the resource
     * @throws MethodNotAllowedHttpException If the user can not access the resource with the supplied HTTP method
     * @throws NotFoundHttpException If the resource is not found
     */
    protected function handleRaw(Request $request): Response
    {
        try {
            /** @var \Lwf\Routing\Router $router */
            $router = $this->getService('routing.router');
            /** @var \Lwf\Security\User $user */
            $user = $this->getService('security.user');
            /** @var \Lwf\Controller\ControllerResolver $resolver */
            $resolver = $this->getService('controller.resolver');

            $this->addService('http.request', function () use ($request) {
                return $request;
            });

            // Match the route with the request and add the result as request attribute
            $request->addAttributes($router->match($request, $user));

            // Fetch the controller and his parameters
            $controller = $resolver->getController($request, $this->appDir);
            $parameters = $resolver->getParameters($request, $controller);

            // If the controller is a Controller object, let's load some stuff
            if (is_array($controller) && $controller[0] instanceof Controller ) {
                /** @var \Lwf\Template\Renderer $renderer */
                $renderer = $this->getService('template.renderer');

                $controller[0]->setKernel($this);
                $renderer->setController($controller[0]);
                $renderer->addFunctions(require $this->confDir . 'templateFunctions.php');
                $renderer->addVars(require $this->confDir . 'templateVars.php');
            }
            $response = call_user_func_array($controller, $parameters);

            return $response;
        } catch (MethodNotAllowedException $e) {
            throw new MethodNotAllowedHttpException(
                $e->getAllowedMethods(),
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (AccessDeniedException $e) {
            throw new AccessDeniedHttpException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (RouteNotFoundException $e) {
            throw new NotFoundHttpException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
