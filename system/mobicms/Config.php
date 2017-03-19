<?php

namespace Mobicms;

use Zend\Stdlib\ArrayObject;

/**
 * Class Config
 *
 * @package Johncms
 *
 */
class Config extends ArrayObject implements Api\ConfigInterface
{
    public function __construct(array $input)
    {
        parent::__construct($input, parent::ARRAY_AS_PROPS);
    }
}
