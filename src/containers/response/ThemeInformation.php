<?php

namespace PayXpert\Connect2Pay\containers\response;

class ThemeInformation
{
    /**
     * @var integer
     */
    private $themeId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var SeamlessLibraryInformation[]
     */
    private $seamlessLibraries;

    /**
     * The theme identifier
     *
     * @return string
     */
    public function getThemeId()
    {
        return $this->themeId;
    }

    public function setThemeId($themeId)
    {
        $this->themeId = $themeId;
        return $this;
    }

    /**
     * The theme name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A list of Seamless Javascript libraries available in the theme
     *
     * @return SeamlessLibraryInformation[]
     */
    public function getSeamlessLibraries()
    {
        return $this->seamlessLibraries;
    }

    public function setSeamlessLibraries($seamlessLibraries)
    {
        $this->seamlessLibraries = $seamlessLibraries;
        return $this;
    }
}