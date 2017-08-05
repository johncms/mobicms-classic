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

/** @var Mobicms\Asset\Manager $asset */
$asset = $container->get(Mobicms\Asset\Manager::class);

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $systemUser->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

switch ($mod) {
    case 'amnesty':
        if ($systemUser->rights < 9) {
            echo $tools->displayError(_t('Amnesty is available for supervisors only'));
        } else {
            echo '<div class="phdr"><a href="index.php?act=ban_panel"><b>' . _t('Ban Panel') . '</b></a> | ' . _t('Amnesty') . '</div>';

            if (isset($_POST['submit'])) {
                $term = isset($_POST['term']) && $_POST['term'] == 1 ? 1 : 0;

                if ($term) {
                    // Очищаем таблицу Банов
                    $db->query("TRUNCATE TABLE `cms_ban_users`");
                    echo '<div class="gmenu"><p>' . _t('Amnesty has been successful') . '</p></div>';
                } else {
                    // Разбаниваем активные Баны
                    $req = $db->query("SELECT * FROM `cms_ban_users` WHERE `ban_time` > '" . time() . "'");

                    while ($res = $req->fetch()) {
                        $ban_left = $res['ban_time'] - time();

                        if ($ban_left < 2592000) {
                            $amnesty_msg = _t('Amnesty');
                            $db->exec("UPDATE `cms_ban_users` SET `ban_time`='" . time() . "', `ban_raz`='--$amnesty_msg--' WHERE `id` = '" . $res['id'] . "'");
                        }
                    }

                    echo '<div class="gmenu"><p>' . _t('All the users with active bans were unbanned (Except for bans &quot;till cancel&quot;)') . '</p></div>';
                }
            } else {
                echo '<form action="index.php?act=ban_panel&amp;mod=amnesty" method="post"><div class="menu"><p>' .
                    '<input type="radio" name="term" value="0" checked="checked" />&#160;' . _t('Unban all') . '<br>' .
                    '<input type="radio" name="term" value="1" />&#160;' . _t('Clear Ban database') .
                    '</p><p><input type="submit" name="submit" value="' . _t('Amnesty') . '" />' .
                    '</p></div></form>' .
                    '<div class="phdr"><small>' . _t('&quot;Unban All&quot; - terminating all active bans<br>&quot;Clear Database&quot; - terminates all bans and clears an offenses history') . '</small></div>';
            }

            echo '<p><a href="index.php?act=ban_panel">' . _t('Ban Panel') . '</a><br><a href="index.php">' . _t('Admin Panel') . '</a></p>';
        }
        break;

    default:
        // БАН-панель, список нарушителей
        echo '<div class="phdr"><a href="index.php"><b>' . _t('Admin Panel') . '</b></a> | ' . _t('Ban Panel') . '</div>';
        echo '<div class="topmenu"><span class="gray">' . _t('Sort') . ':</span> ';

        if (isset($_GET['count'])) {
            echo '<a href="index.php?act=ban_panel">' . _t('Term') . '</a> | ' . _t('Violations') . '</div>';
        } else {
            echo _t('Term') . ' | <a href="index.php?act=ban_panel&amp;count">' . _t('Violations') . '</a></div>';
        }

        $sort = isset($_GET['count']) ? 'bancount' : 'bantime';
        $total = $db->query("SELECT `user_id` FROM `cms_ban_users` GROUP BY `user_id`")->rowCount();

        $req = $db->query("
          SELECT COUNT(`cms_ban_users`.`user_id`) AS `bancount`, MAX(`cms_ban_users`.`ban_time`) AS `bantime`, `cms_ban_users`.`id` AS `ban_id`, `users`.*
          FROM `cms_ban_users` LEFT JOIN `users` ON `cms_ban_users`.`user_id` = `users`.`id`
          GROUP BY `user_id`
          ORDER BY `$sort` DESC" . $tools->getPgStart(true));

        if ($req->rowCount()) {
            while ($res = $req->fetch()) {
                echo '<div class="' . ($res['bantime'] > time() ? 'r' : '') . 'menu">';
                $arg = [
                    'header' => '<br>' . $asset->img('info.png')->class('icon') . '&#160;<small><a href="../profile/?act=ban&amp;user=' . $res['id'] . '">' . _t('Violations history') . '</a> [' . $res['bancount'] . ']</small>',
                ];
                echo $tools->displayUser($res, $arg);
                echo '</div>';
            }
        } else {
            echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
        }

        echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

        if ($total > $userConfig->kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=ban_panel&amp;', $total) . '</div>';
            echo '<p><form action="index.php?act=ban_panel" method="post"><input type="text" name="page" size="2"/><input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
        }

        echo '<p>' . ($systemUser->rights == 9 && $total
                ? '<a href="index.php?act=ban_panel&amp;mod=amnesty">' . _t('Amnesty') . '</a><br>'
                : '')
            . '<a href="index.php">' . _t('Admin Panel') . '</a></p>';
}
