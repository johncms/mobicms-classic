<?php

return [
    'dependencies' => [
        'factories' => [
            Mobicms\Api\BbcodeInterface::class      => Mobicms\Bbcode::class,
            Mobicms\Api\ConfigInterface::class      => Mobicms\ConfigFactory::class,
            Mobicms\Api\EnvironmentInterface::class => Mobicms\Environment::class,
            Mobicms\Api\ToolsInterface::class       => Mobicms\Tools::class,
            Mobicms\Api\UserInterface::class        => Mobicms\UserFactory::class,
            PDO::class                              => Mobicms\PdoFactory::class,

            'counters' => Mobicms\Counters::class,
        ],

        // DEPRECATED!!!
        // Данные псевдонимы запрещены к использованию и будут удалены в ближайших версиях.
        // В своих разработках используйте вызов соответствующих интерфейсов
        'aliases' => [
            Mobicms\User::class => Mobicms\Api\UserInterface::class,
            'bbcode'            => Mobicms\Api\BbcodeInterface::class,
            'env'               => Mobicms\Api\EnvironmentInterface::class,
            'tools'             => Mobicms\Api\ToolsInterface::class,
        ],
    ],
];
