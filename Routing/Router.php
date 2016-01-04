<?php

declare(strict_types=1);

namespace Lwf\Routing;

use Lwf\Http\Request;
use Lwf\Security\User;
use Lwf\Routing\Exception\MethodNotAllowedException;
use Lwf\Routing\Exception\MissingMandatoryParametersException;
use Lwf\Routing\Exception\InvalidParameterException;
use Lwf\Routing\Exception\RouteNotFoundException;
use Lwf\Routing\Exception\AccessDeniedException;

/**
 * A router handle a collection of routes and the matching between a request and a route
 */
class Router
{
    /** @var Route[] */
    private $routes;
    /** @var string  */
    private $compilerClass;
    
    const DECODED_CHARS = [
        // the slash can be used to designate a hierarchical structure and we want allow using it with this meaning
        // some webservers don't allow the slash in encoded form in the path for security reasons anyway
        // see http://stackoverflow.com/questions/4069002/http-400-if-2f-part-of-get-url-in-jboss
        '%2F' => '/',
        // the following chars are general delimiters in the URI specification but have only special meaning in the authority component
        // so they can safely be used in the path in unencoded form
        '%40' => '@',
        '%3A' => ':',
        // these chars are only sub-delimiters that have no predefined meaning and can therefore be used literally
        // so URI producing applications can use these chars to delimit subcomponents in a path segment without being encoded for better readability
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
    ];
    
    /**
     * Generates an absolute URL, e.g. "http://example.com/dir/file".
     */
    const ABSOLUTE_URL = 1;

    /**
     * Generates an absolute path, e.g. "/dir/file".
     */
    const ABSOLUTE_PATH = 2;

    /**
     * Generates a relative path based on the current request path, e.g. "../parent-file".
     */
    const RELATIVE_PATH = 4;

    /**
     * Generates a network path, e.g. "//example.com/dir/file".
     * Such reference reuses the current scheme but specifies the host.
     */
    const NETWORK_PATH = 8;
    
    /**
     * Constructor
     * 
     * @param string $compilerClass The class which will be used for route compilation
     */
    public function __construct(string $compilerClass)
    {
        $this->routes = [];
        $this->compilerClass = $compilerClass;
    }

    /**
     * Add many routes
     *
     * @param Route[] $routeCollection The routes you want to add
     */
    public function addRoutes(array $routeCollection)
    {
        foreach ($routeCollection as $name => $route) {
            $this->addRoute($name, $route);
        }
    }
    
    /**
     * Add a route
     *
     * @param string $name  The name of the route
     * @param Route  $route The Route object.
     */
    public function addRoute(string $name, Route $route)
    {
        if(false == $route->hasOption('compiler_class'))
        {
            $route->setOption('compiler_class', $this->compilerClass);
        }
        
        $this->routes[$name] = $route;
    }
    
    /**
     * Remove a route
     * @param string $name The name of the route
     */
    public function removeRoute(string $name)
    {
        unset($this->routes[$name]);
    }
    
    /**
     * Remove every routes
     */
    public function removeAllRoutes()
    {
        $this->routes = [];
    }
    
    /**
     * Find a route which matches a request
     * 
     * @param Request $request The HTTP request to match with
     * @param User    $user    The User which is responsible for the request
     * 
     * @return array The data about the round which has been found
     * 
     * @throws MethodNotAllowedException If a route has been found but than the HTTP method does not match
     * @throws RouteNotFoundException If no route has been found
     * @throws AccessDeniedException If a route has been found but than the User has no access to the route
     */
    public function match(Request $request, User $user)
    {
        $allowedMethod = [];
        $roleNotAllowed = false;

        if ($request->hasAttribute('_controller')) {
            return [];
        }
        
        $path = $request->getPathInfo();
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $name => $route) {
            $cRoute = $route->compile();
            if ('' !== $cRoute->getStaticPrefix()
                && 0 !== strpos($path, $cRoute->getStaticPrefix())
            ) {
                continue;
            }

            if (!preg_match($cRoute->getRegex(), $path, $matches)) {
                continue;
            }

            $hostMatches = [];
            if ($cRoute->getHostRegex() &&
                !preg_match(
                    $cRoute->getHostRegex(),
                    $request->getHttpHost(),
                    $hostMatches
                )
            ) {
                continue;
            }

            if ($req = $route->getRequirement('_method')) {
                if ('HEAD' === ($method = $request->getMethod())) {
                    $method = 'GET';
                }

                if (!in_array($method, $req = explode('|', strtoupper($req)))) {
                    $allowedMethod = array_merge($allowedMethod, $req);
                    continue;
                }
            }

            if ($roles = $route->getRequirement('_roles')) {
                $roleOk = false;
                foreach (explode('|', $roles) as $role) {
                    if ($roleOk = $user->hasRole($role)) {
                        break;
                    }
                }
                if (!$roleOk) {
                    $roleNotAllowed = true;
                    continue;
                }
            }

            return array_merge(
                $route->getDefaults(),
                array_replace($matches, $hostMatches),
                array('_route' => $name)
            );
        }

        if (!empty($allowedMethod)) {
            throw new MethodNotAllowedException(
                $allowedMethod,
                sprintf("Route %s doesn't allow %s HTTP method", $name, $method)
            );
        }

        if ($roleNotAllowed) {
            throw new AccessDeniedException(
                sprintf("Current user doesn't have sufficient role for route %s", $name)
            );
        }

        throw new RouteNotFoundException(
            sprintf("No route found for request %s", $path)
        );
    }
    
    /**
     * Generate a url
     *
     * @param Request $request       The HTTP request which will be used
     * @param string  $name          The name of the route which will be used
     * @param array   $parameters    The parameters for the route
     * @param int     $referenceType Determine the type of url
     * 
     * @return string
     *
     * @throws RouteNotFoundException If the route doesn't exist
     */    
    public function generateUrl(
        Request $request, $name, $parameters = array(), int $referenceType = self::ABSOLUTE_PATH
    ): string {
        if (!isset($this->routes[$name])) {
            throw new RouteNotFoundException('Route non définie "' . $name . '"');
        }
        
        $route         = $this->routes[$name];
        $compiledRoute = $route->compile();
        
        return $this->doGenerate(
            $compiledRoute->getVariables(), 
            $route->getDefaults(), 
            $route->getRequirements(), 
            $compiledRoute->getTokens(), 
            $parameters, 
            $name, 
            $referenceType, 
            $compiledRoute->getHostTokens(),
            $request
        );
    }

    /**
     *
     * Generate an url
     *
     * @author Symfony Symfony\vendor\symfony\symfony\src\Symfony\Component\Routing\Generator
     *
     * @param array   $variables
     * @param array   $defaults
     * @param array   $requirements
     * @param array   $tokens
     * @param array   $parameters
     * @param string  $name
     * @param int     $referenceType
     * @param array   $hostTokens
     * @param Request $request
     *
     * @return string
     *
     * @throws MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
     *                                             are missing
     * @throws InvalidParameterException When a parameter value for a placeholder is not correct because it does not
     *                                   match the requirement
     */
    private function doGenerate(
        array $variables, array $defaults, array $requirements, array $tokens, array $parameters,
        string $name, int $referenceType, array $hostTokens, Request $request
    ) {
        $variables = array_flip($variables);
        $mergedParams = array_replace($defaults, $request->getAllAttributes(), $parameters);
        
        // all params must be given
        if ($diff = array_diff_key($variables, $mergedParams)) {
            throw new MissingMandatoryParametersException(
                sprintf(
                    'Some mandatory parameters are missing ("%s") to generate a URL for route "%s".',
                    implode('", "', array_keys($diff)),
                    $name
                )
            );
        }
        
        $url = '';
        $optional = true;
        foreach ($tokens as $token) {
            if ('variable' === $token[0]) {
                if (!$optional || !array_key_exists($token[3], $defaults) || null !== $mergedParams[$token[3]]
                    && (string)$mergedParams[$token[3]] !== (string)$defaults[$token[3]]
                ) {
                    // check requirement
                    if (!preg_match('#^'.$token[2].'$#', $mergedParams[$token[3]])) {
                        throw new InvalidParameterException(
                            sprintf(
                                'Parameter "%s" for route "%s" must match "%s" ("%s" given) to generate a corresponding URL.',
                                $token[3],
                                $name,
                                $token[2],
                                $mergedParams[$token[3]]
                            )
                        );
                    }

                    $url = $token[1] . $mergedParams[$token[3]] . $url;
                    $optional = false;
                }
            } else {
                // static text
                $url = $token[1] . $url;
                $optional = false;
            }
        }

        if ('' === $url) {
            $url = '/';
        }

        // the contexts base url is already encoded (see Symfony\Component\HttpFoundation\Request)
        $url = strtr(rawurlencode($url), self::DECODED_CHARS);

        // the path segments "." and ".." are interpreted as relative reference when resolving a URI; see http://tools.ietf.org/html/rfc3986#section-3.3
        // so we need to encode them as they are not used for this purpose here
        // otherwise we would generate a URI that, when followed by a user agent (e.g. browser), does not match this route
        $url = strtr($url, ['/../' => '/%2E%2E/', '/./' => '/%2E/']);
        if ('/..' === substr($url, -3)) {
            $url = substr($url, 0, -2).'%2E%2E';
        } elseif ('/.' === substr($url, -2)) {
            $url = substr($url, 0, -1).'%2E';
        }
        
        $schemeAuthority = '';
        if ($host = $request->getHost()) {
            $scheme = $request->isSafe() ? 'https' : 'http';
            if (isset($requirements['_scheme']) && ($req = strtolower($requirements['_scheme'])) && $scheme !== $req) {
                $referenceType = self::ABSOLUTE_URL;
                $scheme = $req;
            }

            if ($hostTokens) {
                $routeHost = '';
                foreach ($hostTokens as $token) {
                    if ('variable' === $token[0]) {
                        if (!preg_match('#^'.$token[2].'$#', $mergedParams[$token[3]])) {
                            throw new InvalidParameterException(
                                sprintf(
                                    'Parameter "%s" for route "%s" must match "%s" ("%s" given) to generate a corresponding URL.',
                                    $token[3],
                                    $name,
                                    $token[2],
                                    $mergedParams[$token[3]]
                                )
                            );
                        }

                        $routeHost = $token[1] . $mergedParams[$token[3]] . $routeHost;
                    } else {
                        $routeHost = $token[1] . $routeHost;
                    }
                }

                if ($routeHost !== $host) {
                    $host = $routeHost;
                    if (self::ABSOLUTE_URL !== $referenceType) {
                        $referenceType = self::NETWORK_PATH;
                    }
                }
            }

            if (self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType) {
                $port = '';
                if(('http' === $scheme && 80 != $request->getHttpPort())
                    || ('https' === $scheme && 443 != $request->getHttpPort()))
                {
                    $port = ':' . $request->getHttpPort();
                }
                
                $schemeAuthority = self::NETWORK_PATH === $referenceType ? '//' : "$scheme://";
                $schemeAuthority .= $host . $port;
            }
        }

        if (self::RELATIVE_PATH === $referenceType) {
            $url = self::getRelativePath($request->getPathInfo(), $url);
        } else {
            $url = $schemeAuthority.rtrim($request->getBaseUrl(), '/').$url;
        }

        // add a query string if needed
        $extra = array_diff_key($parameters, $variables, $defaults);
        if ($extra && $query = http_build_query($extra, '', '&')) {
            $url .= '?'.$query;
        }
        
        return $url;
    }
    
    /**
     * Returns the target path as relative reference from the base path.
     *
     * Only the URIs path component (no schema, host etc.) is relevant and must be given, starting with a slash.
     * Both paths must be absolute and not contain relative parts.
     * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
     * Furthermore, they can be used to reduce the link size in documents.
     *
     * Example target paths, given a base path of "/a/b/c/d":
     * - "/a/b/c/d"     -> ""
     * - "/a/b/c/"      -> "./"
     * - "/a/b/"        -> "../"
     * - "/a/b/c/other" -> "other"
     * - "/a/x/y"       -> "../../x/y"
     *
     * @param string $basePath   The base path
     * @param string $targetPath The target path
     *
     * @return string The relative target path
     */
    public static function getRelativePath(string $basePath, string $targetPath): string
    {
        if ($basePath === $targetPath) {
            return '';
        }

        $sourceDirs = explode('/', isset($basePath[0]) && '/' === $basePath[0] ? substr($basePath, 1) : $basePath);
        $targetDirs = explode('/', isset($targetPath[0]) && '/' === $targetPath[0] ? substr($targetPath, 1) : $targetPath);
        array_pop($sourceDirs);
        $targetFile = array_pop($targetDirs);

        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }

        $targetDirs[] = $targetFile;
        $path = str_repeat('../', count($sourceDirs)).implode('/', $targetDirs);

        // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
        // (see http://tools.ietf.org/html/rfc3986#section-4.2).
        return '' === $path || '/' === $path[0]
            || false !== ($colonPos = strpos($path, ':')) && ($colonPos < ($slashPos = strpos($path, '/')) || false === $slashPos)
            ? "./$path" : $path;
    }
}
