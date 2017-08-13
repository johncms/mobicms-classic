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
 *
 * @var PDO                           $db
 * @var Mobicms\Api\UserInterface     $systemUser
 * @var Mobicms\Checkpoint\UserConfig $userConfig
 * @var Mobicms\Api\ToolsInterface    $tools
 */

ob_start();
$topic_vote = $db->query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id'")->fetchColumn();

if ($topic_vote == 0 || $systemUser->rights < 7) {
    exit(_t('Wrong data'));
} else {
    $topic_vote = $db->query("SELECT `name`, `time`, `count` FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id' LIMIT 1")->fetch();
    echo '<div  class="phdr">' . _t('Who voted in the poll') . ' &laquo;<b>' . htmlentities($topic_vote['name'], ENT_QUOTES, 'UTF-8') . '</b>&raquo;</div>';
    $total = $db->query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `topic`='$id'")->fetchColumn();
    $req = $db->query("SELECT `cms_forum_vote_users`.*, `users`.`rights`, `users`.`lastdate`, `users`.`name`, `users`.`sex`, `users`.`status`, `users`.`datereg`, `users`.`id`
    FROM `cms_forum_vote_users` LEFT JOIN `users` ON `cms_forum_vote_users`.`user` = `users`.`id`
    WHERE `cms_forum_vote_users`.`topic`='$id'" . $tools->getPgStart(true));
    $i = 0;

    while ($res = $req->fetch()) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        echo $tools->displayUser($res, ['iphide' => 1]);
        echo '</div>';
        ++$i;
    }

    if ($total == 0) {
        echo '<div class="menu">' . _t('No one has voted in this poll yet') . '</div>';
    }

    echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $userConfig->kmess) {
        echo '<p>' . $tools->displayPagination('index.php?act=users&amp;id=' . $id . '&amp;', $total) . '</p>' .
            '<p><form action="index.php?act=users&amp;id=' . $id . '" method="post">' .
            '<input type="text" name="page" size="2"/>' .
            '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
    }

    echo '<p><a href="index.php?id=' . $id . '">' . _t('Go to Topic') . '</a></p>';
}
