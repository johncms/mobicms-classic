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

use Psr\Container\ContainerInterface;

class ConfigFactory
{
    public function __invoke(ContainerInterface $container){
        return new Config($container->get('config')['mobicms']);
    }
}
