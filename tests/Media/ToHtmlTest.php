<?php

namespace Spatie\MediaLibrary\Media;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Tests\TestCase;

class ToHtmlTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();
    }

    /** @test */
    public function it_can_render_itself_as_an_image()
    {
        $this->assertEquals('<img src="/media/1/test.jpg" alt="test">', Media::first()->img());
    }

    /** @test */
    public function it_can_render_a_conversion_of_itself_as_an_image()
    {
        $this->assertEquals('<img src="/media/1/conversions/test-thumb.jpg" alt="test">', Media::first()->img('thumb'));
    }

    /** @test */
    public function it_can_render_extra_attributes()
    {
        $this->assertEquals(
            '<img class="my-class" id="my-id" src="/media/1/conversions/test-thumb.jpg" alt="test">',
             Media::first()->img('thumb', ['class' => 'my-class', 'id' => 'my-id'])
        );
    }

    /** @test */
    public function attributes_can_be_passed_to_the_first_argument()
    {
        $this->assertEquals(
            '<img class="my-class" id="my-id" src="/media/1/test.jpg" alt="test">',
             Media::first()->img(['class' => 'my-class', 'id' => 'my-id'])
        );
    }

    /** @test */
    public function both_the_conversion_and_extra_attributes_can_be_passed_as_the_first_arugment()
    {
        $this->assertEquals(
            '<img class="my-class" id="my-id" src="/media/1/conversions/test-thumb.jpg" alt="test">',
             Media::first()->img(['class' => 'my-class', 'id' => 'my-id', 'conversion' => 'thumb'])
        );
    }
}
