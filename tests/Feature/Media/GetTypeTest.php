<?php

it('can return the file mime', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->mime_type)->toEqual('image/jpeg');
});
