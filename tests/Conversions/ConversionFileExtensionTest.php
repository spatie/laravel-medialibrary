<?php



it('defaults to jpg when the original file is an image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

    assertExtensionEquals('jpg', $media->getUrl('thumb'));
});

it('can keep the original image format if the original file is an image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

    assertExtensionEquals('png', $media->getUrl('keep_original_format'));
});

it('can keep the original image format if the original file is an image with uppercase extension', function () {
    $media = $this->testModelWithConversion->addMedia($this->getUppercaseExtensionTestPng())->toMediaCollection();

    assertExtensionEquals('PNG', $media->getUrl('keep_original_format'));
});

it('always defaults to jpg when the original file is not an image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestMp4())->toMediaCollection();

    assertExtensionEquals('jpg', $media->getUrl('thumb'));
    assertExtensionEquals('jpg', $media->getUrl('keep_original_format'));
});

// Helpers
function assertExtensionEquals(string $expectedExtension, string $file)
{
    $actualExtension = pathinfo($file, PATHINFO_EXTENSION);

    expect($actualExtension)->toEqual($expectedExtension);
}
