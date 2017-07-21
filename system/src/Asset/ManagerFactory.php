<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Asset;

use Psr\Container\ContainerInterface;

class ManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $assetsPath = isset($config['mobicms_global']['assets_url']) ? $config['mobicms_global']['assets_url'] : '';

        return new Manager($assetsPath);
    }
}
