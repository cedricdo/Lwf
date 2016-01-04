<?php

declare(strict_types=1);

namespace Lwf\Validator\Web;

use Lwf\Validator\Validator;
use Lwf\Validator\NotEmptyValidator;

/**
 * Provide a convenient way of checking is a form is valid
 *
 * The validation is done with a set of validator which are run against a set of value
 */
class FormValidator extends Validator
{
    /** @var array[]  */
    private $rules;
    /** @var array */
    private $fields;
    
    /**
     * Constructor
     * 
     * @param array $required The mandatory fields of the form
     * @param array $optional The optionals fields of the form
     * @param array $rules    The rules wich will be used to validate the form
     */
    public function __construct(array $required = [], array $optional = [], array $rules = [])
    {
        $this->fields = array();

        if ($required) {
            $this->fields += array_combine(
                $required, array_fill(0, count($required), true)
            );
        }
        if ($optional) {
            $this->fields += array_combine(
                $optional, array_fill(0, count($optional), false)
            );
        }
        
        $this->rules = $rules;
    }
    
    /**
     * Add an optional field
     * 
     * @param string $field The name of the field
     */
    public function addOptionalField(string $field)
    {
        $this->fields[$field] = false;
    }
    
    /**
     * Add a required field
     *
     * @param string $field The name of the field
     */
    public function addRequiredField(string $field)
    {
        $this->fields[$field] = true;
    }
    
    /**
     * Add a rule
     *
     * @param Validator $validator The Validator object of the rule
     * @param string[]  $fields    The fields which will be validated
     */
    public function addRule(Validator $validator, array $fields)
    {
        $rule = array($validator, $fields);
        if (!array_search($rule, $this->rules)) {
            $this->rules[] = $rule;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        $skip = [];
        $notEmptyValidator = new NotEmptyValidator;

        foreach ($this->fields as $field => $required) {
            if (!isset($value[$field]) && $required) {
                $this->errors[$field][] = 'MissingValidator';
                $skip[$field] = 1;
            } elseif (isset($value[$field])) {
                if ($notEmptyValidator->check($value[$field])->hasError()) {
                    $skip[$field] = 1;
                    if ($required) {
                        $err = $notEmptyValidator->getErrors();
                        $this->errors[$field][] = $err[0];
                    }
                }
            } else {
                $skip[$field] = 1;
            }
        }

        foreach ($this->rules as $rule) {
            list($validator, $fields) = $rule;
            foreach ($fields as $key => $field) {
                if (!is_array($field)) {
                    $index = $field;
                    @$val = $value[$field];
                } else {
                    $index = $key;
                    $val = [];
                    foreach ($field as $f) {
                        $val[] = $value[$f];
                    }
                }

                if (isset($skip[$index])) {
                    continue;
                }

                if ($validator->check($val)->hasError()) {
                    foreach ($validator->getErrors() as $error) {
                        $this->errors[$index][] = $error;
                        $skip[$index] = true;
                    }
                }
            }
        }
    }
}
