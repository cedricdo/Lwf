<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a value is not empty
 */
class NotEmptyValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (trim($value) == '') {
            $this->errors[] = 'NotEmptyValidator';
        }
    }
}
