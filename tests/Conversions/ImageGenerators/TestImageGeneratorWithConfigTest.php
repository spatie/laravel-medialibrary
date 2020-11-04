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
            TestImageGeneratorWithConfig::class => ['firstName' => 'firstValue', 'secondName' => 'secondValue'],
        ]);

        $imageGenerators = ImageGeneratorFactory::getImageGenerators();

        $testGeneratorWithConfig = $imageGenerators->first();

        $this->assertInstanceOf(TestImageGeneratorWithConfig::class, $testGeneratorWithConfig);

        $this->assertEquals('firstValue', $testGeneratorWithConfig->firstName);
        $this->assertEquals('secondValue', $testGeneratorWithConfig->secondName);
    }

    /** @test */
    public function image_generators_will_receive_config_parameters_by_name()
    {
        config()->set('media-library.image_generators', [
            TestImageGeneratorWithConfig::class => ['secondName' => 'secondValue', 'firstName' => 'firstValue', ],
        ]);

        $imageGenerators = ImageGeneratorFactory::getImageGenerators();

        $testGeneratorWithConfig = $imageGenerators->first();

        $this->assertInstanceOf(TestImageGeneratorWithConfig::class, $testGeneratorWithConfig);
        $this->assertEquals('firstValue', $testGeneratorWithConfig->firstName);
        $this->assertEquals('secondValue', $testGeneratorWithConfig->secondName);
    }
}
