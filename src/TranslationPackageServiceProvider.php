<?php

namespace Datalytix\Translations;

use Illuminate\Support\ServiceProvider;

class TranslationPackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/js' => resource_path('js/components'),
        ], 'translations-js');

        $this->commands([
            \Datalytix\Translations\Commands\FindMissingTranslations::class,
        ]);
    }

}
