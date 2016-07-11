<?php

declare(strict_types=1);

namespace Lwf\Http;

use Lwf\Http\Exception\OutOfBoundsException;
use Lwf\Http\Exception\RuntimeException;

/**
 * Represents an HTTP request.
 */
class Request
{
    /** @var  mixed */
    private $default;
    /** @var mixed[]  */
    private $attributes;
    /** @var string */
    private $pathInfo;
    /** @var string */
    private $baseUrl;
    /** @var string[] */
    const ALLOWED_ARRAYS = ['_POST', '_GET', '_COOKIE', '_SERVER', '_ENV'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDefault(null);
        $this->attributes = [];
        $this->computePathInfo();
        $this->computeBaseUrl();
    }
    
    /**
     * Get the default value which will be returned if a parameter does not exist.
     * 
     * @return mixed 
     */
    public function getDefault()
    {
        return $this->default;
    }
    
    /**
     * Set the default value which will be returned if a parameter does not exist.
     * 
     * @param mixed $value The default value
     */
    public function setDefault($value)
    {
        $this->default = $value;
    }
    
    /**
     * Test if a request is an ajax request.
     * 
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->getHeader('X-Requested-With');
    }

    /**
     * Get every attributes.
     *
     * @return mixed[]
     */
    public function getAllAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Get an attribute.
     * 
     * @param string $key The key of the attribute
     * 
     * @return mixed The value of the attribute or the default value if the attribute does not exist
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? $this->default;
    }

    /**
     * Add an attribute.
     *
     * @param string $key   The key of the attribute
     * @param mixed  $value The value of the attribute
     */
    public function addAttribute(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Test the existence of an attribute.
     *
     * @param string $key The key of the attribute.
     *
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Add multiple attributes.
     * 
     * @param mixed[] $attributes List of the attributes to add.
     */
    public function addAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Get every parameters from _GET
     *
     * @return mixed[]
     */
    public function getAllQuery(): array
    {
        return $_GET;
    }
    
    /**
     * Get a parameter from _GET
     * 
     * @param string $key The key of the parameter.
     * 
     * @return mixed The value of the parameter or the default value if the parameter does not exist
     */
    public function getQuery(string $key)
    {
        return $this->get('_GET', $key);
    }
    
    /**
     * Test if a parameter exist in _GET.
     * 
     * @param string $key The key of the parameter
     * 
     * @return bool
     */
    public function hasQuery(string $key): bool
    {
        return $this->has('_GET', $key);
    }

    /**
     * Get every parameters from _POST
     *
     * @return array
     */
    public function getAllPost(): array
    {
        return $_POST;
    }
    
    /**
     * Get a parameter from _POST
     *
     * @param string $key The key of the parameter
     *
     * @return mixed The value of the parameter or the default value if the parameter does not exist
     */
    public function getPost(string $key)
    {
        return $this->get('_POST', $key);
    }
    
    /**
     * Test if a parameter exist in _POST
     * 
     * @param string $key The key of the parameter
     * 
     * @return bool
     */
    public function hasPost(string $key): bool
    {
        return $this->has('_POST', $key);
    }

    /**
     * Get every parameters in _COOKIE
     *
     * @return string[]
     */
    public function getAllCookies(): array
    {
        return $_COOKIE;
    }

    /**
     * Get a parameter from _COOKIE
     *
     * @param string $key The key of the parameter.
     *
     * @return mixed The value of the parameter or the default value if the parameter does not exist
     */
    public function getCookie(string $key)
    {
        return $this->get('_COOKIE', $key);
    }
    
    /**
     * Test if a parameter exists in _COOKIE
     * 
     * @param string $key The key of the parameter
     * 
     * @return bool
     */
    public function hasCookie($key): bool
    {
        return $this->has('_COOKIE', $key);
    }

    /**
     * Get every parameters in _SERVER
     *
     * @return mixed[]
     */
    public function getAllServer(): array
    {
        return $_SERVER;
    }


    /**
     * Get a parameter in _SERVER
     *
     * @param string $key The key of the parameters
     *
     * @return mixed The value of the parameter or the default value if the parameter does not exist
     */
    public function getServer(string $key)
    {
        return $this->get('_SERVER', $key);
    }
    
    /**
     * Test if a parameter exists in _SERVER
     * 
     * @param string $key The key of the parameter
     * 
     * @return bool
     */
    public function hasServer(string $key): bool
    {
        return $this->has('_SERVER', $key);
    }

    /**
     * Get every parameters from _ENV
     *
     * @return mixed[]
     */
    public function getAllEnv(): array
    {
        return $_ENV;
    }

    /**
     * Get a parameter from _ENV
     *
     * @param string $key The key of the parameter
     *
     * @return mixed The value of the parameter or the default value if the parameter does not exist
     */
    public function getEnv(string $key)
    {
        return $this->get('_ENV', $key);
    }
    
    /**
     * Test if a parameter exists in _ENV
     * 
     * @param string $key The key of the parameter
     * 
     * @return bool
     */
    public function hasEnv($key): bool
    {
        return $this->has('_ENV', $key);
    }
    
    /**
     * Get a HTTP header of the request
     *
     * A - can be used int the name of the header instead of _
     * 
     * @param string $header The name of the HTTP header without the leading "HTTP_"
     * 
     * @return mixed The header or the default value if the header does not exist
     */
    public function getHeader(string $header)
    {
        $httpHeader = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        return $this->get('_SERVER', $httpHeader);
    }

    /**
      * Test if the request is HTTPS
      *
      * @return bool
      */
    public function isSafe(): bool
    {
        return $this->getServer('HTTPS') == 'on';
    }

    /**
    * Get the port which received the request
    * 
    * @return int
    */
    public function getHttpPort(): int
    {
        return (int)$this->getServer('SERVER_PORT');
    }

    /**
    * Get the HTTP host
    *
    * @return string
    */
    public function getHttpHost(): string
    {
        $host = $this->getServer('HTTP_HOST');

        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->isSafe() ? 'https' : 'http';
        $name   = $this->getServer('SERVER_NAME');
        $port   = $this->getHttpPort();

        if (($scheme == 'http' && $port == 80)
            || ($scheme == 'https' && $port == 443)
        ) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }
    
    /**
     * Get the server host name
     * 
     * @return string
     */
    public function getHost(): string
    {
        return $this->getServer('SERVER_NAME');
    }

    /**
    * Get the IP address of the client who sent the request
    *
    * @param  boolean $checkProxy false if you do not care about proxy
     *
    * @return string
    */
    public function getClientIp(bool $checkProxy = true): string
    {
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != $this->default) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } elseif ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != $this->default) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }

        return $ip;
    }
    
    /**
     * Get the method of the request
     * 
     * @return string 
     */
    public function getMethod(): string
    {
        return $this->getServer('REQUEST_METHOD');
    }
    
    /**
     * Get the pathInfo of the request
     * 
     * @return string 
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }
    
    /**
     * Get the base url of the request
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get a parameter from a superglobal
     *
     * @param string $array The name of the superglobal
     * @param string $key   The key of the parameter
     *
     * @return mixed
     *
     * @throws OutOfBoundsException If the superglobal name is invalid
     * @throws RuntimeException if the key has syntax error
     */
    private function get(string $array, string $key)
    {
        if (!in_array($array, self::ALLOWED_ARRAYS)) {
            throw new OutOfBoundsException(sprintf("%s can not be used", $array));
        }
        if (false === ($p = strpos($key, '['))) {
            return $GLOBALS[$array][$key] ?? $this->getDefault();
        } else {
            if (!preg_match('`^[[:alnum:][\]]+$`', $key)) {
                return $this->default;
            } else {
                $v   = null;
                $str = '$' . $array . '[' . substr($key, 0, $p) . ']' . substr($key, $p);
                try {
                    @eval('$v = ' . $str . ' ?? $this->default;');
                } catch (\ParseError $e) {
                    throw new RuntimeException(sprintf("%s is invalid as array key", $key));
                }

                return $v;
            }
        }
    }

    /**
     * Set a parameter in a superglobal
     *
     * @param string $array The name of the superglobal
     * @param string $key   The key of the parameter
     * @param mixed  $value The value of the parameter
     *
     * @throws OutOfBoundsException If the superglobal name is invalid
     * @throws RuntimeException if the key has syntax error
     */
    private function set(string $array, string $key, $value)
    {
        if (!in_array($array, self::ALLOWED_ARRAYS)) {
            throw new OutOfBoundsException(sprintf("%s can not be used", $array));
        }
        if (false === ($p = strpos($key, '['))) {
            $GLOBALS[$array][$key] = $value;
        } else {
            if (preg_match('`^[[:alnum:][\]]+$`', $key)) {
                try {
                    @eval('$' . $array . '[' . substr($key, 0, $p) . ']' . substr($key, $p) . ' = $value;');
                } catch (\ParseError $e) {
                    throw new RuntimeException(sprintf("%s is invalid as array key", $key));
                }
            }
        }
    }

    /**
     * Remove a parameter from a superglobal
     *
     * @param string $array The name of the superglobal
     * @param string $key   The key of the parameter
     *
     * @throws OutOfBoundsException If the superglobal name is invalid
     * @throws RuntimeException if the key has syntax error
     */
    private function delete(string $array, string $key)
    {
        if (!in_array($array, self::ALLOWED_ARRAYS)) {
            throw new OutOfBoundsException(sprintf("%s can not be used", $array));
        }
        if (false === ($p = strpos($key, '['))) {
            unset($GLOBALS[$array][$key]);
        } else {
            if (preg_match('`^[[:alnum:][\]]+$`', $key)) {
                try {
                    @eval('unset($' . $array . '[' . substr($key, 0, $p) . ']' . substr($key, $p) . ');');
                } catch (\ParseError $e) {
                    throw new RuntimeException(sprintf("%s is invalid as array key", $key));
                }
            }
        }
    }

    /**
     * Test if a parameter exist in a superglobal
     *
     * @param string $array The name of the superglobal
     * @param string $key   The key of the parameter
     *
     * @return bool
     *
     * @throws OutOfBoundsException If the superglobal name is invalid
     * @throws RuntimeException if the key has syntax error
     */
    private function has(string $array, string $key): bool
    {
        if (!in_array($array, self::ALLOWED_ARRAYS)) {
            throw new OutOfBoundsException(sprintf("%s can not be used", $array));
        }
        if (false === ($p = strpos($key, '['))) {
            return isset($GLOBALS[$array][$key]);
        } else {
            if (preg_match('`^[[:alnum:][\]]+$`', $key)) {
                $t = false;
                try {
                    @eval('$t = isset($' . $array . '[' . substr($key, 0, $p) . ']' . substr($key, $p) . ');');
                } catch (\ParseError $e) {
                    throw new RuntimeException(sprintf("%s is invalid as array key", $key));
                }
                return $t;
            } else {
                return false;
            }
        }
    }

    /**
     * Return the user's preferred language
     *
     * @param array $availableLanguages A list of the availables translation of the application
     *
     * @return string
     */
    public function getPreferredLanguage(array $availableLanguages)
    {
        $availableLanguages = array_flip($availableLanguages);

        preg_match_all(
            '~([\w-]+)(?:[^,\d]+([\d.]+))?~',
            strtolower($this->getServer('HTTP_ACCEPT_LANGUAGE')),
            $matches,
            PREG_SET_ORDER
        );

        foreach($matches as $match) {
            list($a, $b) = explode('-', $match[1]) + array('', '');
            $value = isset($match[2]) ? (float) $match[2] : 1.0;

            if(isset($availableLanguages[$match[1]])) {
                $langs[$match[1]] = $value;
                continue;
            }

            if(isset($availableLanguages[$a])) {
                $langs[$a] = $value - 0.1;
            }

        }
        arsort($langs);
        reset($langs);

        return key($langs);
    }

    /**
     * Compute the pathinfo
     */
    private function computePathInfo()
    {
        $pathInfo = $this->getServer('PATH_INFO');
        if ($pathInfo == $this->default) {
            $uri = $this->getServer('REQUEST_URI');
            if ($uri == '/') {
                $pathInfo = $uri;
            } else {
                $dir = dirname($this->getServer('PHP_SELF'));
                $pathInfo = substr($uri, strpos($uri, $dir) + strlen($dir));

                if (false !== ($offset = strpos($pathInfo, '?'))) {
                    $pathInfo = substr($pathInfo, 0, $offset);
                }
            }
        }
        $pathInfo = ltrim($pathInfo, '/');

        if ($pathInfo == 'index.php') {
            $pathInfo = '';
        }

        $this->pathInfo = '/' . $pathInfo;
    }

    /**
     * Compute the base url
     */
    private function computeBaseUrl()
    {
        $pathInfo = $this->getPathInfo();
        if ($pathInfo == '/') {
            $this->baseUrl = $this->getServer('REQUEST_URI');
        } else {
            $this->baseUrl = substr(
                $this->getServer('REQUEST_URI'),
                0,
                strpos($this->getServer('REQUEST_URI'), $this->getPathInfo())
            );
        }

        $this->baseUrl = '/' . trim($this->baseUrl, '/');
    }
}
