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
 *
 * @var Psr\Container\ContainerInterface        $container
 * @var Mobicms\Asset\Manager                   $asset
 * @var PDO                                     $db
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var Mobicms\Api\UserInterface               $systemUser
 * @var Mobicms\Api\ToolsInterface              $tools
 * @var Mobicms\Api\ConfigInterface             $config
 * @var League\Plates\Engine                    $view
 */

// Закрываем доступ для определенных ситуаций
if (!$id
    || !$systemUser->isValid()
    || isset($systemUser->ban['1'])
    || isset($systemUser->ban['11'])
    || (!$systemUser->rights && $config['mod_forum'] == 3)
) {
    exit(_t('Access denied'));
}

// Вспомогательная Функция обработки ссылок форума
function forum_link($m)
{
    global $config, $db;

    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);

        if ('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == $config['homeurl'] . '/forum/index.php?id=') {
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

if ($flood) {
    echo $view->render('system::app/legacy', [
        'title'   => _t('Forum'),
        'content' => $tools->displayError(sprintf(_t('You cannot add the message so often<br>Please, wait %d sec.'), $flood), '<a href="index.php?id=' . $id . '&amp;start=' . $tools->getPgStart() . '">' . _t('Back') . '</a>'),
    ]);
    exit;
}

$req_r = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 'r' LIMIT 1");

if (!$req_r->rowCount()) {
    exit(_t('Wrong data'));
}

$res_r = $req_r->fetch();

$th = filter_has_var(INPUT_POST, 'th')
    ? mb_substr(filter_var($_POST['th'], FILTER_SANITIZE_SPECIAL_CHARS, ['flag' => FILTER_FLAG_ENCODE_HIGH]), 0, 100)
    : '';

$msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';
$msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

if (isset($_POST['submit'])
    && isset($_POST['token'])
    && isset($_SESSION['token'])
    && $_POST['token'] == $_SESSION['token']
) {
    $error = [];

    if (empty($th)) {
        $error[] = _t('You have not entered topic name');
    }

    if (mb_strlen($th) < 2) {
        $error[] = _t('Topic name too short');
    }

    if (empty($msg)) {
        $error[] = _t('You have not entered the message');
    }

    if (mb_strlen($msg) < 4) {
        $error[] = _t('Text is too short');
    }

    if (!$error) {
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

        // Прверяем, есть ли уже такая тема в текущем разделе?
        if ($db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `refid` = '$id' AND `text` = '$th'")->fetchColumn() > 0) {
            $error[] = _t('Topic with same name already exists in this section');
        }

        // Проверяем, не повторяется ли сообщение?
        $req = $db->query("SELECT * FROM `forum` WHERE `user_id` = '" . $systemUser->id . "' AND `type` = 'm' ORDER BY `time` DESC");

        if ($req->rowCount()) {
            $res = $req->fetch();

            if ($msg == $res['text']) {
                $error[] = _t('Message already exists');
            }
        }
    }

    if (!$error) {
        unset($_SESSION['token']);

        // Если задано в настройках, то назначаем топикстартера куратором
        $curator = $res_r['edit'] == 1 ? serialize([$systemUser->id => $systemUser->name]) : '';

        // Добавляем тему
        $db->prepare('
          INSERT INTO `forum` SET
          `refid` = ?,
          `type` = \'t\',
           `time` = ?,
           `user_id` = ?,
           `from` = ?,
           `text` = ?,
           `soft` = \'\',
           `edit` = \'\',
           `curators` = ?
        ')->execute([
            $id,
            time(),
            $systemUser->id,
            $systemUser->name,
            $th,
            $curator,
        ]);

        $rid = $db->lastInsertId();

        // Добавляем текст поста
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
            $rid,
            time(),
            $systemUser->id,
            $systemUser->name,
            $request->getAttribute('ip'),
            $request->getAttribute('ip_via_proxy'),
            $request->getAttribute('user_agent'),
            $msg,
        ]);

        $postid = $db->lastInsertId();

        // Записываем счетчик постов юзера
        $fpst = $systemUser->postforum + 1;
        $db->exec("UPDATE `users` SET
            `postforum` = '$fpst',
            `lastpost` = '" . time() . "'
            WHERE `id` = '" . $systemUser->id . "'
        ");

        // Ставим метку о прочтении
        $db->exec("INSERT INTO `cms_forum_rdm` SET
            `topic_id`='$rid',
            `user_id`='" . $systemUser->id . "',
            `time`='" . time() . "'
        ");

        if (isset($_POST['addfiles'])) {
            header("Location: ?id=$postid&act=addfile");
        } else {
            header('Location: ?id=' . $rid);
        }
    } else {
        // Выводим сообщение об ошибке
        echo $view->render('system::app/legacy', [
            'title'   => _t('Forum'),
            'content' => $tools->displayError($error, '<a href="index.php?act=nt&amp;id=' . $id . '">' . _t('Repeat') . '</a>'),
        ]);
        exit;
    }
} else {
    $res_c = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $res_r['refid'] . "'")->fetch();
    ob_start();
    $msg_pre = $tools->checkout($msg, 1, 1);
    $msg_pre = $tools->smilies($msg_pre, $systemUser->rights ? 1 : 0);
    $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
    echo '<div class="phdr"><a href="index.php?id=' . $id . '"><b>' . _t('Forum') . '</b></a> | ' . _t('New Topic') . '</div>';

    if ($msg && $th && !isset($_POST['submit'])) {
        echo '<div class="list1">' . $asset->img('op.gif')->class('icon') . '<span style="font-weight: bold">' . $th . '</span></div>' .
            '<div class="list2">' . $tools->displayUser($systemUser, ['iphide' => 1, 'header' => '<span class="gray">(' . $tools->displayDate(time()) . ')</span>', 'body' => $msg_pre]) . '</div>';
    }

    echo '<form name="form" action="index.php?act=nt&amp;id=' . $id . '" method="post">' .
        '<div class="gmenu">' .
        '<p><h3>' . _t('Section') . '</h3>' .
        '<a href="index.php?id=' . $res_c['id'] . '">' . $res_c['text'] . '</a> | <a href="index.php?id=' . $res_r['id'] . '">' . $res_r['text'] . '</a></p>' .
        '<p><h3>' . _t('Title(max. 100)') . '</h3>' .
        '<input type="text" size="20" maxlength="100" name="th" value="' . $th . '"/></p>' .
        '<p><h3>' . _t('Message') . '</h3>';
    echo '</p><p>' . $container->get(Mobicms\Api\BbcodeInterface::class)->buttons('form', 'msg');
    echo '<textarea rows="' . $systemUser->getConfig()->fieldHeight . '" name="msg">' . (isset($_POST['msg']) ? $tools->checkout($_POST['msg']) : '') . '</textarea></p>' .
        '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($_POST['addfiles']) ? 'checked="checked" ' : '') . '/> ' . _t('Add File');

    $token = mt_rand(1000, 100000);
    $_SESSION['token'] = $token;
    echo '</p><p><input type="submit" name="submit" value="' . _t('Save') . '" style="width: 107px; cursor: pointer;"/> ' .
        ($set_forum['preview'] ? '<input type="submit" value="' . _t('Preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
        '<input type="hidden" name="token" value="' . $token . '"/>' .
        '</p></div></form>' .
        '<div class="phdr"><a href="../help/?act=smileys">' . _t('Smilies') . '</a></div>' .
        '<p><a href="index.php?id=' . $id . '">' . _t('Back') . '</a></p>';
}
