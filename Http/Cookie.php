<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE-SYMFONY
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Lwf\Http;

use Lwf\Http\Exception\InvalidArgumentException;

/**
 * Represents a cookie
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @api
 */
class Cookie
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var null|string
     */
    protected $value;
    /**
     * @var null|string
     */
    protected $domain;
    /**
     * @var int
     */
    protected $expire;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var bool
     */
    protected $secure;
    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * Constructor.
     *
     * @param string                    $name       The name of the cookie
     * @param string                    $value      The value of the cookie
     * @param integer|string|\DateTime  $expire     The time the cookie expires
     * @param string                    $path       The path on the server in which the cookie will be available on
     * @param string                    $domain     The domain that the cookie is available to
     * @param Boolean                   $secure     Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param Boolean                   $httpOnly   Whether the cookie will be made accessible only through the HTTP protocol
     *
     * @throws InvalidArgumentException Si le nom du cookie contient des caractères invalides
     * @throws InvalidArgumentException Si le nom du cookie est vide
     * @throws InvalidArgumentException Si l'expiration du cookie est invalide
     *
     * @api
     */
    public function __construct(
        string $name, string $value = null, $expire = 0, string $path = '/', string $domain = null,
        bool $secure = false, bool $httpOnly = true)
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }

        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTime) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if (false === $expire || -1 === $expire) {
                throw new InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = $expire;
        $this->path = empty($path) ? '/' : $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = urlencode($this->getName()).'=';

        if ('' === (string) $this->getValue()) {
            $str .= 'deleted; expires='.gmdate("D, d-M-Y H:i:s T", time() - 31536001);
        } else {
            $str .= urlencode($this->getValue());

            if ($this->getExpiresTime() !== 0) {
                $str .= '; expires='.gmdate("D, d-M-Y H:i:s T", $this->getExpiresTime());
            }
        }

        if ('/' !== $this->path) {
            $str .= '; path='.$this->path;
        }

        if (null !== $this->getDomain()) {
            $str .= '; domain='.$this->getDomain();
        }

        if (true === $this->isSecure()) {
            $str .= '; secure';
        }

        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }

        return $str;
    }

    /**
     * Gets the name of the cookie.
     *
     * @return string
     *
     * @api
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     *
     * @return string
     *
     * @api
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the domain that the cookie is available to.
     *
     * @return string
     *
     * @api
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Gets the time the cookie expires.
     *
     * @return int
     *
     * @api
     */
    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     *
     * @return string
     *
     * @api
     */
    public function getPath():string
    {
        return $this->path;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     *
     * @return bool
     *
     * @api
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @return bool
     *
     * @api
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Whether this cookie is about to be cleared
     *
     * @return Boolean
     *
     * @api
     */
    public function isCleared(): bool
    {
        return $this->expire < time();
    }
}
