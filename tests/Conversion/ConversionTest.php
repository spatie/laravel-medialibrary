<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Conversion\Conversion;

class ConversionTest extends TestCase
{
    protected $conversionName = 'test';

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    public function setUp()
    {
        $this->conversion = new Conversion($this->conversionName);

        parent::setUp();
    }

    /** @test */
    public function it_can_get_its_name()
    {
        $this->assertEquals($this->conversionName, $this->conversion->getName());
    }

    /** @test */
    public function it_can_store_multiple_manipulations()
    {
        $this->conversion->setManipulations(['w' => 1], ['h' => 2]);

        $this->assertEquals(2, count($this->conversion->getManipulations()));
    }

    /** @test */
    public function it_will_add_a_format_parameter_if_it_was_not_given()
    {
        $this->conversion->setManipulations(['w' => 1]);

        $manipulations = $this->conversion->getManipulations();
        $this->arrayHasKey('fm', $manipulations[0]);
        $this->assertEquals('jpg', $manipulations[0]['fm']);
    }

    /** @test */
    public function it_will_use_the_format_parameter_if_it_was_given()
    {
        $this->conversion->setManipulations(['fm' => 'png']);

        $manipulations = $this->conversion->getManipulations();
        $this->arrayHasKey('fm', $manipulations[0]);
        $this->assertEquals('png', $manipulations[0]['fm']);
    }

    /** @test */
    public function it_will_be_performed_on_the_given_collection_names()
    {
        $this->conversion->performOnCollections('images', 'downloads');
        $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
        $this->assertFalse($this->conversion->shouldBePerformedOn('unknown'));
    }

    /** @test */
    public function it_will_be_performed_on_all_collections_if_not_collection_names_are_set()
    {
        $this->conversion->performOnCollections('*');
        $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('unknown'));
    }

    /** @test */
    public function it_will_be_performed_on_all_collections_if_not_collection_name_is_a_star()
    {
        $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('unknown'));
    }

    /** @test */
    public function it_will_be_queued_by_default()
    {
        $this->assertTrue($this->conversion->shouldBeQueued());
    }

    /** @test */
    public function it_can_be_set_to_queued()
    {
        $this->assertTrue($this->conversion->queued()->shouldBeQueued());
    }

    /** @test */
    public function it_can_be_set_to_nonQueued()
    {
        $this->assertFalse($this->conversion->nonQueued()->shouldBeQueued());
    }

    /** @test */
    public function it_can_determine_the_extension_of_the_result()
    {
        $this->conversion->setManipulations(['w' => 100]);

        $this->assertEquals('jpg', $this->conversion->getResultExtension());

        $this->conversion->setManipulations(['w' => 100, 'fm' => 'png']);

        $this->assertEquals('png', $this->conversion->getResultExtension());
    }

    /** @test */
    public function it_can_keep_the_original_file_extension_for_certain_formats()
    {
        $this->conversion->setManipulations(['w' => 100, 'fm' => 'src']);

        $this->assertEquals('jpg', $this->conversion->getResultExtension('jpg'));
        $this->assertEquals('jpeg', $this->conversion->getResultExtension('jpeg'));
        $this->assertEquals('png', $this->conversion->getResultExtension('png'));
        $this->assertEquals('gif', $this->conversion->getResultExtension('gif'));
    }

    /** @test */
    public function it_can_add_width_to_a_manipulation()
    {
        $conversion = $this->conversion->setWidth(10);

        $this->arrayHasKey('w', $this->conversion->getManipulations()[0]);
        $this->assertEquals(10, $this->conversion->getManipulations()[0]['w']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_can_add_height_to_a_manipulation()
    {
        $conversion = $this->conversion->setHeight(10);

        $this->arrayHasKey('h', $this->conversion->getManipulations()[0]);
        $this->assertEquals(10, $this->conversion->getManipulations()[0]['h']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_can_add_format_to_a_manipulation()
    {
        $conversion = $this->conversion->setFormat('gif');

        $this->arrayHasKey('fm', $this->conversion->getManipulations()[0]);
        $this->assertEquals('gif', $this->conversion->getManipulations()[0]['fm']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_throw_an_exception_for_an_invalid_format()
    {
        $this->expectException(\Spatie\MediaLibrary\Exceptions\InvalidConversionParameter::class);
        $this->conversion->setFormat('blabla');
    }

    /** @test */
    public function it_can_add_fit_to_a_manipulation()
    {
        $conversion = $this->conversion->setFit('max');

        $this->arrayHasKey('fit', $this->conversion->getManipulations()[0]);
        $this->assertEquals('max', $this->conversion->getManipulations()[0]['fit']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_throw_an_exception_for_an_invalid_fit()
    {
        $this->expectException(\Spatie\MediaLibrary\Exceptions\InvalidConversionParameter::class);
        $this->conversion->setFit('blabla');
    }

    /** @test */
    public function it_can_add_rectangle_to_a_manipulation()
    {
        $conversion = $this->conversion->setCrop(100, 200, 300, 400);

        $this->arrayHasKey('crop', $this->conversion->getManipulations()[0]);
        $this->assertEquals('100,200,300,400', $this->conversion->getManipulations()[0]['crop']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_allows_zero_x_y_coordinates_in_rectangle_manipulations()
    {
        $conversion = $this->conversion->setCrop(100, 200, 0, 0);

        $this->arrayHasKey('crop', $this->conversion->getManipulations()[0]);
        $this->assertEquals('100,200,0,0', $this->conversion->getManipulations()[0]['crop']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_can_add_a_parameter_to_a_manipulation()
    {
        $conversion = $this->conversion->setManipulationParameter('name', 'value');

        $this->arrayHasKey('name', $this->conversion->getManipulations()[0]);
        $this->assertEquals('value', $this->conversion->getManipulations()[0]['name']);
        $this->assertInstanceOf(\Spatie\MediaLibrary\Conversion\Conversion::class, $conversion);
    }

    /** @test */
    public function it_can_chain_the_convenience_methods()
    {
        $conversion = $this->conversion->setWidth(75)->setHeight(75)->setFit('crop')->setFormat('jpg');

        $otherConversions = (new Conversion('other'))->setManipulations(['w' => 75, 'h' => 75, 'fit' => 'crop', 'fm' => 'jpg']);

        $this->assertEquals($conversion->getManipulations(), $otherConversions->getManipulations());
    }

    /** @test */
    public function it_will_use_the_extract_duration_parameter_if_it_was_given()
    {
        $this->conversion->setExtractVideoFrameAtSecond(10);

        $this->assertEquals(10, $this->conversion->getExtractVideoFrameAtSecond());
    }
}
