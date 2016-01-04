<?php

declare(strict_types=1);

namespace Lwf\Security\Authenticator;

/**
 * Authenticate an user whith a LDAP
 */
class ActiveDirectoryAuthenticator extends Authenticator
{
    /** @var string */
    private $server;
    /** @var string  */
    private $domain;
    
    /**
     * Constructor
     * 
     * @param string $server The hostname or the IP address of the LDAP server
     * @param string $domain The domain name
     */
    public function __construct(string $server, string $domain)
    {
        $this->server = $server;
        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(string $username, string $password): bool
    {
        $ldap = \ldap_connect($this->server);
        return @\ldap_bind(
            $ldap,
            $username . '@' . $this->domain,
            $password
        );
    }
}
