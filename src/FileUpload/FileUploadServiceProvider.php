<?php

namespace Noking50\FileUpload;

use Illuminate\Support\ServiceProvider;

class FileUploadServiceProvider extends ServiceProvider {

    public function boot() {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'fileupload');
        $this->publishes([
            __DIR__ . '/../config/fileupload.php' => config_path('fileupload.php'),
            __DIR__ . '/../lang' => resource_path('lang/vendor/fileupload'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(
                __DIR__ . '/../config/fileupload.php', 'fileupload'
        );
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
