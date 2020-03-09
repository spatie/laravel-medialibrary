<?php

namespace Spatie\MediaLibrary\Tests\Conversions;

use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;

class ConversionCollectionTest extends TestCase
{
    protected Media $media;

    protected Media $secondMedia;

    public function setUp(): void
    {
        parent::setUp();

        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $media->manipulations = ['thumb' => ['filter' => 'greyscale', 'height' => 10]];
        $media->save();

        $secondMedia = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $secondMedia->manipulations = ['thumb' => ['filter' => 'greyscale', 'height' => 20]];
        $secondMedia->save();

        $this->media = $media->fresh();
        $this->secondMedia = $media->fresh();
    }

    /** @test */
    public function it_will_prepend_the_manipulation_saved_on_the_model_and_the_wildmark_manipulations()
    {
        $this->media->manipulations = [
            '*' => ['brightness' => '-80'],
            'thumb' => ['filter' => 'greyscale', 'height' => 10],
        ];

        $conversionCollection = ConversionCollection::createForMedia($this->media);

        $conversion = $conversionCollection->getConversions()[0];

        $this->assertEquals('thumb', $conversion->getName());

        $manipulationSequence = $conversion
            ->getManipulations()
            ->getManipulationSequence()
            ->toArray();

        $this->assertArrayHasKey('optimize', $manipulationSequence[0]);

        unset($manipulationSequence[0]['optimize']);

        $this->assertEquals([[
            'brightness' => '-80',
            'filter' => 'greyscale',
            'height' => 10,
            'width' => 50,
            'format' => 'jpg',
        ]], $manipulationSequence);
    }

    /** @test */
    public function it_will_prepend_the_manipulation_saved_on_the_model()
    {
        $conversionCollection = ConversionCollection::createForMedia($this->media);

        $conversion = $conversionCollection->getConversions()[0];

        $this->assertEquals('thumb', $conversion->getName());

        $manipulationSequence = $conversion
            ->getManipulations()
            ->getManipulationSequence()
            ->toArray();

        $this->assertArrayHasKey('optimize', $manipulationSequence[0]);

        unset($manipulationSequence[0]['optimize']);

        $this->assertEquals([[
            'filter' => 'greyscale',
            'height' => 10,
            'width' => 50,
            'format' => 'jpg',
        ]], $manipulationSequence);
    }

    /** @test */
    public function it_will_apply_the_manipulation_on_the_equally_named_conversion_of_every_model()
    {
        $mediaItems = [$this->media, $this->secondMedia];
        $manipulations = [];

        foreach ($mediaItems as $mediaItem) {
            $conversionCollection = ConversionCollection::createForMedia($mediaItem);

            $conversion = $conversionCollection->getConversions()[0];

            $manipulationSequence = $conversion
                ->getManipulations()
                ->getManipulationSequence()
                ->toArray();

            $manipulations[] = $manipulationSequence;
        }

        $this->assertEquals($manipulations[0], $manipulations[1]);
    }
}
