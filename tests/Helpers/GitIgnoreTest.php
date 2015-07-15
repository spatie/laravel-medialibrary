<?php

namespace Spatie\MediaLibrary\Test\Helpers;

use Spatie\MediaLibrary\Helpers\Gitignore;
use Spatie\MediaLibrary\Test\TestCase;

class GitIgnoreTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_a_gitignore_to_a_directory()
    {
        Gitignore::createIn($this->getTempDirectory());

        $this->assertFileExists($this->getTempDirectory('.gitignore'));

        $this->assertEquals(
            file_get_contents($this->getTempDirectory('.gitignore')),
            file_get_contents(__DIR__.'/../../resources/stubs/gitignore.txt')
        );
    }
}
