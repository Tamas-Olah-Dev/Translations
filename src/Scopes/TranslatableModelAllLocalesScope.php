<?php


namespace Datalytix\Translations\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TranslatableModelAllLocalesScope implements Scope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $class = config('app.translationClass');
        $localeClass = config('app.localeClass');
        if ((!class_exists($class)) || (!class_exists($localeClass))) {
            throw new \Exception('Necessary configuration (app.translationClass, app.localeClass) not set!');
        }
        return $builder->joinSub(
            $class::forModel($model)->selectLocaleFields($model, $localeClass::select('id')->get()->pluck('id'))->groupBy('subject_id'),
            'tr',
            'tr.subject_id',
            '=',
            $model->getTable().'.'.$model->getKeyName()
        );
    }
}
