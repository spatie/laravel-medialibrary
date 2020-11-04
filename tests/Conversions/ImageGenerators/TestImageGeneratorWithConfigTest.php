<?php

namespace Spatie\MediaLibrary\Tests\Conversions\ImageGenerators;

use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGeneratorFactory;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestImageGeneratorWithConfig;

class TestImageGeneratorWithConfigTest extends TestCase
{
    /** @test */
    public function image_generators_can_get_parameter_from_the_config_file()
    {
        config()->set('media-library.image_generators', [
            TestImageGeneratorWithConfig::class => ['test' => 'value', 'test2' => 'value2'],
        ]);

        $imageGenerators = ImageGeneratorFactory::getImageGenerators();

        $testGeneratorWithConfig = $imageGenerators->first();

        $this->assertInstanceOf(TestImageGeneratorWithConfig::class, $testGeneratorWithConfig);
        $this->assertEquals('value', $testGeneratorWithConfig->test);
        $this->assertEquals('value2', $testGeneratorWithConfig->test2);

    }
}
