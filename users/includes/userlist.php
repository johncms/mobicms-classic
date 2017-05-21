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

$textl = _t('List of users');
$headmod = 'userlist';
require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $container->get(Mobicms\Api\UserInterface::class)->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

// Выводим список пользователей
$total = $db->query("SELECT COUNT(*) FROM `users` WHERE `preg` = 1")->fetchColumn();
echo '<div class="phdr"><a href="index.php"><b>' . _t('Community') . '</b></a> | ' . _t('List of users') . '</div>';

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=userlist&amp;', $start, $total, $userConfig->kmess) . '</div>';
}

$req = $db->query("SELECT `id`, `name`, `sex`, `lastdate`, `datereg`, `status`, `rights`, `ip`, `browser`, `rights` FROM `users` WHERE `preg` = 1 ORDER BY `datereg` DESC LIMIT $start, $userConfig->kmess");

for ($i = 0; ($res = $req->fetch()) !== false; $i++) {
    echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
    echo $tools->displayUser($res) . '</div>';
}

echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=userlist&amp;', $start, $total, $userConfig->kmess) . '</div>' .
        '<p><form action="index.php?act=userlist" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/>' .
        '</form></p>';
}

echo '<p><a href="search.php">' . _t('User Search') . '</a><br />' .
    '<a href="index.php">' . _t('Back') . '</a></p>';
