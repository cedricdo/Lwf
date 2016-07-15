<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Check if a string is a valid date with the format
 */
class DateValidator extends Validator
{
    /** @var string  */
    private $pattern;

    /**
     * Constructor
     *
     * @param string $pattern The regex pattern to extract the day, month and year from the string
     */
    public function __construct(string $pattern = '`^(\d{1,2})/(\d{1,2})/(\d{4})$`')
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if (!preg_match($this->pattern, $value, $matches) || !checkdate(intval($matches[2]), intval($matches[1]), intval($matches[3]))) {
            $this->errors[] = 'DateValidator';
        }
    }
}
