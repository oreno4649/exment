<?php
namespace Exceedone\Exment\Validator;

use Illuminate\Contracts\Validation\Rule;

/**
 * Select or select_valtext Rule.
 * numeric or array.
 * *not consider allow multiple.
 */
class SelectRule implements Rule
{
    protected $keys;

    public function __construct($parameters)
    {
        $this->keys = $parameters;
    }

    /**
    * Check Validation
    *
    * @param  string  $attribute
    * @param  mixed  $value
    * @return bool
    */
    public function passes($attribute, $value)
    {
        if (is_nullorempty($value) || is_nullorempty($this->keys)) {
            return true;
        }

        if (!is_array($value) && in_array($value, $this->keys)) {
            return true;
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $value = array_filter($value);

        if (is_array($value)) {
            foreach ($value as $v) {
                if (!in_array($v, $this->keys)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * get validation error message
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.numeric');
    }
}
