<?php

namespace Feature;

use Spatie\MediaLibrary\MediaLibrary;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_has_fallback_for_disk_name()
    {
        $app = app();

        $app['config']->set(MediaLibrary::configFile().'.disk_name', null);
        $app['config']->set(MediaLibrary::configFile().'.default_filesystem', 'test');

        $provider = new MediaLibraryServiceProvider($app);

        $provider->register();

        $this->assertEquals(MediaLibrary::config('default_filesystem'), MediaLibrary::config('disk_name'));
    }

    /** @test */
    public function it_can_use_a_custom_config_file()
    {
        $app = app();

        $config_file = 'medialibrary_custom';

        $disk_name = 'custom_disk';

        $app['config']->set($config_file, compact('disk_name'));

        $provider = new MediaLibraryServiceProvider($app);

        $provider->register();

        MediaLibrary::setConfigFile($config_file);

        $this->assertEquals($disk_name, MediaLibrary::config('disk_name'));
    }
}
