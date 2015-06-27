<?php

namespace Spatie\MediaLibrary;

class GlideManipulator
{
    protected $inputFile;

    protected $manipulation;

    protected $outputFile;

    /**
     * @param mixed $inputFile
     * @return $this
     */
    public function setInputFile($inputFile)
    {
        $this->inputFile = $inputFile;

        return $this;
    }

    /**
     * @param mixed $manipulation
     * @return $this
     */
    public function setManipulation($manipulation)
    {
        $this->manipulation = $manipulation;

        return $this;
    }

    /**
     * @param mixed $outputFile
     * @return $this
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    public function performManipulation()
    {
        (new GlideImage())
            ->load($this->inputFile, $this->manipulation)
            ->useAbsoluteSourceFilePath()
            ->save($this->outputFile);

        return true;
    }
}

