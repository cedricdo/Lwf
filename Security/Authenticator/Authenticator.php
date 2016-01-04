<?php

declare(strict_types=1);

namespace Lwf\Security\Authenticator;

/**
 * Represent a service which will allow to authenticate an user
 */
abstract class Authenticator
{
    /**
     * Authenticate an user
     * 
     * @param string $username The user's name
     * @param string $password The user's password
     * 
     * @return bool 
     */
    abstract public function authenticate(string $username, string $password): bool;
}
