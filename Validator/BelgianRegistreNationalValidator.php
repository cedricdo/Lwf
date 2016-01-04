<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string is a belgian NISS
 */
class BelgianRegistreNationalValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!preg_match('`^(?:\d{2}\.){2}\d{2}-\d{3}\.\d{2}$`', $value)) {
            $this->errors[] = 'BelgianRegistreNationalValidator';
        }
    }
}
