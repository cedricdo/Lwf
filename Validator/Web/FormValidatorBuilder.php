<?php

declare(strict_types = 1);

namespace Lwf\Validator\Web;
use Lwf\Validator\Exception\BadMethodCallException;
use Lwf\Validator\Exception\RuntimeException;
use Lwf\Validator\Validator;

/**
 * Provide a convenient way of building a FormValidator object
 */
class FormValidatorBuilder
{
    const VALIDATOR_NAMESPACE = 'Lwf\Validator\\';
    private $rules;
    private $validatorsInstance;

    /**
     * Build a FormValidator object from a set of rules
     *
     * The rules are an associative array, field name to rules.
     * A rules can be a string, a Validator object or an array of those
     * If it's not an array, it'll be wrapped into an array
     * An element of the array can be a name of validator (a string), a validator instance
     * or names of multiples validator separated by a pipe (|)
     * If a name is provided, it can have a parameter, separated by a semi-colon (:)
     *
     * 'mail'
     * ['mail']
     * ['mail|required']
     * ['mail|required', new ValidatorObject]
     * new ValidatorObject
     * 'maxlength:255|belgiantva'
     *
     * @param array $rules The rules which will be enforced by the FormValidator
     *
     * @return FormValidator
     */
    public function getFormValidator(array $rules)
    {
        $requiredFields = [];
        $optionalFields = [];
        $confirmFields = [];
        $this->rules = [];
        $this->validatorsInstance = [];

        foreach ($rules as $fieldName => $validators) {
            $required = false;
            $validatorInstance = $this->getValidators($validators);

            foreach ($validatorInstance as $validator) {
                if ($validator === 'required') {
                    $required = true;
                } elseif (is_string($validator) && false !== strpos($validator, 'confirm')) {
                    $confirmValidator = explode(':', $validator);
                    $confirmFields[$fieldName] = $confirmValidator[1] ?? '';
                } else {
                    if (false === array_search($validator, $this->validatorsInstance)) {
                        $key = count($this->validatorsInstance);
                        $this->validatorsInstance[$key] = $validator;
                        $this->rules[$key] = [$validator, []];
                    }
                    $this->rules[array_search($validator, $this->validatorsInstance)][1][] = $fieldName;
                }
            }

            if ($required) {
                $requiredFields[] = $fieldName;
            } else {
                $optionalFields[] = $fieldName;
            }
        }

        return new FormValidator($requiredFields, $optionalFields, $confirmFields, $this->rules);
    }

    /**
     * Get the validators instance from an array of rules
     *
     * @param mixed $rules The rules
     *
     * @return array an array of validators object. If the validator required has been provided, the string 'required'
     *               will be an element of the returned array
     */
    private function getValidators($rules)
    {
        $return = [];
        if (!is_array($rules)) {
            $rules = [$rules];
        }

        foreach ($rules as $validator) {
            if ($validator instanceof Validator) {
                $return[] = $validator;
            } elseif (is_string($validator)) {
                foreach (explode('|', $validator) as $classInfo) {
                    if ($classInfo == 'required' || false !== strpos($classInfo, 'confirm')) {
                        $return[] = $classInfo;
                    } else {
                        $param = explode(':', $classInfo);
                        $className = self::VALIDATOR_NAMESPACE . $param[0] . 'Validator';
                        if (isset($param[1])) {
                            try {
                                $contructor = (new \ReflectionClass($className))->getConstructor();
                            } catch(\Throwable $e) {
                                throw new RuntimeException(sprintf("Validator class %s does not exist", $param[0]));
                            }
                            if (is_null($contructor)) {
                                throw new BadMethodCallException(
                                    sprintf(
                                        "You've provided a paremeter but class %s does not have constructor",
                                        $className
                                    )
                                );
                            }
                            $parameters = $contructor->getParameters();
                            if (!isset($parameters[0])) {
                                throw new BadMethodCallException(
                                    sprintf(
                                        "You've provided a paremeter but class %s constructor doesn't need",
                                        $className
                                    )
                                );
                            } else {
                                $type = $parameters[0]->getType();
                                if ($type instanceof \ReflectionType) {
                                    switch ($type->__toString()) {
                                        case 'int':
                                            $param[1] = (int)$param[1];
                                            break;
                                        case 'float':
                                            $param[1] = (float)$param[1];
                                            break;
                                        case 'bool':
                                            $param[1] = (bool)$param[1];
                                            break;
                                        case 'string':
                                            $param[1] = (string)$param[1];
                                            break;
                                    }
                                }
                            }
                            $return[] = new $className($param[1]);
                        } else {
                            $return[] = new $className;
                        }
                    }
                }
            }
        }

        return $return;
    }
}
