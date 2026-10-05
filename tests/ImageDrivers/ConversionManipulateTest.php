<?php

use Spatie\Image\Drivers\ImageDriver;
use Spatie\MediaLibrary\Conversions\Conversion;

it('stores the manipulation closure as a serializable closure', function () {
    $conversion = Conversion::create('thumb')
        ->manipulate(fn (ImageDriver $image) => $image->width(100));

    expect($conversion->getManipulationClosure())->not->toBeNull();
});

it('survives serialization so it can run on the queue', function () {
    $conversion = Conversion::create('thumb')
        ->useImageDriver('cloudflare')
        ->manipulate(fn ($image) => $image->width(100));

    /** @var Conversion $restored */
    $restored = unserialize(serialize($conversion));

    expect($restored->getName())->toBe('thumb')
        ->and($restored->getImageDriverName())->toBe('cloudflare')
        ->and($restored->getManipulationClosure())->not->toBeNull();

    $recorder = new class
    {
        public array $calls = [];

        public function width(int $width): self
        {
            $this->calls[] = ['width', $width];

            return $this;
        }
    };

    ($restored->getManipulationClosure()->getClosure())($recorder);

    expect($recorder->calls)->toBe([['width', 100]]);
});

it('reports a conversion without a url resolving driver as not virtual', function () {
    $conversion = Conversion::create('thumb')
        ->manipulate(fn (ImageDriver $image) => $image->width(100));

    expect($conversion->isVirtual())->toBeFalse();
});
