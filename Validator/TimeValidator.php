<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string is a "time", a string with format xx:xx with x a digit
 */
class TimeValidator extends Validator 
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!preg_match('`\d{1,2}:\d{1,2}`i', $value)) {
            $this->errors[] = 'TimeValidator';
        }
    }
}
