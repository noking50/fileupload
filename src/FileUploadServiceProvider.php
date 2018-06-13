<?php

namespace Noking50\FileUpload;

use Illuminate\Support\ServiceProvider;

class FileUploadServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('fileupload', function () {
            return new FileUpload;
        });
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['fileupload'];
    }

}
