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
 * @var int                       $id
 *
 * @var PDO                       $db
 * @var Mobicms\Api\UserInterface $systemUser
 */

$vote = filter_input(INPUT_POST, 'vote', FILTER_VALIDATE_INT);

if ($vote && $id && $systemUser->isValid()) {
    $smtp = $db->prepare('SELECT * FROM (
                          SELECT COUNT(*) as `topic` FROM `forum` WHERE `type`=? AND `id`=? AND `edit`<>?) q1, (
                          SELECT COUNT(*) as `topic_vote` FROM `cms_forum_vote` WHERE `type`=? AND `id`=? AND `topic`=?) q2, (
                          SELECT COUNT(*) as `vote_user` FROM `cms_forum_vote_users` WHERE `user`=? AND `topic`=?) q3
                          ');
    $smtp->execute(['t', $id, 1, 2, $vote, $id, $systemUser->id, $id]);
    $cnt = $smtp->fetch();

    ob_start();

    if ($cnt['topic_vote'] == 0 || $cnt['vote_user'] > 0 || $cnt['topic'] == 0) {
        echo $view->render(
                           'system::app/legacy', [
                                                  'title'   => _t('Forum'),
                                                  'content' => $tools->displayError(_t('Wrong data'), '<a href="' . htmlspecialchars(getenv('HTTP_REFERER')) . '">' . _t('Back') . '</a>'),
                                                  ]);
        exit;
    }

    $db->exec("INSERT INTO `cms_forum_vote_users` SET `topic` = '$id', `user` = '" . $systemUser->id . "', `vote` = '$vote'");
    $db->exec("UPDATE `cms_forum_vote` SET `count` = count + 1 WHERE id = '$vote'");
    $db->exec("UPDATE `cms_forum_vote` SET `count` = count + 1 WHERE topic = '$id' AND `type` = '1'");
    echo _t('Vote accepted') . '<br /><a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Back') . '</a>';
} else {
    echo $view->render(
                       'system::app/legacy', [
                                              'title'   => _t('Forum'),
                                              'content' => $tools->displayError(_t('Wrong data'), '<a href="' . htmlspecialchars(getenv('HTTP_REFERER')) . '">' . _t('Back') . '</a>'),
                                              ]);
    exit;
}
