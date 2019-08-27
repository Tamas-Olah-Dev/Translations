<?php


namespace Datalytix\Translations;


abstract class Translation extends \Illuminate\Database\Eloquent\Model implements ITranslation
{

    public function getTranslations($locale)
    {
        return $this->loadTranslations($locale);
    }

    public function reloadTranslations($locale)
    {
        app()->make('translator')->setLoaded([]);
        \Cache::forget($this->getCacheKey($locale));
    }

    public function getCacheKey($locale)
    {
        return 'translation_'.$locale;
    }
}