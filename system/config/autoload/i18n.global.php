<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

use Zend\I18n\Translator;

return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'        => 'gettext',
                'base_dir'    => ROOT_PATH . 'system/locale',
                'pattern'     => '/%s/system.mo',
                'text_domain' => 'system',
            ],
        ],
    ],

    'dependencies' => [
        'aliases' => [
            'TranslatorPluginManager'             => Translator\LoaderPluginManager::class,
            Translator\TranslatorInterface::class => Translator\Translator::class,
        ],

        'factories' => [
            Translator\Translator::class          => Translator\TranslatorServiceFactory::class,
            Translator\LoaderPluginManager::class => Translator\LoaderPluginManagerFactory::class,
        ],
    ],
];
