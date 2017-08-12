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

$pageTitle = _t('Top Activity');
ob_start();

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

// Функция отображения списков
function get_top($order = 'postforum', PDO $db, $tools)
{
    $req = $db->query("SELECT * FROM `users` WHERE `$order` > 0 ORDER BY `$order` DESC LIMIT 9");

    if ($req->rowCount()) {
        $out = '';
        $i = 0;

        while ($res = $req->fetch()) {
            $out .= $i % 2 ? '<div class="list2">' : '<div class="list1">';
            $out .= $tools->displayUser($res, ['header' => ('<b>' . $res[$order]) . '</b>']) . '</div>';
            ++$i;
        }

        return $out;
    } else {
        return '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
    }
}

// Меню выбора
$menu = [
    (!$mod ? '<b>' . _t('Forum') . '</b>' : '<a href="?act=top">' . _t('Forum') . '</a>'),
    ($mod == 'guest' ? '<b>' . _t('Guestbook') . '</b>' : '<a href="?act=top&amp;mod=guest">' . _t('Guestbook') . '</a>'),
    ($mod == 'comm' ? '<b>' . _t('Comments') . '</b>' : '<a href="?act=top&amp;mod=comm">' . _t('Comments') . '</a>'),
];

switch ($mod) {
    case 'guest':
        // Топ Гостевой
        echo '<div class="phdr"><a href="."><b>' . _t('Community') . '</b></a> | ' . _t('Most active in Guestbook') . '</div>';
        echo '<div class="topmenu">' . implode(' | ', $menu) . '</div>';
        echo get_top('postguest', $db, $tools);
        echo '<div class="phdr"><a href="../guestbook/">' . _t('Guestbook') . '</a></div>';
        break;

    case 'comm':
        // Топ комментариев
        echo '<div class="phdr"><a href="."><b>' . _t('Community') . '</b></a> | ' . _t('Most commentators') . '</div>';
        echo '<div class="topmenu">' . implode(' | ', $menu) . '</div>';
        echo get_top('komm', $db, $tools);
        echo '<div class="phdr">&#160;</div>';
        break;

    default:
        // Топ Форума
        echo '<div class="phdr"><a href="."><b>' . _t('Community') . '</b></a> | ' . _t('Most active in Forum') . '</div>';
        echo '<div class="topmenu">' . implode(' | ', $menu) . '</div>';
        echo get_top('postforum', $db, $tools);
        echo '<div class="phdr"><a href="../forum/">' . _t('Forum') . '</a></div>';
}

echo '<p><a href=".">' . _t('Back') . '</a></p>';
