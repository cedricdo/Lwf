<?php

declare(strict_types=1);

namespace Lwf\Routing;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CompiledRoutes are returned by the RouteCompiler class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CompiledRoute
{
    /** @var array  */
    private $variables;
    /** @var array  */
    private $tokens;
    /**  @var string */
    private $staticPrefix;
    /** @var string */
    private $regex;
    /** @var array  */
    private $pathVariables;
    /** @var array  */
    private $hostVariables;
    /** @var string  */
    private $hostRegex;
    /** @var array  */
    private $hostTokens;

    /**
     * Constructor.
     *
     * @param string      $staticPrefix       The static prefix of the compiled route
     * @param string      $regex              The regular expression to use to match this route
     * @param array       $tokens             An array of tokens to use to generate URL for this route
     * @param array       $pathVariables      An array of path variables
     * @param string|null $hostRegex          Host regex
     * @param array       $hostTokens         Host tokens
     * @param array       $hostVariables      An array of host variables
     * @param array       $variables          An array of variables (variables defined in the path and in the host patterns)
     */
    public function __construct(
        string $staticPrefix, string $regex, array $tokens, array $pathVariables, string $hostRegex = null,
        array $hostTokens = array(), array $hostVariables = array(), array $variables = array()
    ) {
        $this->staticPrefix = $staticPrefix;
        $this->regex = $regex;
        $this->tokens = $tokens;
        $this->pathVariables = $pathVariables;
        $this->hostRegex = $hostRegex;
        $this->hostTokens = $hostTokens;
        $this->hostVariables = $hostVariables;
        $this->variables = $variables;
    }

    /**
     * Returns the static prefix.
     *
     * @return string The static prefix
     */
    public function getStaticPrefix(): string
    {
        return $this->staticPrefix;
    }

    /**
     * Returns the regex.
     *
     * @return string The regex
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * Returns the host regex
     *
     * @return string|null The host regex or null
     */
    public function getHostRegex()
    {
        return $this->hostRegex;
    }

    /**
     * Returns the tokens.
     *
     * @return array The tokens
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Returns the host tokens.
     *
     * @return array The tokens
     */
    public function getHostTokens(): array
    {
        return $this->hostTokens;
    }

    /**
     * Returns the variables.
     *
     * @return array The variables
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Returns the path variables.
     *
     * @return array The variables
     */
    public function getPathVariables(): array
    {
        return $this->pathVariables;
    }

    /**
     * Returns the host variables.
     *
     * @return array The variables
     */
    public function getHostVariables(): array
    {
        return $this->hostVariables;
    }

}
