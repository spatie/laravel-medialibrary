<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithCustomLoadingAttribute;
use Spatie\Snapshots\MatchesSnapshots;

class ToHtmlTest extends TestCase
{
    use MatchesSnapshots;

    public function setUp(): void
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
        $this->assertEquals(
            '<img src="/media/1/test.jpg" alt="test">',
            $this->firstMedia()->img(),
        );
    }

    /** @test */
    public function it_can_render_a_conversion_of_itself_as_an_image()
    {
        $this->assertEquals(
            '<img src="/media/1/conversions/test-thumb.jpg" alt="test">',
            $this->firstMedia()->img('thumb')
        );
    }

    /** @test */
    public function it_can_render_extra_attributes()
    {
        $this->assertEquals(
            '<img class="my-class" id="my-id" src="/media/1/conversions/test-thumb.jpg" alt="test">',
            $this->firstMedia()->img('thumb', ['class' => 'my-class', 'id' => 'my-id']),
        );
    }

    /** @test */
    public function a_media_instance_is_htmlable()
    {
        $media = $this->firstMedia();

        $renderedView = $this->renderView('media', compact('media'));

        $this->assertEquals(
            '<img src="/media/1/test.jpg" alt="test"> <img src="/media/1/conversions/test-thumb.jpg" alt="test">',
            $renderedView,
        );
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
        $this->assertTrue(Str::contains($image, 'data:image/svg+xml;base64,'));
    }

    /** @test */
    public function it_can_render_itself_with_responsive_images_of_a_conversion_and_a_placeholder()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $image = $media->refresh()->img('thumb');

        $this->assertStringContainsString('/media/2/responsive-images/', $image);
        $this->assertStringContainsString('data:image/svg+xml;base64,', $image);
    }

    /** @test */
    public function it_will_not_rendering_extra_javascript_or_including_base64_svg_when_tiny_placeholders_are_turned_off()
    {
        config()->set('media-library.responsive_images.use_tiny_placeholders', false);

        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $imgTag = $media->refresh()->img();

        $this->assertEquals('<img srcset="http://localhost/media/2/responsive-images/test___media_library_original_340_280.jpg 340w, http://localhost/media/2/responsive-images/test___media_library_original_284_233.jpg 284w, http://localhost/media/2/responsive-images/test___media_library_original_237_195.jpg 237w" src="/media/2/test.jpg" width="340" height="280">', $imgTag);
    }

    /** @test */
    public function the_loading_attribute_can_be_specified_on_the_conversion()
    {
        $media = TestModelWithCustomLoadingAttribute::create(['name' => 'test'])
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $originalImgTag = $media->refresh()->img();
        $this->assertEquals('<img src="/media/2/test.jpg" alt="test">', $originalImgTag);

        $lazyConversionImageTag = $media->refresh()->img('lazy-conversion');
        $this->assertEquals('<img loading="lazy" src="/media/2/conversions/test-lazy-conversion.jpg" alt="test">', $lazyConversionImageTag);

        $eagerConversionImageTag = $media->refresh()->img('eager-conversion');
        $this->assertEquals('<img loading="eager" src="/media/2/conversions/test-eager-conversion.jpg" alt="test">', $eagerConversionImageTag);
    }

    /** @test */
    public function it_has_a_shorthand_function_to_use_lazy_loading()
    {
        $this->assertEquals(
            '<img loading="lazy" src="/media/1/test.jpg" alt="test">',
            $this->firstMedia()->img()->lazy()
        );
    }

    /** @test */
    public function it_can_set_extra_attributes()
    {
        $this->assertEquals(
            '<img extra="value" src="/media/1/test.jpg" alt="test">',
            (string) $this->firstMedia()->img()->attributes(['extra' => 'value'])
        );
    }

    protected function firstMedia(): Media
    {
        return Media::first();
    }
}
