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
use Psr\Container\ContainerInterface;

class PlatesEngineFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $plates = new Engine;
        $plates->setFileExtension('phtml');
        $plates->setDirectory(ROOT_PATH . 'themes/default/templates');

        return $plates;
    }
}
