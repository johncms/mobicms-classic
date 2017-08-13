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

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    $vote_count = abs(intval($_POST['count_vote'] ?? 2));

    $topic = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `id`='$id' AND `edit` != '1'")->fetchColumn();
    $topic_vote = $db->query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id'")->fetchColumn();
    ob_start();

    if ($topic_vote != 0 || $topic == 0) {
        exit(_t('Wrong data'));
    }

    if (isset($_POST['submit'])) {
        $vote_name = mb_substr(trim($_POST['name_vote']), 0, 50);

        if (!empty($vote_name) && !empty($_POST[0]) && !empty($_POST[1]) && !empty($_POST['count_vote'])) {
            $db->exec("INSERT INTO `cms_forum_vote` SET
                `name`=" . $db->quote($vote_name) . ",
                `time`='" . time() . "',
                `type` = '1',
                `topic`='$id'
            ");
            $db->exec("UPDATE `forum` SET  `realid` = '1'  WHERE `id` = '$id'");

            if ($vote_count > 20) {
                $vote_count = 20;
            } else {
                if ($vote_count < 2) {
                    $vote_count = 2;
                }
            }

            for ($vote = 0; $vote < $vote_count; $vote++) {
                $text = mb_substr(trim($_POST[$vote]), 0, 30);

                if (empty($text)) {
                    continue;
                }

                $db->exec("INSERT INTO `cms_forum_vote` SET
                    `name`=" . $db->quote($text) . ",
                    `type` = '2',
                    `topic`='$id'
                ");
            }
            echo _t('Poll added') . '<br /><a href="?id=' . $id . '">' . _t('Continue') . '</a>';
        } else {
            echo _t('The required fields are not filled') . '<br /><a href="?act=addvote&amp;id=' . $id . '">' . _t('Repeat') . '</a>';
        }
    } else {
        echo '<form action="index.php?act=addvote&amp;id=' . $id . '" method="post">' .
            '<br />' . _t('Poll (max. 150)') . ':<br>' .
            '<input type="text" size="20" maxlength="150" name="name_vote" value="' . htmlentities(($_POST['name_vote'] ?? ''), ENT_QUOTES, 'UTF-8') . '"/><br>';

        if (isset($_POST['plus'])) {
            ++$vote_count;
        } elseif (isset($_POST['minus'])) {
            --$vote_count;
        }

        if ($vote_count < 2) {
            $vote_count = 2;
        } elseif ($vote_count > 20) {
            $vote_count = 20;
        }

        for ($vote = 0; $vote < $vote_count; $vote++) {
            echo _t('Answer') . ' ' . ($vote + 1) . '(max. 50): <br><input type="text" name="' . $vote . '" value="' . htmlentities(($_POST[$vote] ?? ''), ENT_QUOTES, 'UTF-8') . '"/><br>';
        }

        echo '<input type="hidden" name="count_vote" value="' . abs(intval($vote_count)) . '"/>';
        echo ($vote_count < 20) ? '<br><input type="submit" name="plus" value="' . _t('Add Answer') . '"/>' : '';
        echo $vote_count > 2 ? '<input type="submit" name="minus" value="' . _t('Delete last') . '"/><br>' : '<br>';
        echo '<p><input type="submit" name="submit" value="' . _t('Save') . '"/></p></form>';
        echo '<a href="index.php?id=' . $id . '">' . _t('Back') . '</a>';
    }
}
