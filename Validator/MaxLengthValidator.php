<?php

declare(strict_types = 1);

namespace Lwf\Validator;
use Lwf\Validator\Exception\RangeException;

/**
 * Check if a string has a specific length
 */
class MaxLengthValidator extends Validator
{
    /** @var  int */
    private $max;

    /**
     * Constructor
     *
     * @param int $max The maximum length of the string
     */
    public function __construct(int $max)
    {
        if ($max <= 0) {
            throw new RangeException(sprintf("max length connect be lesser than 1, %d provided", $max));
        }

        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        if ($this->max < strlen($value)) {
            $this->errors[] = 'MaxLengthValidator';
        }
    }
}
