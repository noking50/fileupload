<?php

namespace Noking50\FileUpload\Rules;

use Illuminate\Contracts\Validation\Rule;

class JsonFile implements Rule {

    protected $min;
    protected $max;
    protected $validate_result;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($min = 0, $max = 0) {
        $this->min = intval($min);
        $this->max = intval($max);
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

        $files = json_decode($value, true);
        if (!is_array($files)) {
            $this->validate_result = 'format';
            return false;
        }
        if (count($files) < $this->min) {
            if (count($files) == 0) {
                $this->validate_result = 'required';
            } else {
                $this->validate_result = 'min';
            }
            return false;
        }
        if (count($files) > $this->max) {
            $this->validate_result = 'max';
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
        switch($this->validate_result){
            case 'format':
                $message = trans('fileupload::validation.json_file.format');
                break;
            case 'required':
                $message = trans('fileupload::validation.json_file.required');
                break;
            case 'min':
                $message = trans('fileupload::validation.json_file.min', ['min' => $this->min]);
                break;
            case 'max':
                $message = trans('fileupload::validation.json_file.max', ['min' => $this->max]);
                break;
        }
        
        return $message;
    }

}
