<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\View;

use League\Plates\Engine;
use Mobicms\Api\ConfigInterface;
use Mobicms\Asset\PlatesExtension;
use Psr\Container\ContainerInterface;

class PlatesEngineFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $plates = new Engine;
        $plates->setFileExtension('phtml');

        // Load extensions
        $plates->loadExtension(new PlatesExtension($container));

        $plates->addFolder('system', ROOT_PATH . 'themes/default/templates');
        $plates->addData(['config' => $container->get(ConfigInterface::class)]);

        return $plates;
    }
}
