<?php

declare(strict_types=1);

namespace Lwf\Validator;

use Lwf\Validator\Exception\InvalidArgumentException;

/**
 * Check if a value is contained in a collection of allowed values
 */
class WhiteListValidator extends Validator
{
    /** @var \Traversable  */
    private $iterator;
    
    /**
     * Constructor
     * 
     * @param \Traversable $iterator The allowed values
     * 
     * @throws InvalidArgumentException Si la valeur n'est pas itérable.
     */
    public function __construct($iterator)
    {
        if (!is_array($iterator) && !($iterator instanceof \Traversable)) {
            throw new InvalidArgumentException(
                'Parameter has to be an array or a Traversable object'
            );
        }
            
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        foreach ($this->iterator as $element) {
            if ($value == $element) {
                return;
            }
        }

        $this->errors[] = 'WhiteListValidator';
    }
}
