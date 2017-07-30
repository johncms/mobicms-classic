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

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Psr\Container\ContainerInterface;

class PlatesExtension implements ExtensionInterface
{
    /**
     * @var Manager
     */
    private $assetManager;

    public function __construct(ContainerInterface $container)
    {
        $this->assetManager = $container->get(Manager::class);
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('asset', [$this->assetManager, 'getAsset']);
    }
}
