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

$pageTitle = _t('Online');
require ROOT_PATH . 'system/head.php';

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Asset\Manager $asset */
$asset = $container->get(Mobicms\Asset\Manager::class);

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\EnvironmentInterface $env */
$env = App::getContainer()->get(Mobicms\Api\EnvironmentInterface::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $systemUser->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

$start = $tools->getPgStart();

// Показываем список Online
$menu[] = !$mod ? '<b>' . _t('Users') . '</b>' : '<a href="?act=online">' . _t('Users') . '</a>';
$menu[] = $mod == 'history' ? '<b>' . _t('History') . '</b>' : '<a href="?act=online&amp;mod=history">' . _t('History') . '</a> ';

if ($systemUser->rights) {
    $menu[] = $mod == 'guest' ? '<b>' . _t('Guests') . '</b>' : '<a href="?act=online&amp;mod=guest">' . _t('Guests') . '</a>';
    $menu[] = $mod == 'ip' ? '<b>' . _t('IP Activity') . '</b>' : '<a href="?act=online&amp;mod=ip">' . _t('IP Activity') . '</a>';
}

echo '<div class="phdr"><b>' . _t('Who is online?') . '</b></div>' .
    '<div class="topmenu">' . implode(' | ', $menu) . '</div>';

switch ($mod) {
    case 'history':
        // История посетилелей за последние 2 суток
        $sql_total = "SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 172800 . " AND `lastdate` < " . (time() - 310));
        $sql_list = "SELECT * FROM `users` WHERE `lastdate` > " . (time() - 172800) . " AND `lastdate` < " . (time() - 310) . " ORDER BY `sestime` DESC LIMIT ";
        break;

    case 'ip':
        if ($systemUser->rights) {
            // Список активных IP, со счетчиком обращений
            $ip_array = array_count_values($env->getIpLog());
            $total = count($ip_array);
            $requestip = ip2long($container->get(Mobicms\Http\Request::class)->ip());

            if ($start >= $total) {
                // Исправляем запрос на несуществующую страницу
                $start = max(0, $total - (($total % $userConfig->kmess) == 0 ? $userConfig->kmess : ($total % $userConfig->kmess)));
            }

            $end = $start + $userConfig->kmess;

            if ($end > $total) {
                $end = $total;
            }

            arsort($ip_array);
            $i = 0;

            foreach ($ip_array as $key => $val) {
                $ip_list[$i] = [$key => $val];
                ++$i;
            }

            if ($total && $systemUser->rights) {
                if ($total > $userConfig->kmess) {
                    echo '<div class="topmenu">' . $tools->displayPagination('?act=online&amp;mod=ip&amp;', $total, null, $start) . '</div>';
                }

                for ($i = $start; $i < $end; $i++) {
                    $out = each($ip_list[$i]);
                    $ip = long2ip($out[0]);

                    if ($out[0] == $requestip) {
                        echo '<div class="gmenu">';
                    } else {
                        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    }

                    echo '[' . $out[1] . ']&#160;&#160;<a href="' . $config->homeurl . '/admin/?act=search_ip&amp;ip=' . $ip . '">' . $ip . '</a>' .
                        '&#160;&#160;<small>[<a href="' . $config->homeurl . '/admin/?act=ip_whois&amp;ip=' . $ip . '">?</a>]</small></div>';
                }

                echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

                if ($total > $userConfig->kmess) {
                    echo '<div class="topmenu">' . $tools->displayPagination('?act=online&amp;mod=ip&amp;', $total) . '</div>' .
                        '<p><form action="?act=online&amp;mod=ip" method="post">' .
                        '<input type="text" name="page" size="2"/>' .
                        '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
                }
            }

            require ROOT_PATH . 'system/end.php';
            exit;
            break;
        }

    case 'guest':
        if ($systemUser->rights) {
            // Список гостей Онлайн
            $sql_total = "SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300);
            $sql_list = "SELECT * FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300) . " LIMIT ";
            break;
        }

    default:
        // Список посетителей Онлайн
        $sql_total = "SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 300);
        $sql_list = "SELECT * FROM `users` WHERE `lastdate` > " . (time() - 300) . " ORDER BY `name` ASC LIMIT ";
}

$total = $db->query($sql_total)->fetchColumn();

// Исправляем запрос на несуществующую страницу
if ($start >= $total) {
    $start = max(0, $total - (($total % $userConfig->kmess) == 0 ? $userConfig->kmess : ($total % $userConfig->kmess)));
}

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=online&amp;' . ($mod ? 'mod=' . $mod . '&amp;' : ''), $total) . '</div>';
}

if ($total) {
    $req = $db->query($sql_list . "$start, $userConfig->kmess");
    $i = 0;

    while ($res = $req->fetch()) {
        $res['id'] = isset($res['id']) ? $res['id'] : 0;

        if ($res['id'] == $systemUser->id) {
            echo '<div class="gmenu">';
        } else {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        }

        $arg['stshide'] = 1;
        $arg['header'] = ' <span class="gray">(';

        if ($mod == 'history') {
            $arg['header'] .= $tools->displayDate($res['lastdate']);
        } else {
            $arg['header'] .= $tools->timecount(time() - $res['sestime']);
        }

        $arg['header'] .= ')</span><br />' . $asset->img('info.png')->class('icon') . $tools->displayPlace($res['place'], $res['id']);
        echo $tools->displayUser($res, $arg);
        echo '</div>';
        ++$i;
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=online&amp;' . ($mod ? 'mod=' . $mod . '&amp;' : ''), $total) . '</div>' .
        '<p><form action="?act=online' . ($mod ? '&amp;mod=' . $mod : '') . '" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/>' .
        '</form></p>';
}
