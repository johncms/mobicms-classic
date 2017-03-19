<?php

namespace Mobicms;

use Psr\Container\ContainerInterface;

class ConfigFactory
{
    public function __invoke(ContainerInterface $container){
        return new Config($container->get('config')['mobicms']);
    }
}
