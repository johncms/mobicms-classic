<?php

namespace Mobicms\Asset;

/**
 * The <img> tag builder
 *
 * @package Mobicms\Asset
 * @author  Oleg Kasyanov <dev@mobicms.net>
 *
 * @method $this alt(string $alt)
 * @method $this height(int $height)
 * @method $this lowsrc(string $lowsrc)
 * @method $this width(int $width)
 * @method $this usemap(string $usemap)
 *
 * @method $this class(string $class)
 * @method $this id(string $id)
 * @method $this style(string $style)
 * @method $this title(string $title)
 */
class Img
{
    private $attribute;
    private $xhtml;

    public function __construct($src, $xhtml = false)
    {
        $this->attribute['src'] = 'src="' . $src . '"';
        $this->xhtml = $xhtml;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $arg = isset($arguments[0]) ? filter_var($arguments[0], FILTER_SANITIZE_SPECIAL_CHARS) : '';
        $this->attribute[$name] = $name . '="' . $arg . '"';

        return $this;
    }

    public function __toString()
    {
        if (!isset($this->attribute['alt'])) {
            $this->attribute['alt'] = 'alt=""';
        }

        return '<img ' . implode(' ', $this->attribute) . '>';
    }

    public function hidden()
    {
        $this->attribute['hidden'] = $this->xhtml ? 'hidden="hidden"' : 'hidden';

        return $this;
    }
}
