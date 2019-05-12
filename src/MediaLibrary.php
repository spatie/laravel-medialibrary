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
     * Get the config array, or a value from it.
     *
     * @param  string|null  $key
     * @param  mixed        $default
     * @return mixed
     */
    public static function config($key = null, $default = null)
    {
        $config = config(static::configFile());

        return func_num_args() ? data_get($config, $key, $default) : $config;
    }

    /**
     * Set a config value for the given key.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public static function setConfig(string $key, $value)
    {
        config([static::configFile().'.'.$key => $value]);
    }
}
