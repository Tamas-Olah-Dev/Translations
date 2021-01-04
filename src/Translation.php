<?php


namespace Datalytix\Translations;


abstract class Translation extends \Illuminate\Database\Eloquent\Model implements ITranslation
{

    public function getCachedJSONTranslations($locale)
    {
        return \Cache::rememberForever($this->getCacheKey($locale), function() use ($locale) {
            return json_encode($this->getTranslations($locale));
        });
    }

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


    public function scopeSelectLocaleFields($query, $model, $localeIds)
    {
        // https://modern-sql.com/use-case/pivot
        // to be used with TranslatableModelScope
        if (!method_exists($model, 'getTranslatedProperties')) {
            throw new \Exception('Only to be used with TranslatableModel objects');
        }
        $fields = $model->getTranslatedProperties();
        $selects = ['subject_id'];
        foreach ($fields as $field) {
            foreach ($localeIds as $localeId) {
                $selects[] = \DB::raw('MAX(CASE WHEN field="'.$field.'" and locale_id="'.$localeId.'" THEN translation END) '.$field.'_'.$localeId);
                if ($localeId == \App::getLocale()) {
                    $selects[] = \DB::raw('MAX(CASE WHEN field="'.$field.'" and locale_id="'.$localeId.'" THEN translation END) '.$field.'_translated');
                }

            }
        }

        return $query->select($selects);
    }

}