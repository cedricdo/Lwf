<?php

declare(strict_types=1);

namespace Lwf\Http;

use Lwf\Http\Exception\OutOfBoundsException;
use Lwf\Http\Exception\RuntimeException;

/**
 * Represents an HTTP response.
 */
class Response
{
    /** @var string[]  */
    const STATUS_TEXTS = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];
    /** @var string[] */
    private $headers;
    /** @var Cookie[]  */
    private $cookies;
    /** @var string */
    private $body;
    /** @var  int */
    private $code;
    /** @var string  */
    private $protocolVersion;
    /** @var string  */
    private $charset;

    /**
     * Constructor.
     *
     * @param string   $body    The body of the response.
     * @param int      $code    The HTTP code of the response.
     * @param string[] $headers The HTTP headers
     */
    public function __construct(string $body = '', int $code = 200, array $headers = [])
    {
        $this->body = $body;
        $this->setCode($code);
        $this->headers = $headers;
        $this->protocolVersion = '1.1';
        $this->charset = 'UTF-8';
        $this->cookies = [];
    }
    
    /**
     * Get the HTTP code of the response
     * 
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }
    
    /**
     * Set the HTTP code of the response
     * 
     * @param int $code The HTTP code
     * 
     * @throws OutOfBoundsException Si le code Http n'est pas valide.
     */
    public function setCode(int $code)
    {
        if (!array_key_exists($code, self::STATUS_TEXTS)) {
            throw new OutOfBoundsException(sprintf("%d is not a valid HTTP code", $code));
        }
        
        $this->code = $code;
    }
    
    /**
     * Add a cookie to the response
     * 
     * @param Cookie $cookie
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }
    
    /**
     * Remove a cookie from the client
     *
     * @param string $name   The name of the cokkie.
     * @param string $path   The path of the cookie
     * @param string $domain The domain of the cookie.
     *
     * @throws RuntimeException If the HTTP headers has already been sent
     */
    public function removeCookie(string $name, string $path = '/', string $domain = null)
    {
        if (headers_sent()) {
            throw new RuntimeException("Headers have already been sent");
        }

        unset($this->cookies[$name]);
        setcookie($name, null, 1, $path, $domain);
    }

    /**
     * Set the body of the response
     * 
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }
    
    /**
     * Get the body of the response
     * 
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Append data to the body of the response
     * 
     * @param string $data
     */
    public function addBody(string $data)
    {
        $this->body .= $data;
    }

    /**
     * Add or modify a HTTP header
     *
     * @param string $name  The name of the header
     * @param string $value The value of the header
     */
    public function addHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Remove a HTTP header
     *
     * @param string $name The name of the header
     *
     * @throws OutOfBoundsException If the header does not exist
     */
    public function removeHeader(string $name)
    {
        if (!isset($this->headers[$name])) {
            throw new OutOfBoundsException(sprintf("Header %s doesn't exists", $name));
        }

        unset($this->headers[$name]);
    }

    /**
     * Get a HTTP header
     *
     * @param string $name The name of the header
     *
     * @return string
     *
     * @throws OutOfBoundsException If the header does not exist
     */
    public function getHeader(string $name): string
    {
        if (!isset($this->headers[$name])) {
            throw new OutOfBoundsException(sprintf("Header %s doesn't exists", $name));
        }

        return $this->headers[$name];
    }
    
    /**
     * Remove every HTTP headers
     */
    public function clearHeaders()
    {
        $this->headers = [];
    }
    
    /**
     * Set the HTTP version protocol of the response
     * 
     * @param string $protocolVersion
     */
    public function setProtocolVersion(string $protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
    }
    
    /**
     * Get the HTTP version protocol of the response
     * 
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }
    
    /**
     * Get the charset of the response
     * 
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }
    
    /**
     * Set the charset of the response
     * 
     * @param string $charset
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }
    
    /**
     * Send the headers of the response to the client.
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        header(sprintf(
            'HTTP/%s %s %s',
            $this->protocolVersion,
            $this->code,
            self::STATUS_TEXTS[$this->code]
        ));

        if (!isset($this->headers['Content-Type'])) {
            $this->addHeader(
                'Content-Type',
                'text/html; charset=' . $this->charset
            );
        } elseif ('text/' === substr($this->headers['Content-Type'], 0, 5)
            && false === strpos($this->headers['Content-Type'], 'charset')
        ) {
            $this->addHeader(
                'Content-Type',
                $this->headers['Content-Type'] . '; charset=' . $charset
            );
        }

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, false);
        }

        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }
    
    /**
     * Send the body of the response to the client
     */
    public function sendBody()
    {
        echo $this->body;
    }
    
    /**
     * Send the response to the client
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();
    }
}
