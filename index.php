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

/** @var Mobicms\Http\Router $router */
$router = $container->get(Mobicms\Http\Router::class);

$router->respond('GET', '/', function () {
    include ROOT_PATH . 'modules/homepage/index.php';
});

$router->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body(
                'ERROR 404: Page not found.'
            );
            break;
        case 405:
            $router->response()->body(
                'ERROR 404: You can\'t do that!'
            );
            break;
        default:
            $router->response()->body(
                'ERROR: Oh no, a bad error happened that caused a ' . $code
            );
    }
});

$router->dispatch($request);
