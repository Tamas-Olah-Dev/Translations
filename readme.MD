This package allows for loading translations from the DB instead of JSON/PHP files.

In config/app.php replace the line 
```Illuminate\Translation\TranslationServiceProvider::class```

with

```Datalytix\Translations\TranslationServiceProvider::class```.

A class is also needed for loading the translations. The class has to be specified in app.translationClass and needs to implement \Datalytix\Translations\ITranslation.

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