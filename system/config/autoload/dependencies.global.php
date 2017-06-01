<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

return [
    'dependencies' => [
        'factories' => [
            Mobicms\Api\BbcodeInterface::class      => Mobicms\Bbcode::class,
            Mobicms\Api\ConfigInterface::class      => Mobicms\Config\ConfigFactory::class,
            Mobicms\Api\EnvironmentInterface::class => Mobicms\Environment::class,
            Mobicms\Http\Request::class             => Mobicms\Http\RequestFactory::class,
            Mobicms\Http\Response::class            => Mobicms\Http\ResponseFactory::class,
            Mobicms\Http\Router::class              => Mobicms\Http\RouterFactory::class,
            Mobicms\Api\ToolsInterface::class       => Mobicms\Tools\Utilites::class,
            Mobicms\Api\UserInterface::class        => Mobicms\Checkpoint\UserFactory::class,
            PDO::class                              => Mobicms\Database\PdoFactory::class,

            'counters' => Mobicms\Counters::class,
        ],

        'aliases' => [],
    ],
];
