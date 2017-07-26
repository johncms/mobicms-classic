<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

defined('MOBICMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);
$view->addFolder('redirect', __DIR__ . '/templates/');

$url = isset($_REQUEST['url']) ? strip_tags(rawurldecode(trim($_REQUEST['url']))) : false;

if ($url) {
    // Редирект по ссылкам в текстах, обработанным функцией tags()
    if (isset($_POST['submit'])) {
        header('Location: ' . $url);
    } else {
        echo $view->render('redirect::index', [
            'redirect_url' => rawurlencode($url),
            'referer'      => (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : '/'),
            'url'          => $url,
        ]);
    }
}
