<?php


namespace Datalytix\Translations\Traits;


trait controlsTranslationManager
{
    public function operation()
    {
        if (method_exists($this, request('action'))) {
            $method = request('action');
            return $this->$method();
        }
        abort(404);
    }

    public function fetchTranslations()
    {
        return response()->json(app()->make('translation')->getTranslationsForEditor(request('locales')));
    }

    public function storeTranslation()
    {
        return response(
            app()->make('translation')->storeTranslation(
                request('key'),
                request('locale'),
                request('translation')
            )
        );
    }
}