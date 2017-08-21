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

$error = [];
$search_post = isset($_POST['search']) ? trim($_POST['search']) : false;
$search_get = isset($_GET['search']) ? rawurldecode(trim($_GET['search'])) : false;
$search = $search_post ? $search_post : $search_get;

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $container->get(Mobicms\Api\UserInterface::class)->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

if (isset($_GET['ip'])) {
    $search = trim($_GET['ip']);
}

echo '<div class="phdr"><a href="index.php"><b>' . _t('Admin Panel') . '</b></a> | ' . _t('Search IP') . '</div>' .
    '<form action="index.php?act=search_ip" method="post"><div class="gmenu"><p>' .
    '<input type="text" name="search" value="' . $tools->checkout($search) . '" />' .
    '<input type="submit" value="' . _t('Search') . '" name="submit" /><br>' .
    '</p></div></form>';

if ($search) {
    if (strstr($search, '-')) {
        // Обрабатываем диапазон адресов
        $array = explode('-', $search);
        $ip = trim($array[0]);

        if (!preg_match('#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#', $ip)) {
            $error[] = _t('First IP is entered incorrectly');
        } else {
            $ip1 = $ip;
        }

        $ip = trim($array[1]);

        if (!preg_match('#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#', $ip)) {
            $error[] = _t('Second IP is entered incorrectly');
        } else {
            $ip2 = $ip;
        }
    } elseif (strstr($search, '*')) {
        // Обрабатываем адреса с маской
        $array = explode('.', $search);

        for ($i = 0; $i < 4; $i++) {
            if (!isset($array[$i]) || $array[$i] == '*') {
                $ipt1[$i] = '0';
                $ipt2[$i] = '255';
            } elseif (is_numeric($array[$i]) && $array[$i] >= 0 && $array[$i] <= 255) {
                $ipt1[$i] = $array[$i];
                $ipt2[$i] = $array[$i];
            } else {
                $error = _t('Invalid IP');
            }
        }

            $ip1 = $ipt1[0] . '.' . $ipt1[1] . '.' . $ipt1[2] . '.' . $ipt1[3];
            $ip2 = $ipt2[0] . '.' . $ipt2[1] . '.' . $ipt2[2] . '.' . $ipt2[3];
    } else {
        // Обрабатываем одиночный адрес
        if (!preg_match('#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#', $search)) {
            $error = _t('Invalid IP');
        } else {
            $ip1 = $search;
            $ip2 = $ip1;
        }
    }
}

if ($search && !$error) {
    /** @var PDO $db */
    $db = $container->get(PDO::class);

    // Выводим результаты поиска
    echo '<div class="phdr">' . _t('Search results') . '</div>';
    $data = [$ip1, $ip2, $ip1, $ip2];
    $req = $db->prepare('SELECT COUNT(*) FROM `users` WHERE `ip` BETWEEN ? AND ? OR `ip_via_proxy` BETWEEN ? AND ?');
    $req->execute($data);
    $total = $req->fetchColumn();

    if ($total > $userConfig->kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=search_ip&amp;search=' . urlencode($search) . '&amp;', $total) . '</div>';
    }

    if ($total) {
        $req = $db->prepare('SELECT * FROM `users` WHERE `ip` BETWEEN ? AND ? OR `ip_via_proxy` BETWEEN ? AND ? ORDER BY `ip` ASC, `name` ASC' . $tools->getPgStart(true));
        $req->execute($data);
        $i = 0;

        while ($res = $req->fetch()) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            echo $tools->displayUser($res, ['iphist' => 1]);
            echo '</div>';
            ++$i;
        }
    } else {
        echo '<div class="menu"><p>' . _t('At your request, nothing found') . '</p></div>';
    }

    echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $userConfig->kmess) {
        // Навигация по страницам
        echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=search_ip&amp;search=' . urlencode($search) . '&amp;', $total) . '</div>' .
            '<p><form action="index.php?act=search_ip&amp;search=' . urlencode($search) . '" method="post">' .
            '<input type="text" name="page" size="2"/><input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/>' .
            '</form></p>';
    }
    echo '<p><a href="index.php?act=search_ip">' . _t('New Search') . '</a><br><a href="index.php">' . _t('Admin Panel') . '</a></p>';
} else {
    // Выводим сообщение об ошибке
    if ($error) {
        echo $tools->displayError($error);
    }

    // Инструкции для поиска
    echo '<div class="phdr"><small>' . _t('<b>Sample queries:</b><br><span class="red">10.5.7.1</span> - Search for a single address<br><span class="red">10.5.7.1-10.5.7.100</span> - Search a range address (forbidden to use mask symbol *)<br><span class="red">10.5.*.*</span> - Search mask. Will be found all subnet addresses starting with 0 and ending with 255') . '</small></div>';
    echo '<p><a href="index.php">' . _t('Admin Panel') . '</a></p>';
}
