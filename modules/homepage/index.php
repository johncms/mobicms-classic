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

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);
$view->addFolder('homepage', __DIR__ . '/templates/');

$mp = new Mobicms\Deprecated\NewsWidget;

$data = [
    'counters'      => $container->get('counters'),
    'news'          => $mp->news,
    'newscount'     => $mp->newscount,
    'showGuestbook' => ($config->mod_guest || $systemUser->rights >= 7 ?: false),
    'showForum'     => ($config->mod_forum || $systemUser->rights >= 7 ?: false),
    'showDownloads' => ($config->mod_down || $systemUser->rights >= 7 ?: false),
    'showLibrary'   => ($config->mod_lib || $systemUser->rights >= 7 ?: false),
    'showActive'    => ($systemUser->isValid() || $config->active ?: false),
];

echo $view->render('homepage::mainmenu', $data);
