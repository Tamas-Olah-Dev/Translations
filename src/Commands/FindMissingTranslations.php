<?php

namespace Datalytix\Translations\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FindMissingTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:findmissing {baselocale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a list of translation labels missing from the database.';

    protected $translationFunctionPattern = '/(\_\_\(|translate\(|trans\(|\@lang\()\'(.*?)\'/miu';

    public function scandirRecursive($path, $filterString = null)
    {
        $list = array_diff(scandir($path), array( ".", ".." ));
        if ($filterString !== null) {
            $newList = [];
            foreach ($list as $index => $listItem) {
                if ((stripos($listItem, $filterString) !== false) || (is_dir($path.DIRECTORY_SEPARATOR.$listItem))) {
                    $newList[] = $listItem;
                }
            }
            $list = $newList;
        }
        $list2 = array();
        foreach ($list as $l) {
            if (is_dir($path.DIRECTORY_SEPARATOR.$l)) {
                $list3 = array_diff($this->scandirRecursive($path.DIRECTORY_SEPARATOR.$l), array( ".", ".." ));
                foreach ($list3 as $l3) {
                    if (($filterString == null)
                        || (($filterString != null) && (stripos($l3, $filterString) !== false))) {
                        $list2[] = $l.DIRECTORY_SEPARATOR.$l3;
                    }
                }
            }
        }
        return array_merge($list, $list2);
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $locale = $this->argument('baselocale');
        $translations = array_keys(app()->make('translation')->loadTranslations($locale));
        $path = base_path();
        $allfiles = [
            $path.DIRECTORY_SEPARATOR.'app' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'app', '.php'),
            $path.DIRECTORY_SEPARATOR.'config' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'config', '.php'),
            $path.DIRECTORY_SEPARATOR.'database' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'database', '.php'),
            $path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'),
            $path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views', '.php')
        ];
        $unfilteredResults = [];
        foreach ($allfiles as $basepath => $files) {
            foreach ($files as $file) {
                try {
                    $content = file_get_contents($basepath.DIRECTORY_SEPARATOR.$file);
                } catch (\Exception $e) {
                }
                $matches = [];
                preg_match_all($this->translationFunctionPattern, $content, $matches);
                if (count($matches[0]) > 0) {
                    $unfilteredResults = array_merge($unfilteredResults, $matches[2]);
                }
            }
        }
        $results = collect($unfilteredResults)->filter(function($item) use ($translations) {
            return array_search($item, $translations) === false;
        })->unique()->transform(function($item) {
            return '"'.$item.'",';
        })->all();
        $filename = storage_path('app'.DIRECTORY_SEPARATOR.'translationkeys-'.now()->format('Y-m-d-H-i-s').'.txt');
        file_put_contents($filename, implode("\n", $results));
        $this->info('List generated: '.$filename);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }
}
