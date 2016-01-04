<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string is a valid belgian TVA
 */
class BelgianTvaValidator extends Validator 
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!preg_match('`be[[:digit:]]{10}`i', $value)) {
            $this->errors[] = 'BelgianTvaValidator';
        }
    }
}
