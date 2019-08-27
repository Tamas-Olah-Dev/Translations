<?php


namespace Datalytix\Translations;


use App\Translation;

class TranslationServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            $app->singleton('translation', function($app) {
                $class = config('app.translationClass');
                $translation = new $class();
                if (!$translation instanceof ITranslation) {
                    throw new \Exception('Invalid translation class specified in app.translationClass - the class does not implement ITranslation.');
                }
                return $translation;
            });
            return new DBLoader($app->make('translation'));
        });
    }

}