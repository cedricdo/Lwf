<?php

declare(strict_types=1);

namespace Lwf\Security;

/**
 * Root user, hasRole() always returns true
 */
class RootUser extends User
{
    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return true;
    }
}
