<?php

namespace Datalytix\Translations\Sample;

use Datalytix\Translations\ITranslation;
use Illuminate\Database\Eloquent\Model;

class Translation extends \Datalytix\Translations\Translation implements ITranslation
{
    /**
     * This is an example of what the Translation class
     * needed for the package should look like.
     *
     * The instance should always be bound in Laravel's service container
     * as a singleton
     *
     */

    protected $fillable = [
        'key',
        'locale',
        'translation'
    ];

    public function loadTranslations($locale)
    {
        return self::where('locale', '=', $locale)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->translation];
            })->all();
    }

    public function getCachedJSONTranslations($locale)
    {
        return \Cache::rememberForever($this->getCacheKey($locale), function() use ($locale) {
            return json_encode($this->getTranslations($locale));
        });
    }

    public function getTranslationsForEditor($locales)
    {
        $result = [];
        foreach ($locales as $locale) {
            $translations = $this->getTranslations($locale);
            foreach ($translations as $key => $translation) {
                if (!isset($result[$key])) {
                    $result[$key] = [];
                }
                $result[$key][$locale] = $translation;
            }
        }

        return $result;
    }

    public function storeTranslation($key, $locale, $translation)
    {
        self::updateOrCreate([
            'key' => $key,
            'locale' => $locale
        ], [
            'key' => $key,
            'locale' => $locale,
            'translation' => $translation,
        ]);

        $this->reloadTranslations($locale);
        return 'OK';
    }
}
