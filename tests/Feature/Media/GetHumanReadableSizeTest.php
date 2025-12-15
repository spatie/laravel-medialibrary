<?php

test('the human_readable_size attribute can be accessed', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->human_readable_size)->toBeString();
    expect($media->human_readable_size)->toContain('KB');
});

test('the human_readable_size returns correct format', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->human_readable_size)->toMatch('/^\d+(\.\d+)?\s(B|KB|MB|GB|TB|PB|EB|ZB|YB)$/');
});
