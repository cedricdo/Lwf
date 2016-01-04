<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if two values are equals
 */
class EqualsValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if ($value[0] !== $value[1]) {
            $this->errors[] = 'EqualsValidator';
        }
    }
}
