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

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);

// Закрываем от неавторизованных юзеров
if (!$systemUser->isValid() && !$config->active) {
    echo $view->render('system::app/legacy', [
        'content' => $tools->displayError(_t('For registered users only')),
    ]);
    exit;
}

// Переключаем режимы работы
$array = [
    'admlist',
    'birth',
    'online',
    'search',
    'top',
    'userlist',
];

if (in_array($act, $array) && file_exists(__DIR__ . '/includes/' . $act . '.php')) {
    require_once __DIR__ . '/includes/' . $act . '.php';
} else {
    /** @var Mobicms\Asset\Manager $asset */
    $asset = $container->get(Mobicms\Asset\Manager::class);

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Mobicms\Deprecated\Counters $counters */
    $counters = $container->get('counters');

    // Актив сайта
    ob_start();

    $brth = $db->query("SELECT COUNT(*) FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1'")->fetchColumn();
    $count_adm = $db->query("SELECT COUNT(*) FROM `users` WHERE `rights` > 0")->fetchColumn();

    echo '<div class="phdr"><b>' . _t('Community') . '</b></div>' .
        '<div class="gmenu"><form action="?act=search" method="post">' .
        '<p><h3>' . $asset->img('search.png')->class('left') . '&#160;' . _t('Look for the User') . '</h3>' .
        '<input type="text" name="search"/>' .
        '<input type="submit" value="' . _t('Search') . '" name="submit" /><br />' .
        '<small>' . _t('The search is performed by Nickname and are case-insensitive.') . '</small></p></form></div>' .
        '<div class="menu"><p>' .
        $asset->img('contacts.png')->class('icon') . '<a href="?act=userlist">' . _t('Users') . '</a> (' . $container->get('counters')->users() . ')<br />' .
        $asset->img('users.png')->class('icon') . '<a href="?act=admlist">' . _t('Administration') . '</a> (' . $count_adm . ')<br>' .
        ($brth ? $asset->img('award.png')->class('icon') . '<a href="?act=birth">' . _t('Birthdays') . '</a> (' . $brth . ')<br>' : '') .
        $asset->img('photo.gif')->class('icon') . '<a href="../album/">' . _t('Photo Albums') . '</a> (' . $counters->album() . ')<br>' .
        $asset->img('rate.gif')->class('icon') . '<a href="?act=top">' . _t('Top Activity') . '</a></p>' .
        '</div>';
}

echo $view->render('system::app/legacy', [
    'title'   => _t('Community'),
    'content' => ob_get_clean(),
]);
