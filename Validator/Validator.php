<?php

declare(strict_types = 1);

namespace Lwf\Validator;

/**
 * Allow to validate data
 */
abstract class Validator
{
    /** @var mixed[] */
    protected $errors;

    /**
     * Helper for check() method
     *
     * @param mixed $value The data
     */
    protected abstract function rawCheck($value);

    /**
     * Tell if the last validation raised an error
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Get the errors raised by the last validation
     *
     * @return mixed[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if the data follow the rule
     *
     * @param mixed $value The data
     *
     * @return Validator The current Validator instance
     */
    public function check($value): Validator
    {
        $this->errors = [];
        $this->rawCheck($value);
        return $this;
    }
}
