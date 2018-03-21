<?php

namespace Spatie\MediaLibrary\Models\Media;

use Spatie\MediaLibrary\Models\Media;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\MediaLibrary\Tests\TestCase;

class ToHtmlTest extends TestCase
{
    use MatchesSnapshots;

    public function setUp()
    {
        parent::setUp();

        $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
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

    /** @test */
    public function a_media_instance_is_htmlable()
    {
        $media = Media::first();

        $renderedView = $this->renderView('media', compact('media'));

        $this->assertEquals('<img src="/media/1/test.jpg" alt="test"> <img src="/media/1/conversions/test-thumb.jpg" alt="test">', $renderedView);
    }

    /** @test */
    public function converting_a_non_image_to_an_image_tag_will_not_blow_up()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestPdf())
            ->toMediaCollection();

        $this->assertEquals('', $media->img());
    }

    /** @test */
    public function it_can_render_itself_with_responsive_images_and_a_placeholder()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $image = $media->refresh()->img();

        $this->assertEquals(3, substr_count($image, '/media/2/responsive-images/'));
        $this->assertTrue(str_contains($image, 'data:image/svg+xml;base64,'));
    }

    /** @test */
    public function it_can_render_itself_with_responsive_images_of_a_conversion_and_a_placeholder()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $image = $media->refresh()->img('thumb');

        $this->assertEquals(1, substr_count($image, '/media/2/responsive-images/'));
        $this->assertTrue(str_contains($image, 'data:image/svg+xml;base64,'));
    }

    /** @test */
    public function it_will_not_rendering_extra_javascript_or_including_base64_svg_when_tiny_placeholders_are_turned_off()
    {
        config()->set('medialibrary.responsive_images.use_tiny_placeholders', false);

        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $imgTag = $media->refresh()->img();

        $this->assertEquals('<img srcset="/media/2/responsive-images/test___medialibrary_original_340_280.jpg 340w, /media/2/responsive-images/test___medialibrary_original_284_233.jpg 284w, /media/2/responsive-images/test___medialibrary_original_237_195.jpg 237w" src="/media/2/test.jpg" width="340">', $imgTag);
    }
}
