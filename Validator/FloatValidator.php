<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string is a valid float number
 */
class FloatValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!ctype_digit(str_replace(['.', ','], '', $value))) {
            $this->errors[] = 'FloatValidator';
        }
    }
}
