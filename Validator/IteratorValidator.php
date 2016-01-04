<?php

declare(strict_types=1);

namespace Lwf\Validator;

/**
 * Validate a collection of data with the same Validator object
 */
class IteratorValidator extends Validator
{
    /** @var Validator  */
    private $validator;
    
    /**
     * Constructor
     * 
     * @param Validator $validator The validator to use
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($values)
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if ($this->validator->check($value)->hasError()) {
                list($error) = $this->validator->getErrors();
                $this->errors[] = $error;
                return;
            }
        }
    }
}
