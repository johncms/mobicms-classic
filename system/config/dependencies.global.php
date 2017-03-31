<?php

return [
    'dependencies' => [
        'factories' => [
            Mobicms\Api\BbcodeInterface::class      => Mobicms\Bbcode::class,
            Mobicms\Api\ConfigInterface::class      => Mobicms\Config\ConfigFactory::class,
            Mobicms\Api\EnvironmentInterface::class => Mobicms\Environment::class,
            Mobicms\Api\ToolsInterface::class       => Mobicms\Tools::class,
            Mobicms\Api\UserInterface::class        => Mobicms\UserFactory::class,
            PDO::class                              => Mobicms\Database\PdoFactory::class,

            'counters' => Mobicms\Counters::class,
        ],

        'aliases' => [],
    ],
];
