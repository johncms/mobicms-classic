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

/** @var Mobicms\Http\Response $response */
$response = $container->get(Mobicms\Http\Response::class);

/** @var Mobicms\Http\Router $router */
$router = $container->get(Mobicms\Http\Router::class);

// Главная страница
$router->respond('GET', '/', function () {
    include ROOT_PATH . 'modules/homepage/index.php';
});

// Гостевая
$router->respond(['GET', 'POST'], '@^/guestbook/', function () {
    include ROOT_PATH . 'modules/guestbook/index.php';
});

// Справка
$router->respond(['GET', 'POST'], '@^/help/', function () {
    include ROOT_PATH . 'modules/help/index.php';
});

// Гостевая
$router->respond(['GET', 'POST'], '@^/news/', function () {
    include ROOT_PATH . 'modules/news/index.php';
});

// Пользователи (актив сайта)
$router->respond(['GET', 'POST'], '@^/users/', function () {
    include ROOT_PATH . 'modules/users/index.php';
});

// Обработка ошибок
$router->onHttpError(function ($code, $router) use ($response) {
    switch ($code) {
        case 404:
            $response->body(
                'ERROR 404: Page not found.'
            );
            break;
        case 405:
            $response->body(
                'ERROR 404: You can\'t do that!'
            );
            break;
        default:
            $response->body(
                'ERROR: Oh no, a bad error happened that caused a ' . $code
            );
    }
});

// Запускаем Роутер
$router->dispatch($request, $response);
