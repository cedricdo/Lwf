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
    /** @var array */
    private $confirms;
    
    /**
     * Constructor
     * 
     * @param array $required The mandatory fields of the form
     * @param array $optional The optionals fields of the form
     * @param array $confirm  A set of field which have to be confirmed by other fields
     * @param array $rules    The rules wich will be used to validate the form
     */
    public function __construct(array $required = [], array $optional = [], array $confirm = [], array $rules = [])
    {
        $this->fields = [];

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

        $this->confirms = $confirm;
        $this->rules = $rules;
    }

    /**
     * Add a field which has to be confirmed
     *
     * The confirmation will be validated if another field with the same value exist
     * By default, the name of the confirmation field is the same as the original field with _confirm at the end
     * If you want to override this, provide the parameter $fieldConfirmName
     *
     * @param string $field            The name of the field which need a confirmation
     * @param string $fieldConfirmName The name of the confirmation field
     */
    public function addConfirmField(string $field, string $fieldConfirmName = '')
    {
        $this->confirms[$field] = $fieldConfirmName;
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
        $rule = [$validator, $fields];
        if (!array_search($rule, $this->rules)) {
            $this->rules[] = $rule;
        }
    }

    /**
     * Check if the required field are provided and not empty
     *
     * @param array $value The values to test
     *
     * @return array The field which can be skipped on further validation
     */
    private function checkRequired(array $value): array
    {
        $skip = [];
        $notEmptyValidator = new NotEmptyValidator;

        foreach ($this->fields as $field => $required) {
            if (!isset($value[$field]) && $required) {
                $this->errors[$field][] = 'RequiredValidator';
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

        return $skip;
    }

    /**
     * Check if the confirm field are correct
     *
     * @param array $value The values to test
     */
    private function checkConfirm(array $value)
    {
        foreach ($this->confirms as $fieldName => $confirmFieldName) {
            if (empty($confirmFieldName)) {
                $confirmFieldName = $fieldName . '_confirm';
            }
            if (!isset($value[$confirmFieldName]) || $value[$fieldName] != $value[$confirmFieldName]) {
                $this->errors[$fieldName][] = 'ConfirmValidator';
            }
        }
    }

    /**
     * Check if the fields values follow the validators rules
     *
     * @param array $skip  The field which can be skipped because they're required but not provided
     * @param array $value The values to test
     */
    private function checkValidators(array $skip, array $value)
    {
        foreach ($this->rules as $rule) {
            /** @var Validator $validator */
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

    /**
     * {@inheritdoc}
     */
    protected function rawCheck($value)
    {
        $skip = $this->checkRequired($value);
        $this->checkConfirm($value);
        $this->checkValidators($skip, $value);
    }
}
