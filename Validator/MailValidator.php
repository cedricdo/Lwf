<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string is a valid e-mail address
 */
class MailValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'MailValidator';
        }
    }
}
