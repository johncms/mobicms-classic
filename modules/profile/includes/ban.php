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
$ban = isset($_GET['ban']) ? intval($_GET['ban']) : 0;

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

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

$pageTitle = htmlspecialchars($user['name']) . ': ' . _t('Ban Panel');

switch ($mod) {
    case 'do':
        // Баним пользователя (добавляем Бан в базу)
        if ($systemUser->rights < 1 || ($systemUser->rights < 6 && $user['rights']) || ($systemUser->rights <= $user['rights'])) {
            echo $tools->displayError(_t('You do not have enought rights to ban this user'));
        } else {
            echo '<div class="phdr"><b>' . _t('Ban the User') . '</b></div>';
            echo '<div class="rmenu"><p>' . $tools->displayUser($user) . '</p></div>';

            if (isset($_POST['submit'])) {
                $error = false;
                $term = isset($_POST['term']) ? intval($_POST['term']) : false;
                $timeval = isset($_POST['timeval']) ? intval($_POST['timeval']) : false;
                $time = isset($_POST['time']) ? intval($_POST['time']) : false;
                $reason = !empty($_POST['reason']) ? trim($_POST['reason']) : '';
                $banref = isset($_POST['banref']) ? intval($_POST['banref']) : false;

                if (empty($reason) && empty($banref)) {
                    $reason = _t('Reason not specified');
                }

                if (empty($term) || empty($timeval) || empty($time) || $timeval < 1) {
                    $error = _t('There is no required data');
                }

                if ($systemUser->rights == 1 && $term != 14 || $systemUser->rights == 2 && $term != 12 || $systemUser->rights == 3 && $term != 11 || $systemUser->rights == 4 && $term != 16 || $systemUser->rights == 5 && $term != 15) {
                    $error = _t('You have no rights to ban in this section');
                }

                if ($db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "' AND `ban_time` > '" . time() . "' AND `ban_type` = '$term'")->fetchColumn()) {
                    $error = _t('Ban already active');
                }

                switch ($time) {
                    case 2:
                        // Часы
                        if ($timeval > 24) {
                            $timeval = 24;
                        }
                        $timeval = $timeval * 3600;
                        break;

                    case 3:
                        // Дни
                        if ($timeval > 30) {
                            $timeval = 30;
                        }
                        $timeval = $timeval * 86400;
                        break;

                    case 4:
                        // До отмены (на 10 лет)
                        $timeval = 315360000;
                        break;

                    default:
                        // Минуты
                        if ($timeval > 60) {
                            $timeval = 60;
                        }
                        $timeval = $timeval * 60;
                }

                if ($systemUser->rights < 6 && $timeval > 86400) {
                    $timeval = 86400;
                }

                if ($systemUser->rights < 7 && $timeval > 2592000) {
                    $timeval = 2592000;
                }

                if (!$error) {
                    // Заносим в базу
                    $stmt = $db->prepare('INSERT INTO `cms_ban_users` SET
                      `user_id` = ?,
                      `ban_time` = ?,
                      `ban_while` = ?,
                      `ban_type` = ?,
                      `ban_who` = ?,
                      `ban_reason` = ?
                    ');

                    $stmt->execute([
                        $user['id'],
                        (time() + $timeval),
                        time(),
                        $term,
                        $systemUser->name,
                        $reason,
                    ]);

                    echo '<div class="rmenu"><p><h3>' . _t('User banned') . '</h3></p></div>';
                } else {
                    echo $tools->displayError($error);
                }
            } else {
                // Форма параметров бана
                echo '<form action="?act=ban&amp;mod=do&amp;user=' . $user['id'] . '" method="post">' .
                    '<div class="menu"><p><h3>' . _t('Ban type') . '</h3>';

                if ($systemUser->rights >= 6) {
                    // Блокировка
                    echo '<div><input name="term" type="radio" value="1" checked="checked" />&#160;' . _t('Full block') . '</div>';
                    // Приват
                    echo '<div><input name="term" type="radio" value="3" />&#160;' . _t('Private messages') . '</div>';
                    // Комментарии
                    echo '<div><input name="term" type="radio" value="10" />&#160;' . _t('Comments') . '</div>';
                    // Гостевая
                    echo '<div><input name="term" type="radio" value="13" />&#160;' . _t('Guestbook') . '</div>';
                }

                if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
                    // Форум
                    echo '<div><input name="term" type="radio" value="11" ' . ($systemUser->rights == 3 ? 'checked="checked"'
                            : '') . '/>&#160;' . _t('Forum') . '</div>';
                }

                if ($systemUser->rights == 5 || $systemUser->rights >= 6) {
                    // Библиотека
                    echo '<div><input name="term" type="radio" value="15" />&#160;' . _t('Library') . '</div>';
                }

                echo '</p><p><h3>' . _t('Ban time') . '</h3>' .
                    '&#160;<input type="text" name="timeval" size="2" maxlength="2" value="12"/><br>' .
                    '<input name="time" type="radio" value="1" />&#160;' . _t('Minutes (60 max.)') . '<br />' .
                    '<input name="time" type="radio" value="2" checked="checked" />&#160;' . _t('Hours (24 max.)') . '<br />';

                if ($systemUser->rights >= 6) {
                    echo '<input name="time" type="radio" value="3" />&#160;' . _t('Days (30 max.)') . '<br />';
                }

                if ($systemUser->rights >= 7) {
                    echo '<input name="time" type="radio" value="4" />&#160;<span class="red">' . _t('Till cancel') . '</span>';
                }

                echo '</p><p><h3>' . _t('Reason') . '</h3>';

                if (isset($_GET['fid'])) {
                    // Если бан из форума, фиксируем ID поста
                    $fid = intval($_GET['fid']);
                    echo '&#160;' . _t('Violation') . ' <a href="' . $config['homeurl'] . '/forum/index.php?act=post&amp;id=' . $fid . '"></a><br />' .
                        '<input type="hidden" value="' . $fid . '" name="banref" />';
                }

                echo '&#160;<textarea rows="' . $userConfig->fieldHeight . '" name="reason"></textarea>' .
                    '</p><p><input type="submit" value="' . _t('Apply Ban') . '" name="submit" />' .
                    '</p></div></form>';
            }
            echo '<div class="phdr"><a href="?user=' . $user['id'] . '">' . _t('Profile') . '</a></div>';
        }
        break;

    case 'cancel':
        // Разбаниваем пользователя (с сохранением истории)
        if (!$ban || $user['id'] == $systemUser->id || $systemUser->rights < 7) {
            echo $tools->displayError(_t('Wrong data'));
        } else {
            $req = $db->query("SELECT * FROM `cms_ban_users` WHERE `id` = '$ban' AND `user_id` = " . $user['id']);

            if ($req->rowCount()) {
                $res = $req->fetch();
                $error = false;

                if ($res['ban_time'] < time()) {
                    $error = _t('Ban not active');
                }

                if (!$error) {
                    echo '<div class="phdr"><b>' . _t('Ban termination') . '</b></div>';
                    echo '<div class="gmenu"><p>' . $tools->displayUser($user) . '</p></div>';

                    if (isset($_POST['submit'])) {
                        $db->exec("UPDATE `cms_ban_users` SET `ban_time` = '" . time() . "' WHERE `id` = '$ban'");
                        echo '<div class="gmenu"><p><h3>' . _t('Ban terminated') . '</h3></p></div>';
                    } else {
                        echo '<form action="?act=ban&amp;mod=cancel&amp;user=' . $user['id'] . '&amp;ban=' . $ban . '" method="POST">' .
                            '<div class="menu"><p>' . _t('Ban time is going to the end. Infrigement will be saved in the bans history') . '</p>' .
                            '<p><input type="submit" name="submit" value="' . _t('Terminate Ban') . '" /></p>' .
                            '</div></form>' .
                            '<div class="phdr"><a href="?act=ban&amp;user=' . $user['id'] . '">' . _t('Back') . '</a></div>';
                    }
                } else {
                    echo $tools->displayError($error);
                }
            } else {
                echo $tools->displayError(_t('Wrong data'));
            }
        }
        break;

    case 'delete':
        // Удаляем бан (с удалением записи из истории)
        if (!$ban || $systemUser->rights < 9) {
            echo $tools->displayError(_t('Wrong data'));
        } else {
            $req = $db->query("SELECT * FROM `cms_ban_users` WHERE `id` = '$ban' AND `user_id` = " . $user['id']);

            if ($req->rowCount()) {
                $res = $req->fetch();
                echo '<div class="phdr"><b>' . _t('Delete Ban') . '</b></div>' .
                    '<div class="gmenu"><p>' . $tools->displayUser($user) . '</p></div>';

                if (isset($_POST['submit'])) {
                    $db->exec("DELETE FROM `cms_ban_users` WHERE `id` = '$ban'");
                    echo '<div class="gmenu"><p><h3>' . _t('Ban deleted') . '</h3><a href="?act=ban&amp;user=' . $user['id'] . '">' . _t('Continue') . '</a></p></div>';
                } else {
                    echo '<form action="?act=ban&amp;mod=delete&amp;user=' . $user['id'] . '&amp;ban=' . $ban . '" method="POST">' .
                        '<div class="menu"><p>' . _t('Removing ban along with a record in the bans history') . '</p>' .
                        '<p><input type="submit" name="submit" value="' . _t('Delete') . '" /></p>' .
                        '</div></form>' .
                        '<div class="phdr"><a href="?act=ban&amp;user=' . $user['id'] . '">' . _t('Back') . '</a></div>';
                }
            } else {
                echo $tools->displayError(_t('Wrong data'));
            }
        }
        break;

    case 'delhist':
        // Очищаем историю нарушений юзера
        if ($systemUser->rights == 9) {
            echo '<div class="phdr"><b>' . _t('Violations history') . '</b></div>' .
                '<div class="gmenu"><p>' . $tools->displayUser($user) . '</p></div>';

            if (isset($_POST['submit'])) {
                $db->exec("DELETE FROM `cms_ban_users` WHERE `user_id` = " . $user['id']);
                echo '<div class="gmenu"><h3>' . _t('Violations history cleared') . '</h3></div>';
            } else {
                echo '<form action="?act=ban&amp;mod=delhist&amp;user=' . $user['id'] . '" method="post">' .
                    '<div class="menu"><p>' . _t('Are you sure want to clean entire history of user violations?') . '</p>' .
                    '<p><input type="submit" value="' . _t('Clear') . '" name="submit" />' .
                    '</p></div></form>';
            }

            $total = $db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();
            echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>' .
                '<p>' . ($total
                    ? '<a href="?act=ban&amp;user=' . $user['id'] . '">' . _t('Violations history') . '</a><br />'
                    : '') .
                '<a href="../admin/?act=ban_panel">' . _t('Ban Panel') . '</a></p>';
        } else {
            echo $tools->displayError(_t('Violations history can be cleared by Supervisor only'));
        }
        break;

    default:
        // История нарушений
        echo '<div class="phdr"><a href="?user=' . $user['id'] . '"><b>' . _t('Profile') . '</b></a> | ' . _t('Violations History') . '</div>';
        // Меню
        $menu = [];

        if ($systemUser->rights >= 6) {
            $menu[] = '<a href="../admin/index.php?act=ban_panel">' . _t('Ban Panel') . '</a>';
        }

        if ($systemUser->rights == 9) {
            $menu[] = '<a href="?act=ban&amp;mod=delhist&amp;user=' . $user['id'] . '">' . _t('Clear history') . '</a>';
        }

        if (!empty($menu)) {
            echo '<div class="topmenu">' . implode(' | ', $menu) . '</div>';
        }

        if ($user['id'] != $systemUser->id) {
            echo '<div class="user"><p>' . $tools->displayUser($user) . '</p></div>';
        } else {
            echo '<div class="list2"><p>' . _t('My Violations') . '</p></div>';
        }

        $total = $db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();

        if ($total) {
            $req = $db->query("SELECT * FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "' ORDER BY `ban_time` DESC" . $tools->getPgStart(true));
            $i = 0;

            $types = [
                1  => _t('Full block'),
                2  => _t('Private messages'),
                10 => _t('Comments'),
                11 => _t('Forum'),
                13 => _t('Guestbook'),
                15 => _t('Library'),
            ];

            while ($res = $req->fetch()) {
                $remain = $res['ban_time'] - time();
                $period = $res['ban_time'] - $res['ban_while'];
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                echo $asset->img(($remain > 0 ? 'red' : 'green') . '.gif')->class('left') . '&#160;' .
                    '<b>' . $types[$res['ban_type']] . '</b>' .
                    ' <span class="gray">(' . date("d.m.Y / H:i", $res['ban_while']) . ')</span>' .
                    '<br />' . $tools->checkout($res['ban_reason']) .
                    '<div class="sub">';

                if ($systemUser->rights > 0) {
                    echo '<span class="gray">' . _t('Who applied the Ban?') . ':</span> ' . $res['ban_who'] . '<br />';
                }

                echo '<span class="gray">' . _t('Time') . ':</span> '
                    . ($period < 86400000 ? $tools->timecount($period) : _t('Till cancel'));

                if ($remain > 0) {
                    echo '<br /><span class="gray">' . _t('Remains') . ':</span> ' . $tools->timecount($remain);
                }

                // Меню отдельного бана
                $menu = [];

                if ($systemUser->rights >= 7 && $remain > 0) {
                    $menu[] = '<a href="?act=ban&amp;mod=cancel&amp;user=' . $user['id'] . '&amp;ban=' . $res['id'] . '">' . _t('Cancel Ban') . '</a>';
                }

                if ($systemUser->rights == 9) {
                    $menu[] = '<a href="?act=ban&amp;mod=delete&amp;user=' . $user['id'] . '&amp;ban=' . $res['id'] . '">' . _t('Delete Ban') . '</a>';
                }

                if (!empty($menu)) {
                    echo '<div>' . implode(' | ', $menu) . '</div>';
                }

                echo '</div></div>';
                ++$i;
            }
        } else {
            echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
        }

        echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

        if ($total > $userConfig->kmess) {
            echo '<p>' . $tools->displayPagination('?act=ban&amp;user=' . $user['id'] . '&amp;', $total) . '</p>' .
                '<p><form action="?act=ban&amp;user=' . $user['id'] . '" method="post">' .
                '<input type="text" name="page" size="2"/>' .
                '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
        }
}
