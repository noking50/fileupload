<?php

namespace Noking50\FileUpload\Rules;

use Illuminate\Contracts\Validation\Rule;

class JsonEditor implements Rule {

    protected $required;
    protected $validate_result;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($required = false) {
        $this->required = boolval($required);
        $this->validate_result = null;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
        $this->validate_result = null;

        $editor = json_decode($value, true);
        if (!is_array($editor)) {
            $this->validate_result = 'format';
            return false;
        }
        if ($this->required === true && count($editor) <= 0) {
            $this->validate_result = 'required';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        $message = 'Validate failed';
        switch ($this->validate_result) {
            case 'format':
                $message = trans('fileupload::validation.json_editor.format');
                break;
            case 'required':
                $message = trans('fileupload::validation.json_editor.required');
                break;
        }

        return $message;
    }

}
