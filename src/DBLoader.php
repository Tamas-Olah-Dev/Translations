<?php


namespace Datalytix\Translations;


class DBLoader implements \Illuminate\Contracts\Translation\Loader
{
    protected $translation;

    public function __construct(ITranslation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string $locale
     * @param  string $group
     * @param  string $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        if ($group === '*' && $namespace === '*') {
            return $this->translation->loadTranslations($locale);
        }
        return [];
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string $namespace
     * @param  string $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        // TODO: Implement addNamespace() method.
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string $path
     * @return void
     */
    public function addJsonPath($path)
    {
        // TODO: Implement addJsonPath() method.
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces()
    {
        return [];
    }
}