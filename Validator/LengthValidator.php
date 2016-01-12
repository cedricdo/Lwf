<?php

declare(strict_types = 1);

namespace Lwf\Validator;

/**
 * Check if a string has a specific length
 */
class LengthValidator extends Validator
{
    const NO_MAX_LIMIT = 0;
    const NO_MIN_LIMIT = 0;

    /** @var  int */
    private $min;
    /** @var  int */
    private $max;

    /**
     * Constructor
     *
     * @param int $max The maximum length of the string
     * @param int $min The minimum length of the string
     */
    public function __construct(int $max = self::NO_MAX_LIMIT, int $min = self::NO_MIN_LIMIT)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        $length = strlen($value);

        if (
            ($this->max != self::NO_MAX_LIMIT && $length > $this->max) ||
            ($this->min != self::NO_MAX_LIMIT && $length < $this->min)
        ) {
            $this->errors[] = 'LengthValidator';
        }
    }
}
