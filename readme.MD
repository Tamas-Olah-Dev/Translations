This package allows for loading translations from the DB instead of JSON/PHP files.

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

```
(there is an example in the Sample directory of the package for more reference)


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
 
