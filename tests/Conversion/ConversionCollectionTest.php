<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Conversion\ConversionCollection;

class ConversionCollectionTest extends TestCase
{
    /** @var \Spatie\MediaLibrary\Media */
    protected $media;

    public function setUp()
    {
        parent::setUp();

        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $media->manipulations = ['thumb' => ['filter' => 'greyscale', 'height' => 10]];
        $media->save();

        $this->media = $media->fresh();
    }

    /** @test */
    public function it_will_prepend_the_manipulation_saved_on_the_model()
    {
        $conversionCollection = ConversionCollection::createForMedia($this->media);

        $conversion = $conversionCollection->getConversions()[0];

        $this->assertEquals('thumb', $conversion->getName());

        $this->assertEquals([[
            'filter' => 'greyscale',
            'height' => 10,
            'width' => 50,
            'format' => 'jpg',
        ]], $conversion
            ->getManipulations()
            ->getManipulationSequence()
            ->toArray()
        );
    }
}
