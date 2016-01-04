<?php

declare(strict_types=1);

namespace Lwf\Template;

use Lwf\Controller\Controller;
use Lwf\Template\Exception\InvalidArgumentException;
use Lwf\Template\Exception\OutOfBoundsException;

/**
 * Render a template
 */
class Renderer
{
    /** @var mixed[] */
    private $templateVars;
    /** @var Callable[] */
    private $templateFunctions;
    /** @var Controller */
    private $controller;
    
    /**
     * Constructor
     *
     * @param mixed[]    $templateVars      The variables which will be available in the template
     * @param Callable[] $templateFunctions The functions which will be available in the template
     */
    public function __construct(array $templateVars = [], array $templateFunctions = [])
    {
        if ($templateFunctions) {
            $this->addFunctions($templateFunctions);
        } else {
            $this->templateFunctions = [];
        }
        if ($templateVars) {
            $this->addFunctions($templateVars);
        } else {
            $this->templateVars = [];
        }
    }
    
    /**
     * Set the controller which will be available in the template
     * 
     * @param Controller $controller 
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }
    
    /**
     * Get the controller which will be available in the template
     * 
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }
    
    /**
     * Add a variable which will be available in the template
     *
     * @param string $name  The name which will be used in the template for using the variable
     * @param mixed  $value The value of the variable. If it's a Callable, the value will be the return of the call
     */
    public function addVar(string $name, $value)
    {
        if (is_callable($value)) {
            $value = call_user_func($value, $this);
        }
        $this->templateVars[$name] = $value;
    }
    
    /**
     * Add many variables which will be available in the template
     * 
     * @param array $vars The variables
     */
    public function addVars(array $vars)
    {
        foreach($vars as $name => $value)
        {
            $this->addVar($name, $value);
        }
    }
    
    /**
     * Add a function which will be available in the template
     *
     * If yo define a function whith the name "foo", the call in the template will looks like "$_foo(arg1, arg2, ...)"
     *
     * @param string   $name     The name which will be used in the template for using the function
     * @param Callable $function The function.
     *
     * @throws InvalidArgumentException If $function isn't Callable
     */
    public function addFunction(string $name, $function)
    {
        if (!is_callable($function)) {
            throw new InvalidArgumentException();
        }
        $c = $this;
        $this->templateFunctions['_' . $name] = function () use ($c, $function) {
            return call_user_func_array(
                $function,
                array_merge([$c], func_get_args())
            );
        };
    }
    
    
    /**
     * Add many functions which will be available in the template
     * 
     * @param Callable[] $functions The functions
     */
    public function addFunctions(array $functions)
    {
        foreach ($functions as $name => $function) {
            $this->addFunction($name, $function);
        }
    }
    
    /**
     * Get a function which will be available in the template
     * 
     * @param string $name The name of the function
     * 
     * @return Callable
     * 
     * @throws OutOfBoundsException If the function is not defined
     */
    public function getFunction(string $name)
    {
        if (!isset($this->templateFunctions['_' . $name])) {
            throw new OutOfBoundsException(
                sprintf('Undefined template function %s', $name)
            );
        }
        return $this->templateFunctions['_' . $name];
    }
    
    /**
     * Get a variable which will be available in the template
     * 
     * @param string $name The name of the variable
     * 
     * @return mixed
     * 
     * @throws OutOfBoundsException if the variable is not defined
     */
    public function getVar(string $name)
    {
        if (!isset($this->templateVars[$name])) {
            throw new OutOfBoundsException(
                sprintf('Undefined template variable %s', $name)
            );
        }
        return $this->templateVars[$name];
    }
    
    /**
     * Render a template and returns it as a plain string
     * 
     * @param string $template The name of the template
     * 
     * @return string
     *
     * @throws \Throwable Any exception which would be thrown in the template
     */
    public function render(string $template): string
    {
        extract($this->templateVars);
        extract($this->templateFunctions);
        $c = $this->controller;
        ob_start();
        try {
            require $template;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }
    
    /**
     * This method is used for a _include template function and should not be used for any other meaning
     * 
     * @param string $template The name of the template
     */
    public function renderRaw(string $template)
    {
        extract($this->templateVars);
        extract($this->templateFunctions);
        $c = $this->controller;
        require $template;
    }
}
