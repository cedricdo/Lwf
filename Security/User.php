<?php

declare(strict_types=1);

namespace Lwf\Security;
use Lwf\Security\Exception\OutOfBoundsException;

/**
 * Represent an User
 */
class User 
{
    /** User has not been authenticated */
    const NOT_AUTHENTICATED = 0;
    /** User has been authenticated trough cookie */
    const AUTHENTICATED = 1;
    /** User has been authenticated trough form */
    const FULLY_AUTHENTICATED = 2;

    /** @var string  */
    private $login;
    /** @var string  */
    private $pass;
    /** @var string[]  */
    private $roles;
    /** @var int  */
    private $authenticated;

    /**
     * Constructor
     *
     * @param string $login User's login
     * @param string $pass  User's passworf
     * @param array  $roles List of user's roles
     */
    public function __construct(string $login = null, string $pass = null, array $roles = [])
    {
        $this->login = $login;
        $this->pass = $pass;
        $this->roles = array_flip($roles);
        $this->authenticated = self::NOT_AUTHENTICATED;
    }
    
    /**
     * Test if a user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated != self::NOT_AUTHENTICATED;
    }

    /**
     * Set the user authentication state
     * 
     * @param int $authenticated
     *
     * @throws OutOfBoundsException If the autentication state provided is invalid
     */
    public function setAuthenticated(int $authenticated)
    {
        if ($authenticated < self::NOT_AUTHENTICATED || $authenticated > self::FULLY_AUTHENTICATED) {
            throw new OutOfBoundsException(sprintf("Invalid authentication state %d", $authenticated));
        }
        $this->authenticated = $authenticated;
    }

    /**
     * Get the user's login
     * 
     * @return mixed Null if the user's login has not been defined
     */
    public function getLogin()
    {
        return $this->login;
    }
    
    /**
     * Set the user's login
     * 
     * @param string $login The user's login
     */
    public function setLogin(string $login)
    {
        $this->login = trim($login);
    }
    
    /**
     * Get the user's password
     * 
     * @return mixed Null if the user's password has not been defined
     */
    public function getPass()
    {
        return $this->pass;
    }
    
    /**
     * Set the user's password
     * 
     * @param string $pass The user's password
     */
    public function setPass(string $pass)
    {
        $this->pass = $pass;
    }
    
    /**
     * Set the user's roles
     * 
     * @param string[] $roles The user's role
     */
    public function setRoles(array $roles)
    {
        $this->clearRole();
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }
    
    /**
     * Add a role to the user
     * 
     * @param string $role The role
     */
    public function addRole(string $role)
    {
        $this->roles[$role] = 1;
    }
    
    /**
     * Remove a user role
     * 
     * @param string $role The role to remove
     */
    public function removeRole(string $role)
    {
        if (!isset($this->roles[$role])) {
            throw new OutOfBoundsException(sprintf("The user doesn't have role %s", $role));
        }

        unset($this->roles[$role]);
    }
    
    /**
     * Get the user's role
     * 
     * @return string[]
     */
    public function getRoles(): array
    {
        return array_keys($this->roles);
    }
    
    /**
     * Test if a user has a role
     * 
     * @param string $role The role you want to check
     *
     * @return bool 
     */
    public function hasRole(string $role): bool
    {
        return isset($this->roles[$role]);
    }
    
    /**
     * Remove every role of the user
     */
    public function clearRole()
    {
        $this->roles = [];
    }
}
