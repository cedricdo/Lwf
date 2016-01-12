<?php

declare(strict_types = 1);

namespace Lwf\Validator;
use Lwf\Validator\Exception\RangeException;

/**
 * Check if a string has a specific length
 */
class MinLengthValidator extends Validator
{
    /** @var  int */
    private $min;

    /**
     * Constructor
     *
     * @param int $min The minimum length of the string
     */
    public function __construct(int $min)
    {
        if ($min <= 0) {
            throw new RangeException(sprintf("min length connect be lesser than 1, %d provided", $min));
        }

        $this->min = $min;
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if ($this->min < strlen($value)) {
            $this->errors[] = 'MinLengthValidator';
        }
    }
}
