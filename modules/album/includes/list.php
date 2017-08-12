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

ob_start();

// Список альбомов юзера
if (isset($_SESSION['ap'])) {
    unset($_SESSION['ap']);
}

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Asset\Manager $asset */
$asset = $container->get(Mobicms\Asset\Manager::class);

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

echo '<div class="phdr"><a href="index.php"><b>' . _t('Photo Albums') . '</b></a> | ' . _t('Personal') . '</div>';
$req = $db->query("SELECT * FROM `cms_album_cat` WHERE `user_id` = '" . $user['id'] . "' " . ($user['id'] == $systemUser->id || $systemUser->rights >= 6 ? "" : "AND `access` > 1") . " ORDER BY `sort` ASC");
$total = $req->rowCount();

if ($user['id'] == $systemUser->id && $total < $max_album && empty($systemUser->ban) || $systemUser->rights >= 7) {
    echo '<div class="topmenu"><a href="?act=edit&amp;user=' . $user['id'] . '">' . _t('Create Album') . '</a></div>';
}

echo '<div class="user"><p>' . $tools->displayUser($user, ['iphide' => 1,]) . '</p></div>';

if ($total) {
    $i = 0;
    while ($res = $req->fetch()) {
        $count = $db->query("SELECT COUNT(*) FROM `cms_album_files` WHERE `album_id` = '" . $res['id'] . "'")->fetchColumn();
        echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
            $asset->img('album-' . $res['access'] . '.gif')->class('left') .'&#160;' .
            '<a href="?act=show&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '"><b>' . $tools->checkout($res['name']) . '</b></a>&#160;(' . $count . ')';

        if ($user['id'] == $systemUser->id || $systemUser->rights >= 6 || !empty($res['description'])) {
            $menu = [
                '<a href="?act=sort&amp;mod=up&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . _t('Up') . '</a>',
                '<a href="?act=sort&amp;mod=down&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . _t('Down') . '</a>',
                '<a href="?act=edit&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . _t('Edit') . '</a>',
                '<a href="?act=delete&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . _t('Delete') . '</a>',
            ];
            echo '<div class="sub">' .
                (!empty($res['description']) ? '<div class="gray">' . $tools->checkout($res['description'], 1, 1) . '</div>' : '') .
                ($user['id'] == $systemUser->id && empty($systemUser->ban) || $systemUser->rights >= 6 ? implode(' | ', $menu) : '') .
                '</div>';
        }

        echo '</div>';
        ++$i;
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';
