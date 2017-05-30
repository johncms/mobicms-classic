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

$klein = new Klein\Klein();
$klein->respond('GET', '/', function () {
    include ROOT_PATH . 'modules/homepage/index.php';
});

$klein->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body(
                'ERROR 404: Page not found.'
            );
            break;
        case 405:
            $router->response()->body(
                'You can\'t do that!'
            );
            break;
        default:
            $router->response()->body(
                'Oh no, a bad error happened that caused a ' . $code
            );
    }
});

$klein->dispatch(App::getContainer()->get(Mobicms\Http\Request::class));
