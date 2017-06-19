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

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

$headmod = 'users';

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

// Закрываем от неавторизованных юзеров
if (!$systemUser->isValid() && !$config->active) {
    require ROOT_PATH . 'system/head.php';
    echo $tools->displayError(_t('For registered users only'));
    require ROOT_PATH . 'system/end.php';
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
    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Mobicms\Counters $counters */
    $counters = $container->get('counters');

    // Актив сайта
    $textl = _t('Community');
    require ROOT_PATH . 'system/head.php';

    $brth = $db->query("SELECT COUNT(*) FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1'")->fetchColumn();
    $count_adm = $db->query("SELECT COUNT(*) FROM `users` WHERE `rights` > 0")->fetchColumn();

    echo '<div class="phdr"><b>' . _t('Community') . '</b></div>' .
        '<div class="gmenu"><form action="?act=search" method="post">' .
        '<p><h3><img src="../assets/images/search.png" width="16" height="16" class="left" />&#160;' . _t('Look for the User') . '</h3>' .
        '<input type="text" name="search"/>' .
        '<input type="submit" value="' . _t('Search') . '" name="submit" /><br />' .
        '<small>' . _t('The search is performed by Nickname and are case-insensitive.') . '</small></p></form></div>' .
        '<div class="menu"><p>' .
        $tools->image('images/contacts.png', ['width' => 16, 'height' => 16]) . '<a href="?act=userlist">' . _t('Users') . '</a> (' . $container->get('counters')->users() . ')<br />' .
        $tools->image('images/users.png', ['width' => 16, 'height' => 16]) . '<a href="?act=admlist">' . _t('Administration') . '</a> (' . $count_adm . ')<br>' .
        ($brth ? $tools->image('images/award.png', ['width' => 16, 'height' => 16]) . '<a href="?act=birth">' . _t('Birthdays') . '</a> (' . $brth . ')<br>' : '') .
        $tools->image('images/photo.gif', ['width' => 16, 'height' => 16]) . '<a href="../album/">' . _t('Photo Albums') . '</a> (' . $counters->album() . ')<br>' .
        $tools->image('images/rate.gif', ['width' => 16, 'height' => 16]) . '<a href="?act=top">' . _t('Top Activity') . '</a></p>' .
        '</div>';
}

require_once ROOT_PATH . 'system/end.php';
