<?php

namespace App\Traits;

use App\MessageCollection;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * Trait for models which have validation rules when saving
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasValidationTrait
{
    /**
     * Array of errors for this model
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Array of field names
     *
     * @var array
     */
    protected $fieldNames;

    /**
     * Get the validation rules for this model
     *
     * This method should return an array of validators such as:
     * ```
     * return [
     *      'name' => Validator::notEmpty(),
     *      'status' => Validator::in('active', 'inactive'),
     * ];
     * ```
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getValidators()
    {
        return [];
    }

    /**
     * Get the error array for this model
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getErrors()
    {
        return new MessageCollection($this->errors);
    }

    /**
     * Validate this model
     *
     * This method runs the validation routines on model fields and returns a
     * boolean result.
     *
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function validate()
    {
        $this->errors = [];
        $validators = $this->getValidators();
        foreach ($validators as $field => $validator) {
            $value = $this->$field;
            if (isset($this->fieldNames[$field])) {
                $name = $this->fieldNames[$field];
            } else {
                $name = ucwords(str_replace('_', ' ', strtolower($field)));
            }
            try {
                $validator
                    ->setName($name)
                    ->assert($this->$field);
            } catch (NestedValidationException $ex) {
                $this->errors[$field] = $ex->getMessages();
            }
        }

        return 0 == count($this->errors);
    }
}
