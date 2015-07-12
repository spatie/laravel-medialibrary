<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Spatie\MediaLibrary\Conversion\Conversion;

class ConversionTest extends \PHPUnit_Framework_TestCase
{

    protected $conversionName = 'test';

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    public function setUp()
    {
        $this->conversion = new Conversion($this->conversionName);
    }
    /**
     * @test
     */
    public function it_can_get_its_name()
    {
        $this->assertEquals($this->conversionName, $this->conversion->getName());
    }

    /**
     * @test
     */
    public function it_can_store_multiple_manipulations()
    {
        $this->conversion->setManipulations(['w' => '1'], ['h' => 2]);

        $this->assertEquals(2, count($this->conversion->getManipulations()));
    }

    /**
     * @test
     */
    public function it_will_add_a_format_parameter_if_it_was_not_given()
    {
        $this->conversion->setManipulations(['w' => '1']);

        $manipulations = $this->conversion->getManipulations();
        $this->arrayHasKey('fm', $manipulations[0]);
        $this->assertEquals('jpg', $manipulations[0]['fm']);
    }

    /**
 * @test
 */
    public function it_will_use_the_format_parameter_if_it_was_given()
    {
        $this->conversion->setManipulations(['fm' => 'png']);

        $manipulations = $this->conversion->getManipulations();
        $this->arrayHasKey('fm', $manipulations[0]);
        $this->assertEquals('png', $manipulations[0]['fm']);
    }

    /**
     * @test
     */
    public function it_will_be_performed_on_the_given_collection_names()
    {
        $this->conversion->performOnCollections('images', 'downloads');
        $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
        $this->assertFalse($this->conversion->shouldBePerformedOn('unknown'));
    }

    /**
     * @test
     */
    public function it_will_be_performed_on_all_collections_if_not_collection_names_are_set()
    {
        $this->conversion->performOnCollections('*');
        $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('unknown'));
    }

    /**
     * @test
     */
    public function it_will_be_performed_on_all_collections_if_not_collection_name_is_a_star()
    {
        $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
        $this->assertTrue($this->conversion->shouldBePerformedOn('unknown'));
    }

    /**
     * @test
     */
    public function it_will_be_queued_by_default()
    {
        $this->assertTrue($this->conversion->shouldBeQueued());
    }

    /**
     * @test
     */
    public function it_can_be_set_to_queued()
    {
        $this->assertTrue($this->conversion->queued()->shouldBeQueued());
    }

    /**
     * @test
     */
    public function it_can_be_set_to_nonQueued()
    {
        $this->assertFalse($this->conversion->nonQueued()->shouldBeQueued());
    }

    /**
     * @test
     */
    public function it_can_determine_the_extension_of_the_result()
    {
        $this->conversion->setManipulations(['w'=>100]);

        $this->assertEquals('jpg', $this->conversion->getResultExtension());

        $this->conversion->setManipulations(['w'=>100, 'fm'=>'png']);

        $this->assertEquals('png', $this->conversion->getResultExtension());
    }

    /**
     * @test
     */
    public function it_can_add_width_to_a_manipulation()
    {
        $this->conversion->setWidth(10);

        $this->arrayHasKey('w',$this->conversion->getManipulations()[0]);
        $this->assertEquals(10,$this->conversion->getManipulations()[0]['w']);
    }
}