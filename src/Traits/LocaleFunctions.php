<?php


namespace Datalytix\Translations\Traits;


trait LocaleFunctions
{
    public static function getYearMonth($year, $monthName, $locale = null)
    {
        $locale = $locale == null ? \App::getLocale() : $locale;
        if ($locale == 'hu') {
            return $year.'. '.$monthName;
        }
        return $monthName.' '.$year;
    }

    public static function getKeyValueCollection($useAdditionalQueries = true)
    {
        return self::all()->pluck('name', 'id')->mapWithKeys(function ($item, $key) {
            return [$key => __($item)];
        });
    }

    public function getIsMainLabelAttribute()
    {
        return $this->is_main == 0 ? __('Nem') : __('Igen');
    }

    public function getUppercaseIdAttribute()
    {
        return mb_strtoupper($this->id);
    }

    public static function getMainLocale()
    {
        return self::where('is_main', '=', 1)->first();
    }

    public function getTranslatedPropertyName($field)
    {
        return $field.'_'.$this->id;
    }

    public function formatName($subject, $separator = ' ')
    {
        // subject should have firstname and lastname properties
        $nameorder = $this->nameorder == null ? 'lastname,firstname' : $this->nameorder;
        $fields = explode(',', $nameorder);

        return implode($separator, [$subject->{$fields[0]}, $subject->{$fields[1]}]);
    }


}