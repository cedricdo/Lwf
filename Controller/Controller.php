<?php

declare(strict_types=1);

namespace Lwf\Controller;

use Lwf\Http\Kernel;
use Lwf\Http\Response;
use Lwf\Routing\Router;

/**
 * Controller base class
 */
abstract class Controller
{
    /** @var  Kernel */
    private $kernel;
    
    /**
     * Set the kernel which will be used by the controller
     * 
     * @param Kernel $kernel The kernel
     * 
     */
    public function setKernel(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Return the kernel used by the controller
     *
     * @return Kernel
     *
     */
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }
    
    /**
     * Convenience method for getting a service
     * 
     * @param string $service The name of the service
     * 
     * @return mixed
     */
    public function get(string $service)
    {
        return $this->kernel->getService($service);
    }
    
    /**
     * Convenience method for generating an absolute url from a route name and parameters
     * 
     * @param string $routeName The name of the route
     * @param array $parameters The parameters
     * 
     * @return string
     */
    public function generateUrl(string $routeName, array $parameters = []) : string
    {
        return $this->get('routing.router')->generateUrl(
            $this->get('http.request'),
            $routeName, 
            $parameters, 
            Router::ABSOLUTE_URL
        );
    }
    
    /**
     * Convenience method for generating a relative url from a route name and parameters
     *
     * @param string $routeName The name of the route
     * @param array $parameters The parameters
     * 
     * @return string
     */
    public function generatePath(string $routeName, array $parameters = []) : string
    {
        return $this->get('routing.router')->generateUrl(
            $this->get('http.request'),
            $routeName, 
            $parameters, 
            Router::ABSOLUTE_PATH
        ); 
    }
    
    /**
     * Render a template and returns it as a Response object
     * 
     * @param string $template The name of the template
     * 
     * @return Response
     */
    public function render(string $template) : Response
    {
        $render = $this->get('template.renderer')->render(
            dirname($this::DIR) . '/Templates/' . $template
        );
        
        return new Response($render);
    }
    
    /**
     * Render a template and returns it as plain string
     *
     * @param string $template The name of the template
     * 
     * @return string
     */
    public function renderRaw(string $template) : string
    {
        $render = $this->get('template.renderer')->render(
            dirname($this::DIR) . '/Templates/' . $template
        );
        
        return $render;
    }
    
    /**
     * Build a Response object which will make a redirection
     * 
     * @param string $url The destination url
     * @param int $code The HTTP code for the redirection
     *
     * @return Response
     */
    public function redirect(string $url, int $code = 302) : Response
    {
        $kernel = $this->kernel;
        /** @var \Lwf\Template\Renderer $renderer */
        $renderer = $this->get('template.renderer');
        if (is_file(rtrim($kernel->getAppDir(), '/') . '/Templates/Http/' . $code . '.php')) {
            $template = rtrim($kernel->getAppDir(), '/') . '/Templates/Http/' . $code . '.php';
        } else {
            $template = rtrim($kernel::DIR, '/') . '/Templates/' . $code . '.php';
        }
        
        $renderer->addVar('url', $url);
        return new Response($renderer->render($template), $code, ['Location' => $url]);
    }
}
