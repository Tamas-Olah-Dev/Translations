<?php


namespace Datalytix\Translations;


use App\Locale;
use Datalytix\Translations\Scopes\TranslatableModelAllLocalesScope;
use Datalytix\Translations\Scopes\TranslatableModelScope;
use Illuminate\Database\Eloquent\Model;

abstract class TranslatableModel extends Model
{
    abstract public static function getSubjecttypeId();

    protected static function booted()
    {
        //translations are joined on query level at boot
        static::addGlobalScope(new TranslatableModelScope);
    }

    public function scopeWithAllTranslations($query)
    {
        return (new TranslatableModelAllLocalesScope())->apply(
            $query->withoutGlobalScope(TranslatableModelScope::class),
            new static()
        );
    }

    public function scopeWithoutTranslations($query)
    {
        return $query->withoutGlobalScope(TranslatableModelScope::class);
    }

    public static function createWithTranslations($data)
    {
        $model = null;
        $transactionResult = \DB::transaction(function() use (&$model, $data) {
            $localeClass = config('app.localeClass');
            $mainLocale = $localeClass::getMainLocale();
            $processedFields = [];
            $translationDatasets = [];
            foreach (static::getTranslatedProperties() as $property) {
                if (array_key_exists($property, $data)) {
                    $translationDatasets[] = [
                        'field' => $property,
                        'locale_id' => $mainLocale->id,
                        'translation' => (string)$data[$property]
                    ];
                    //$processedFields[] = $property;
                }
            }
            foreach (static::getTranslatedPropertiesWithLocales() as $property) {
                if (array_key_exists($property, $data)) {
                    $keyAndLocale = self::splitKeyToFieldAndLocale($property);
                    //if ($keyAndLocale['locale'] != $mainLocale->id) {
                    $translationDatasets[] = [
                        'field'       => $keyAndLocale['field'],
                        'locale_id'   => $keyAndLocale['locale'],
                        'translation' => (string)$data[$property]
                    ];
                    //}
                    $processedFields[] = $property;
                }
            }
            $model = static::query()->create(collect($data)->except($processedFields)->all());
            $translationClass = config('app.translationClass');
            foreach ($translationDatasets as $dataset) {
                $translationClass::updateOrCreate([
                    'subject_id' => $model->id,
                    'subjecttype_id' => static::getSubjecttypeId(),
                    'locale_id' => $dataset['locale_id'],
                    'key' => $model->id.'-'.static::getSubjecttypeId().'-'.$dataset['field'],
                    'field' => $dataset['field']
                ], [
                    'translation' => (string)$dataset['translation'],
                ]);
            }
        });

        return $transactionResult === null ? $model : false;
    }

    public function remove()
    {
        return \DB::transaction(function() {
                $translationClass = config('app.translationClass');
                $translationClass::forModel($this)
                    ->where('subject_id', '=', $this->id)
                    ->delete();
                $this->delete();
            }) === null;
    }

    public function updateWithTranslations($data)
    {
        $transactionResult = \DB::transaction(function() use ($data) {
            $localeClass = config('app.localeClass');
            $mainLocale = $localeClass::getMainLocale();
            $processedFields = [];
            $translationDatasets = [];
            foreach (static::getTranslatedProperties() as $property) {
                if (array_key_exists($property, $data)) {
                    $translationDatasets[] = [
                        'field' => $property,
                        'locale_id' => $mainLocale->id,
                        'translation' => (string)$data[$property]
                    ];
                    //$processedFields[] = $property;
                }
            }
            foreach (static::getTranslatedPropertiesWithLocales() as $property) {
                if (array_key_exists($property, $data)) {
                    $keyAndLocale = self::splitKeyToFieldAndLocale($property);
                    //if ($keyAndLocale['locale'] != $mainLocale->id) {
                    $translationDatasets[] = [
                        'field' => $keyAndLocale['field'],
                        'locale_id' => $keyAndLocale['locale'],
                        'translation' => (string)$data[$property]
                    ];
                    //}
                    $processedFields[] = $property;
                }
            }
            $this->update(collect($data)->except($processedFields)->all());
            $translationClass = config('app.translationClass');
            foreach ($translationDatasets as $dataset) {
                $translationClass::updateOrCreate([
                    'subject_id' => $this->id,
                    'subjecttype_id' => static::getSubjecttypeId(),
                    'locale_id' => $dataset['locale_id'],
                    'field' => $dataset['field']
                ], [
                    'key' => $this->id.'-'.static::getSubjecttypeId().'-'.$dataset['field'],
                    'translation' => $dataset['translation'],
                ]);
            }
        });

        return $transactionResult === null ? $this : false;
    }


    public static function updateOrCreateWithTranslations(array $search, array $data)
    {
        $element = static::where($search)->first();
        if ($element == null) {
            return static::createWithTranslations($search + $data);
        } else {
            return $element->updateWithTranslations($data);
        }
    }

    /**
     * get the field names to generate accessors for
     * @return array
     */
    abstract public static function getTranslatedProperties():array;

    protected static function getTranslatedPropertiesWithLocales()
    {
        $result = [];
        $localeClass = config('app.localeClass');

        foreach ($localeClass::select('id')->get() as $locale) {
            foreach (static::getTranslatedProperties() as $property) {
                $result[] = $property.'_'.$locale->id;
            }
        }
        return $result;
    }

    protected static function splitKeyToFieldAndLocale($key)
    {
//        $pieces = explode('_', $key);
//        return ['field' => $pieces[0], 'locale' => $pieces[1]];
        return [
            'field'  => \Str::beforeLast($key, '_'),
            'locale' => \Str::afterLast($key, '_')
        ];
    }
}
