<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string contains only digits
 */
class DigitsValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!ctype_digit($value)) {
            $this->errors[] = 'DigitsValidator';
        }
    }
}
