<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

define('MOBICMS', 1);

require('system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Http\Request $request */
$request = $container->get(Mobicms\Http\Request::class);

require CONFIG_PATH . 'routes.php';

/** @var FastRoute\Dispatcher $dispatcher */
$dispatcher = $container->get(FastRoute\Dispatcher::class);
$match = $dispatcher->dispatch($request->method(), rawurldecode($request->pathname()));

switch ($match[0]) {
    case FastRoute\Dispatcher::FOUND:
        if (is_callable($match[1])) {
            call_user_func_array($match[1], $match[2]);
        } else {
            include ROOT_PATH . $match[1];
        }
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        echo '405 Method Not Allowed';
        break;

    default:
        echo '404 Not Found';
}
