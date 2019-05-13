<?php

namespace Spatie\MediaLibrary;

class MediaLibrary
{
    /**
     * The config file to use.
     *
     * @var string
     */
    protected static $config_file = 'medialibrary';

    /**
     * Get the config file.
     *
     * @return string
     */
    public static function configFile()
    {
        return static::$config_file;
    }

    /**
     * Specify the config file to use.
     *
     * @param  string  $config_file
     * @return static
     */
    public static function setConfigFile(string $config_file)
    {
        $config_file = trim($config_file, ' /');
        $config_file = rtrim($config_file, '.php');
        $config_file = str_replace('/', '.', $config_file);

        static::$config_file = $config_file;

        return new static;
    }

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed         $default
     * @return mixed|array
     */
    public static function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return config(static::configFile(), []);
        }

        if (is_array($key)) {
            return static::setConfig($key);
        }

        return static::getConfig($key, $default);
    }

    /**
     * Get the config value for the given key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function getConfig(string $key, $default = null)
    {
        return data_get(static::config(), $key, $default);
    }

    /**
     * Set a config value for the given key.
     *
     * @param  array|string  $key
     * @param  mixed         $value
     * @return void
     */
    public static function setConfig($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        // Here, we'll prepend the given keys with the config file that's being used.
        $keys = collect($keys)->mapWithKeys(function ($value, $key) {
            return [static::configFile().'.'.$key => $value];
        })->all();

        config($keys);
    }
}
