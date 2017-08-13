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

/**
 * @var int                                     $id
 * @var array                                   $queryParams
 * @var array                                   $set_forum
 *
 * @var Psr\Container\ContainerInterface        $container
 * @var PDO                                     $db
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var Mobicms\Api\UserInterface               $systemUser
 * @var Mobicms\Checkpoint\UserConfig           $userConfig
 * @var Mobicms\Api\ToolsInterface              $tools
 * @var Mobicms\Api\ConfigInterface             $config
 * @var League\Plates\Engine                    $view
 */

$postParams = $request->getParsedBody();

$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = $tools->getPgStart();

// Закрываем доступ для определенных ситуаций
if (!$id
    || !$systemUser->isValid()
    || isset($systemUser->ban[1])
    || isset($systemUser->ban[11])
    || (!$systemUser->rights && $config['mod_forum'] == 3)
) {
    echo $view->render('system::app/legacy', [
        'title'   => _t('Forum'),
        'content' => $tools->displayError(_t('Access denied')),
    ]);
    exit();
}

// Вспомогательная Функция обработки ссылок форума
function forum_link($m)
{
    global $db, $config;

    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);

        if ('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == $config->homeurl . '/forum/index.php?id=') {
            $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
            $req = $db->query("SELECT `text` FROM `forum` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");

            if ($req->rowCount()) {
                $res = $req->fetch();
                $name = strtr($res['text'], [
                    '&quot;' => '',
                    '&amp;'  => '',
                    '&lt;'   => '',
                    '&gt;'   => '',
                    '&#039;' => '',
                    '['      => '',
                    ']'      => '',
                ]);

                if (mb_strlen($name) > 40) {
                    $name = mb_substr($name, 0, 40) . '...';
                }

                return '[url=' . $m[3] . ']' . $name . '[/url]';
            } else {
                return $m[3];
            }
        } else {
            return $m[3];
        }
    }
}

// Проверка на флуд
$flood = $tools->antiflood();

$type1 = $db->query("SELECT * FROM `forum` WHERE `id` = '$id'")->fetch();

if ($flood) {
    echo $view->render('system::app/legacy', [
        'title'   => _t('Forum'),
        'content' => $tools->displayError(sprintf(_t('You cannot add the message so often<br>Please, wait %d sec.'), $flood),
            '<a href="index.php?id=' . ($type1['type'] == 'm' ? $type1['refid'] : $id) . '&amp;start=' . $start . '">' . _t('Back') . '</a>'),
    ]);
    exit;
}

switch ($type1['type']) {
    case 't':
        // Добавление простого сообщения
        if (($type1['edit'] == 1 || $type1['close'] == 1) && $systemUser->rights < 6) {
            // Проверка, закрыта ли тема
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('You cannot write in a closed topic'), '<a href="index.php?id=' . $id . '">' . _t('Back') . '</a>'),
            ]);
            exit;
        }

        $msg = isset($postParams['msg']) ? trim($postParams['msg']) : '';
        //Обрабатываем ссылки
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

        if (isset($postParams['submit'])
            && !empty($postParams['msg'])
            && isset($postParams['token'])
            && isset($_SESSION['token'])
            && $postParams['token'] == $_SESSION['token']
        ) {
            // Проверяем на минимальную длину
            if (mb_strlen($msg) < 4) {
                echo $view->render('system::app/legacy', [
                    'title'   => _t('Forum'),
                    'content' => $tools->displayError(_t('Text is too short'), '<a href="index.php?id=' . $id . '">' . _t('Back') . '</a>'),
                ]);
                exit;
            }

            // Проверяем, не повторяется ли сообщение?
            $req = $db->query("SELECT * FROM `forum` WHERE `user_id` = '" . $systemUser->id . "' AND `type` = 'm' ORDER BY `time` DESC");

            if ($req->rowCount()) {
                $res = $req->fetch();
                if ($msg == $res['text']) {
                    echo $view->render('system::app/legacy', [
                        'title'   => _t('Forum'),
                        'content' => $tools->displayError(_t('Message already exists'), '<a href="index.php?id=' . $id . '&amp;start=' . $start . '">' . _t('Back') . '</a>'),
                    ]);
                    exit;
                }
            }

            // Удаляем фильтр, если он был
            if (isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id) {
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
            }

            unset($_SESSION['token']);

            // Проверяем, было ли последнее сообщение от того же автора?
            $req = $db->query("SELECT *, CHAR_LENGTH(`text`) AS `strlen` FROM `forum` WHERE `type` = 'm' AND `refid` = " . $id . " AND `close` != 1 ORDER BY `time` DESC LIMIT 1");

            $update = false;

            if ($req->rowCount()) {
                $update = true;
                $res = $req->fetch();

                if (isset($postParams['merge'])
                    && !isset($postParams['addfiles'])
                    && $res['time'] + 3600 < strtotime('+ 1 hour')
                    && $res['strlen'] + strlen($msg) < 65536
                    && $res['user_id'] == $systemUser->id
                ) {
                    $newpost = $res['text'];

                    if (strpos($newpost, '[timestamp]') === false) {
                        $newpost = '[timestamp]' . date("d.m.Y H:i", $res['time']) . '[/timestamp]' . PHP_EOL . $newpost;
                    }

                    $newpost .= PHP_EOL . PHP_EOL . '[timestamp]' . date("d.m.Y H:i", time()) . '[/timestamp]' . PHP_EOL . $msg;

                    // Обновляем пост
                    $db->prepare('UPDATE `forum` SET
                      `text` = ?,
                      `time` = ?
                      WHERE `id` = ' . $res['id']
                    )->execute([$newpost, time()]);
                } else {
                    $update = false;

                    // Добавляем сообщение в базу
                    $db->prepare('
                      INSERT INTO `forum` SET
                      `refid` = ?,
                      `type` = \'m\',
                      `time` = ?,
                      `user_id` = ?,
                      `from` = ?,
                      `ip` = ?,
                      `ip_via_proxy` = ?,
                      `soft` = ?,
                      `text` = ?,
                      `edit` = \'\',
                      `curators` = \'\'
                    ')->execute([
                        $id,
                        time(),
                        $systemUser->id,
                        $systemUser->name,
                        $request->getAttribute('ip'),
                        $request->getAttribute('ip_via_proxy'),
                        $request->getAttribute('user_agent'),
                        $msg,
                    ]);

                    $fadd = $db->lastInsertId();
                }
            }

            // Обновляем время топика
            $db->exec("UPDATE `forum` SET
                `time` = '" . time() . "'
                WHERE `id` = '$id'
            ");

            // Обновляем статистику юзера
            $db->exec("UPDATE `users` SET
                `postforum`='" . ($systemUser->postforum + 1) . "',
                `lastpost` = '" . time() . "'
                WHERE `id` = '" . $systemUser->id . "'
            ");

            // Вычисляем, на какую страницу попадает добавляемый пост
            $page = $set_forum['upfp'] ? 1 : ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '$id'" . ($systemUser->rights >= 6 ? '' : " AND `close` != '1'"))->fetchColumn() / $userConfig->kmess);

            if (isset($postParams['addfiles'])) {
                if ($update) {
                    header('Location: ?id=' . $res['id'] . '&act=addfile');
                } else {
                    header('Location: ?id=' . $fadd . '&act=addfile');
                }
            } else {
                header('Location: ?id=' . $id . '&page=' . $page);
            }
            exit;
        } else {
            ob_start();
            $msg_pre = $tools->checkout($msg, 1, 1);
            $msg_pre = $tools->smilies($msg_pre, $systemUser->rights ? 1 : 0);
            $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
            echo '<div class="phdr"><b>' . _t('Topic') . ':</b> ' . $type1['text'] . '</div>';

            if ($msg && !isset($postParams['submit'])) {
                echo '<div class="list1">' . $tools->displayUser($systemUser, ['iphide' => 1, 'header' => '<span class="gray">(' . $tools->displayDate(time()) . ')</span>', 'body' => $msg_pre]) . '</div>';
            }

            echo '<form name="form" action="index.php?act=say&amp;id=' . $id . '&amp;start=' . $start . '" method="post"><div class="gmenu">' .
                '<p><h3>' . _t('Message') . '</h3>';
            echo '</p><p>' . $container->get(Mobicms\Api\BbcodeInterface::class)->buttons('form', 'msg');
            echo '<textarea rows="' . $userConfig->fieldHeight . '" name="msg">' . (empty($postParams['msg']) ? '' : $tools->checkout($msg)) . '</textarea></p><p>';

            // Проверяем, было ли последнее сообщение от того же автора?
            $req = $db->query("SELECT *, CHAR_LENGTH(`text`) AS `strlen` FROM `forum` WHERE `type` = 'm' AND `refid` = " . $id . " AND `close` != 1 ORDER BY `time` DESC LIMIT 1");

            if ($req->rowCount()) {
                $res = $req->fetch();

                // Показываем чекбокс объединения постов
                if ($res['strlen'] + strlen($msg) < 65536 && $res['user_id'] == $systemUser->id) {
                    echo '<input type="checkbox" name="merge" value="1" checked="checked"/> ' . _t('Merge with previous message') . '<br>';
                }
            }

            echo '<input type="checkbox" name="addfiles" value="1" ' . (isset($postParams['addfiles']) ? 'checked="checked" ' : '') . '/> ' . _t('Add File');

            $token = mt_rand(1000, 100000);
            $_SESSION['token'] = $token;
            echo '</p><p>' .
                '<input type="submit" name="submit" value="' . _t('Send') . '" style="width: 107px; cursor: pointer"/> ' .
                ($set_forum['preview'] ? '<input type="submit" value="' . _t('Preview') . '" style="width: 107px; cursor: pointer"/>' : '') .
                '<input type="hidden" name="token" value="' . $token . '"/>' .
                '</p></div></form>';
        }

        echo '<div class="phdr"><a href="../help/?act=smileys">' . _t('Smilies') . '</a></div>' .
            '<p><a href="index.php?id=' . $id . '&amp;start=' . $start . '">' . _t('Back') . '</a></p>';
        break;

    case 'm':
        // Добавление сообщения с цитированием поста
        $th = $type1['refid'];
        $th1 = $db->query("SELECT * FROM `forum` WHERE `id` = '$th'")->fetch();

        if (($th1['edit'] == 1 || $th1['close'] == 1) && $systemUser->rights < 6) {
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('You cannot write in a closed topic'), '<a href="index.php?id=' . $th1['id'] . '">' . _t('Back') . '</a>'),
            ]);
            exit;
        }

        if ($type1['user_id'] == $systemUser->id) {
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('You can not reply to your own message'), '<a href="index.php?id=' . $th1['id'] . '">' . _t('Back') . '</a>'),
            ]);
            exit;
        }

        $shift = ($config['timeshift'] + $userConfig->timeshift) * 3600;
        $vr = date("d.m.Y / H:i", $type1['time'] + $shift);
        $msg = isset($postParams['msg']) ? trim($postParams['msg']) : '';
        $txt = isset($postParams['txt']) ? intval($postParams['txt']) : false;

        if (!empty($postParams['citata'])) {
            // Если была цитата, форматируем ее и обрабатываем
            $citata = isset($postParams['citata']) ? trim($postParams['citata']) : '';
            $citata = $container->get(Mobicms\Api\BbcodeInterface::class)->notags($citata);
            $citata = preg_replace('#\[c\](.*?)\[/c\]#si', '', $citata);
            $citata = mb_substr($citata, 0, 200);
            $tp = date("d.m.Y H:i", $type1['time']);
            $msg = '[c][url=' . $config['homeurl'] . '/forum/index.php?act=post&id=' . $type1['id'] . ']#[/url] ' . $type1['from'] . ' ([time]' . $tp . "[/time])\n" . $citata . '[/c]' . $msg;
        } elseif (isset($postParams['txt'])) {
            // Если был ответ, обрабатываем реплику
            switch ($txt) {
                case 2:
                    $repl = $type1['from'] . ', ' . _t('I am glad to answer you') . ', ';
                    break;

                case 3:
                    $repl = $type1['from'] . ', ' . _t('respond to Your message') . ' ([url=' . $config['homeurl'] . '/forum/index.php?act=post&id=' . $type1['id'] . ']' . $vr . '[/url]): ';
                    break;

                default :
                    $repl = $type1['from'] . ', ';
            }
            $msg = $repl . ' ' . $msg;
        }

        //Обрабатываем ссылки
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

        if (isset($postParams['submit'])
            && isset($postParams['token'])
            && isset($_SESSION['token'])
            && $postParams['token'] == $_SESSION['token']
        ) {
            if (empty($postParams['msg'])) {
                echo $view->render('system::app/legacy', [
                    'title'   => _t('Forum'),
                    'content' => $tools->displayError(_t('You have not entered the message'), '<a href="index.php?act=say&amp;id=' . $th . (isset($queryParams['cyt']) ? '&amp;cyt' : '') . '">' . _t('Repeat') . '</a>'),
                ]);
                exit;
            }

            // Проверяем на минимальную длину
            if (mb_strlen($msg) < 4) {
                echo $view->render('system::app/legacy', [
                    'title'   => _t('Forum'),
                    'content' => $tools->displayError(_t('Text is too short'), '<a href="index.php?id=' . $id . '">' . _t('Back') . '</a>'),
                ]);
                exit;
            }

            // Проверяем, не повторяется ли сообщение?
            $req = $db->query("SELECT * FROM `forum` WHERE `user_id` = '" . $systemUser->id . "' AND `type` = 'm' ORDER BY `time` DESC LIMIT 1");

            if ($req->rowCount()) {
                $res = $req->fetch();

                if ($msg == $res['text']) {
                    echo $view->render('system::app/legacy', [
                        'title'   => _t('Forum'),
                        'content' => $tools->displayError(_t('Message already exists'), '<a href="index.php?id=' . $th . '&amp;start=' . $start . '">' . _t('Back') . '</a>'),
                    ]);
                    exit;
                }
            }

            // Удаляем фильтр, если он был
            if (isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $th) {
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
            }

            unset($_SESSION['token']);

            // Добавляем сообщение в базу
            $db->prepare('
              INSERT INTO `forum` SET
              `refid` = ?,
              `type` = \'m\',
              `time` = ?,
              `user_id` = ?,
              `from` = ?,
              `ip` = ?,
              `ip_via_proxy` = ?,
              `soft` = ?,
              `text` = ?,
              `edit` = \'\',
              `curators` = \'\'
            ')->execute([
                $th,
                time(),
                $systemUser->id,
                $systemUser->name,
                $request->getAttribute('ip'),
                $request->getAttribute('ip_via_proxy'),
                $request->getAttribute('user_agent'),
                $msg,
            ]);

            $fadd = $db->lastInsertId();

            // Обновляем время топика
            $db->exec("UPDATE `forum`
                SET `time` = '" . time() . "'
                WHERE `id` = '$th'
            ");

            // Обновляем статистику юзера
            $db->exec("UPDATE `users` SET
                `postforum`='" . ($systemUser->postforum + 1) . "',
                `lastpost` = '" . time() . "'
                WHERE `id` = '" . $systemUser->id . "'
            ");

            // Вычисляем, на какую страницу попадает добавляемый пост
            $page = $set_forum['upfp'] ? 1 : ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '$th'" . ($systemUser->rights >= 6 ? '' : " AND `close` != '1'"))->fetchColumn() / $userConfig->kmess);

            if (isset($postParams['addfiles'])) {
                header("Location: ?id=$fadd&act=addfile");
            } else {
                header("Location: ?id=$th&page=$page");
            }
            exit;
        } else {
            $pageTitle = _t('Forum');
            ob_start();
            $qt = " $type1[text]";
            $msg_pre = $tools->checkout($msg, 1, 1);
            $msg_pre = $tools->smilies($msg_pre, $systemUser->rights ? 1 : 0);
            $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
            echo '<div class="phdr"><b>' . _t('Topic') . ':</b> ' . $th1['text'] . '</div>';
            $qt = str_replace("<br>", "\r\n", $qt);
            $qt = trim(preg_replace('#\[c\](.*?)\[/c\]#si', '', $qt));
            $qt = $tools->checkout($qt, 0, 2);

            if (!empty($postParams['msg']) && !isset($postParams['submit'])) {
                echo '<div class="list1">' . $tools->displayUser($systemUser, ['iphide' => 1, 'header' => '<span class="gray">(' . $tools->displayDate(time()) . ')</span>', 'body' => $msg_pre]) . '</div>';
            }

            echo '<form name="form" action="index.php?act=say&amp;id=' . $id . '&amp;start=' . $start . (isset($queryParams['cyt']) ? '&amp;cyt' : '') . '" method="post"><div class="gmenu">';

            if (isset($queryParams['cyt'])) {
                // Форма с цитатой
                echo '<p><b>' . $type1['from'] . '</b> <span class="gray">(' . $vr . ')</span></p>' .
                    '<p><h3>' . _t('Quote') . '</h3>' .
                    '<textarea rows="' . $userConfig->fieldHeight . '" name="citata">' . (empty($postParams['citata']) ? $qt : $tools->checkout($postParams['citata'])) . '</textarea>' .
                    '<br /><small>' . _t('Only allowed 200 characters, other text will be cropped.') . '</small></p>';
            } else {
                // Форма с репликой
                echo '<p><h3>' . _t('Appeal') . '</h3>' .
                    '<input type="radio" value="0" ' . (!$txt ? 'checked="checked"' : '') . ' name="txt" />&#160;<b>' . $type1['from'] . '</b>,<br />' .
                    '<input type="radio" value="2" ' . ($txt == 2 ? 'checked="checked"' : '') . ' name="txt" />&#160;<b>' . $type1['from'] . '</b>, ' . _t('I am glad to answer you') . ',<br />' .
                    '<input type="radio" value="3" ' . ($txt == 3 ? 'checked="checked"' : '') . ' name="txt" />&#160;<b>' . $type1['from'] . '</b>, ' . _t('respond to Your message') . ' (<a href="index.php?act=post&amp;id=' . $type1['id'] . '">' . $vr . '</a>):</p>';
            }

            echo '<p><h3>' . _t('Message') . '</h3>';
            echo '</p><p>' . $container->get(Mobicms\Api\BbcodeInterface::class)->buttons('form', 'msg');
            echo '<textarea rows="' . $userConfig->fieldHeight . '" name="msg">' . (empty($postParams['msg']) ? '' : $tools->checkout($postParams['msg'])) . '</textarea></p>' .
                '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($postParams['addfiles']) ? 'checked="checked" ' : '') . '/> ' . _t('Add File');

            $token = mt_rand(1000, 100000);
            $_SESSION['token'] = $token;
            echo '</p><p><input type="submit" name="submit" value="' . _t('Send') . '" style="width: 107px; cursor: pointer;"/> ' .
                ($set_forum['preview'] ? '<input type="submit" value="' . _t('Preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
                '<input type="hidden" name="token" value="' . $token . '"/>' .
                '</p></div></form>';
        }

        echo '<div class="phdr"><a href="../help/?act=smileys">' . _t('Smilies') . '</a></div>' .
            '<p><a href="index.php?id=' . $type1['refid'] . '&amp;start=' . $start . '">' . _t('Back') . '</a></p>';
        break;

    default:
        echo $view->render('system::app/legacy', [
            'title'   => _t('Forum'),
            'content' => $tools->displayError(_t('Topic has been deleted or does not exists'), '<a href="index.php">' . _t('Forum') . '</a>'),
        ]);
        exit;
}
