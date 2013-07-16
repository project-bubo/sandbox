<?php

namespace Bubo\Media\TemplateContainers;

use Nette;

/**
 * Representation of image file in front end template
 */
class MediaImage extends Nette\Object {

    private $imageData;
    private $paths;

    public $presenter;

    public function __construct($imageData, $presenter)
    {
        $this->imageData = $imageData;
        $this->presenter = $presenter;
    }

    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    public function pathExists($index)
    {
        return isset($this->paths[$index]);
    }

    public function getDescription($name, $lang = NULL)
    {
        $returnText = NULL;

        $_lang = $lang === NULL ? $this->presenter->getFullLang() : $lang;
        $description = \Utils\MultiValues::unserialize($this->imageData['ext']);

        if ($description) {
            if (isset($description[$_lang][$name])) {
                $returnText = $description[$_lang][$name];
            }
        }

        return $returnText;
    }

    public function getDirPath($index)
    {
        return $this->pathExists($index) ? ($this->getMediaBaseDir() . '/' . $this->paths[$index]) : NULL;
    }

    public function getMediaBasePath()
    {
        return $this->presenter->mediaManagerService->getBasePath();
    }

    public function getMediaBaseDir()
    {
        return $this->presenter->mediaManagerService->getBaseDir();
    }

    public function getPath($index)
    {
        return $this->pathExists($index) ? ($this->getMediaBasePath() . '/' . $this->paths[$index]) : NULL;
    }

}