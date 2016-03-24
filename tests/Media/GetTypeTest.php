<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;


class GetTypeTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
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
        $media->file_name = 'test.' . $extension;
        $this->assertEquals($type, $media->type_from_extension);
    }

    public static function extensionProvider()
    {
        $extensions =
            [
                ['jpg', Media::TYPE_IMAGE],
                ['jpeg', Media::TYPE_IMAGE],
                ['png', Media::TYPE_IMAGE],
                ['gif', Media::TYPE_IMAGE],
                ['pdf', Media::TYPE_PDF],
                ['bla', Media::TYPE_OTHER],
            ];

        $capitalizedExtensions = array_map(function ($extension) {
            $extension[0] = strtoupper($extension[0]);

            return $extension;
        }, $extensions);

        return array_merge($extensions, $capitalizedExtensions);
    }

    /**
     * @test
     * @dataProvider mimeProvider
     *
     * @param string $file
     * @param string $type
     */
    public function it_can_determine_the_type_from_the_mime($file, $type)
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory($file))->toMediaLibrary();
        $this->assertEquals($type, $media->type_from_mime);
    }

    public static function mimeProvider()
    {
        return [
            ['image', Media::TYPE_IMAGE],
            ['test', Media::TYPE_OTHER],
            ['test.jpg', Media::TYPE_IMAGE],
            ['test.pdf', Media::TYPE_PDF],
            ['test.txt', Media::TYPE_OTHER]
        ];
    }

}
