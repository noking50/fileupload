<?php

namespace Noking50\FileUpload\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Classes\FileUpload\FileUpload
 */
class FileUpload extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'fileupload';
    }

}
