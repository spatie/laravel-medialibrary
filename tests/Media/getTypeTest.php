<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;

class GetTypeTest extends TestCase
{
    public function setUp()
    {
    }

    /**
     * @test
     * @dataProvider extensionProvider
     *
     * @param string $extension
     * @param string $type
     */
    public function it_can_determine_the_type_from_the_extension($extension, $type)
    {
        $media = new Media();
        $media->file_name = 'test.'.$extension;
        $this->assertEquals($type, $media->type);
    }

    public static function extensionProvider()
    {
        $extensions =
            [
                ['jpg', Media::TYPE_IMAGE],
                ['jpeg', Media::TYPE_IMAGE],
                ['png', Media::TYPE_IMAGE],
                ['pdf', Media::TYPE_PDF],
                ['bla', Media::TYPE_OTHER],
            ];

        $capitalizedExtensions = array_map(function ($extension) {
            $extension[0] = strtoupper($extension[0]);

            return $extension;
        }, $extensions);

        return array_merge($extensions, $capitalizedExtensions);
    }
}
