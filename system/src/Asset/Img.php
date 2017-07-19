<?php

namespace Mobicms\Asset;

/**
 * Class Img
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

    public function __construct($src)
    {
        $this->attribute['src'] = 'src="' . $src . '"';
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
        $this->attribute['hidden'] = 'hidden';
        return $this;
    }
}
