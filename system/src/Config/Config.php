<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Config;

use Mobicms\Api\ConfigInterface;
use Zend\Stdlib\ArrayObject;

class Config extends ArrayObject implements ConfigInterface
{
    public function __construct(array $input)
    {
        parent::__construct($input, parent::ARRAY_AS_PROPS);
    }
}
