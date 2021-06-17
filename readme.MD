This package allows for loading translations from the DB instead of JSON/PHP files.

The app.translationClass and app.localeClass need to be set to the respective class names. Locale needs its id to be the 2-char locale key Laravel uses, and it has to use the LocaleFunctions trait.

In config/app.php replace the line 
```Illuminate\Translation\TranslationServiceProvider::class```

with

```Datalytix\Translations\TranslationServiceProvider::class```.

A class is also needed for loading the translations. The class has to be specified in app.translationClass and needs to implement \Datalytix\Translations\ITranslation, and
it needs to be bound in the boot() method of AppServiceProvider:
```
        $this->app->singleton('Translation', function() {
            return new App\Translation();
        });
        app()->bind('translation', \App\Translation::class);
```
There is an example in the Sample directory of the package for more reference. The class needs to have subject_id, subjecttype_id, key and field columns for proper operation. Subjecttype_id binds to the class to be translated, subject_id binds to the instance, field sets the actual translated property and key is needed for the Laravel translation function's lookups.


After saving a new translation the command 

```app()->make('translator')->setLoaded([])```
 
 will force a reload.
 
 To use in Javascript components, include the following in the head of the blade layout:
 ```
<script> window.laravelTranslations = JSON.parse('{{ app()->make('translation')->getCachedJSONTranslations(\App::getLocale()) }}')</script> 
```
The main Vue components needs the translate mixin:
```
import {translateMixin} from './components/mixins/translateMixin';
...
mixins: [translateMixin],
```

There is a Vue component that allows for editing translations. It should be included in the views like this:

```
    <translation-manager operations-url="{{ route('translations_operation') }}"
                         key-property="key"
                         :locales="['en', 'hu']"
    >
```
where 
- operations-url points to a controller that uses the controlsTranslationManager trait (it uses the bound singleton to list and update translations automatically)
- key-property is the identifying key of the translation model (not the ID, but rather a key that is shared by phrases across locales)
- locales is an array of available locales in the app
 
###Seeding

With 2.0, models are now supposed to have all translatable fields as columns, storing the translations of the main locale. So if there is a Book object and the main locale is 'hu', this should look like this:

- the 'books' table should have a 'title' field 
- this field should contain the translation for the HU locale
- when using updateWithTranslations or createWithTranslations, the datasets should not have 'title_hu' fields, as that translation is inferred from 'title'. So for 'hu' and 'en' translations the dataset should look like ``['title' => 'Title in Hungarian', 'title_en' => 'Title in English']``. 