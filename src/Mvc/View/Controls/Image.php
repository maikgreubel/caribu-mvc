<?php
namespace Nkey\Caribu\Mvc\View\Controls;

use Nkey\Caribu\Mvc\View\Control;
use Nkey\Caribu\Mvc\Controller\Request;

/**
 * Image control
 *
 * @author Maik Greubel <greubel@nkey.de>
 *         This file is part of Caribu MVC package
 */
class Image implements Control
{
    /**
     * The url to the image
     *
     * @var string
     */
    private $imageUrl;

    /**
     * The alternative text if image can not be found
     *
     * @var string
     */
    private $alternateText;

    /**
     * Create a new image control
     *
     * @param string $imageUrl The url to the image
     */
    public function __construct($imageUrl = '', $alternativeText = '')
    {
        $this->setImageUrl($imageUrl);
        $this->setAlternativeText($alternativeText);
    }

    /**
     * Set the image url
     *
     * @param string $imageUrl The url to set
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * Set the alternative text
     *
     * @param string $text The text to set
     */
    public function setAlternativeText($text)
    {
        $this->alternateText = $text;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\Control::render()
     */
    public function render(Request $request, $parameters = array())
    {
        $rendered = sprintf('<img src="%s" alt="%s"/>', $this->imageUrl, $this->alternateText);

        return $rendered;
    }
}
