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
 * @var int                           $id
 * @var array                         $set_forum
 *
 * @var Mobicms\Asset\Manager         $asset
 * @var PDO                           $db
 * @var Mobicms\Api\UserInterface     $systemUser
 * @var Mobicms\Checkpoint\UserConfig $userConfig
 * @var Mobicms\Api\ToolsInterface    $tools
 */

ob_start();
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = $tools->getPgStart();

if (!$id) {
    exit(_t('Wrong data'));
}

// Запрос сообщения
$res = $db->query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
WHERE `forum`.`type` = 'm' AND `forum`.`id` = '$id'" . ($systemUser->rights >= 7 ? "" : " AND `forum`.`close` != '1'") . " LIMIT 1")->fetch();

// Запрос темы
$them = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `id` = '" . $res['refid'] . "'")->fetch();
echo '<div class="phdr"><b>' . _t('Topic') . ':</b> ' . $them['text'] . '</div><div class="menu">';

// Данные пользователя
echo '<table cellpadding="0" cellspacing="0"><tr><td>';

if (file_exists(UPLOAD_PATH . 'users/avatar/' . $res['user_id'] . '.png')) {
    echo '<img src="../uploads/users/avatar/' . $res['user_id'] . '.png" width="32" height="32" alt="' . $res['from'] . '" />&#160;';
} else {
    echo $asset->img('empty.png')->alt($res['from']) . '&#160;';
}

echo '</td><td>';

if ($res['sex']) {
    echo $asset->img(($res['sex'] == 'm' ? 'm' : 'w') . ($res['datereg'] > time() - 86400 ? '_new' : '') . '.png')->class('icon-inline');
} else {
    echo $asset->img('del.png')->class('icon');
}

// Ник юзера и ссылка на его анкету
if ($systemUser->isValid() && $systemUser->id != $res['user_id']) {
    echo '<a href="../profile/?user=' . $res['user_id'] . '"><b>' . $res['from'] . '</b></a> ';
} else {
    echo '<b>' . $res['from'] . '</b> ';
}

// Метка должности
$user_rights = [
    3 => '(FMod)',
    6 => '(Smd)',
    7 => '(Adm)',
    9 => '(SV!)',
];
echo $user_rights[$res['rights']] ?? null;

// Метка Онлайн / Офлайн
echo(time() > $res['lastdate'] + 300 ? '<span class="red"> [Off]</span> ' : '<span class="green"> [ON]</span> ');
echo '<a href="index.php?act=post&amp;id=' . $res['id'] . '" title="Link to post">[#]</a>';

// Ссылки на ответ и цитирование
if ($systemUser->isValid() && $systemUser->id != $res['user_id']) {
    echo '&#160;<a href="index.php?act=say&amp;id=' . $res['id'] . '&amp;start=' . $start . '">' . _t('[r]') . '</a>&#160;' .
        '<a href="index.php?act=say&amp;id=' . $res['id'] . '&amp;start=' . $start . '&amp;cyt">' . _t('[q]') . '</a> ';
}

// Время поста
echo ' <span class="gray">(' . $tools->displayDate($res['time']) . ')</span><br />';

// Статус юзера
if (!empty($res['status'])) {
    echo '<div class="status">' . $asset->img('label.png')->class('icon-inline') . $res['status'] . '</div>';
}

echo '</td></tr></table>';

// Вывод текста поста
$text = $tools->checkout($res['text'], 1, 1);
$text = $tools->smilies($text, ($res['rights'] >= 1) ? 1 : 0);
echo $text . '';

// Если есть прикрепленный файл, выводим его описание
$q = $db->prepare('SELECT * FROM `cms_forum_files` WHERE `post`=?');
$q->execute([$res['id']]);
$freq = $q->fetchAll();
if (count($freq)) {
    echo '<div class="post-files">';
    $pic_ext = [
        'gif',
        'jpg',
        'jpeg',
        'png',
    ];
    foreach ($freq as $fres) {
        $fls = round(@filesize(UPLOAD_PATH . 'forum/attach/' . $fres['filename']) / 1024, 2);
        echo '<div class="gray" style="font-size: x-small;background-color: rgba(128, 128, 128, 0.1);padding: 2px 4px;float: left;margin: 4px 4px 0 0;">' . _t('Attachment') . ':';
        // Предпросмотр изображений
        $att_ext = strtolower(pathinfo(UPLOAD_PATH . 'forum/attach/' . $fres['filename'], PATHINFO_EXTENSION));

        if (in_array($att_ext, $pic_ext)) {
            echo '<div><a href="index.php?act=file&amp;id=' . $fres['id'] . '">';
            echo '<img src="../assets/modules/forum/thumbinal.php?file=' . (urlencode($fres['filename'])) . '" alt="' . _t('Click to view image') . '" /></a></div>';
        } else {
            echo '<br /><a href="index.php?act=file&amp;id=' . $fres['id'] . '">' . $fres['filename'] . '</a>';
        }

        echo ' (' . $fls . ' кб.)<br>';
        echo _t('Downloads') . ': ' . $fres['dlcount'] . '</div>';
        $file_id = $fres['id'];
    }
    echo '<div style="clear: both;"></div></div>';
}

echo '</div>';

// Вычисляем, на какой странице сообщение?
$page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '$id'")->fetchColumn() / $userConfig->kmess);
echo '<div class="phdr"><a href="index.php?id=' . $res['refid'] . '&amp;page=' . $page . '">' . _t('Back to topic') . '</a></div>';
echo '<p><a href="index.php">' . _t('Forum') . '</a></p>';
