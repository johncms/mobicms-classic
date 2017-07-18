<?php

namespace Mobicms\Asset;

class Img
{
    private $image;

    public function __construct($src)
    {
        $this->image['src'] = 'src="' . $src . '"';
    }

    public function alt($alt)
    {
        $this->image['alt'] = 'alt="' . $alt . '"';
    }

    public function height($height)
    {
        $this->image['height'] = 'height="' . $height . '"';

        return $this;
    }

    public function width($width)
    {
        $this->image['width'] = 'width="' . $width . '"';

        return $this;
    }

    public function __toString()
    {
        if (!isset($this->image['alt'])) {
            $this->image['alt'] = 'alt=""';
        }

        return '<img ' . implode(' ', $this->image) . '/>';
    }
}
