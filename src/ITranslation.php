<?php


namespace Datalytix\Translations;


interface ITranslation
{
    public function __construct();
    public function loadTranslations($locale);
    public function getTranslations($locale);
    public function getCachedJSONTranslations($locale);
    public function getTranslationsForEditor($locales);
    public function storeTranslation($key, $locale, $translation);
    public function scopeForModel($query, $model, $localeId = null);
}