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
    if (!$id) {
        exit(_t('Wrong data'));
    }

    // Проверяем, существует ли тема
    $req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't'");

    if (!$req->rowCount()) {
        exit(_t('Topic has been deleted or does not exists'));
    }

    $res = $req->fetch();

    if (isset($_POST['submit'])) {
        $del = isset($_POST['del']) ? intval($_POST['del']) : null;

        if ($del == 2 && $systemUser->rights == 9) {
            // Удаляем топик
            $req1 = $db->query("SELECT * FROM `cms_forum_files` WHERE `topic` = '$id'");

            if ($req1->rowCount()) {
                while ($res1 = $req1->fetch()) {
                    unlink(UPLOAD_PATH . 'forum/attach/' . $res1['filename']);
                }

                $db->exec("DELETE FROM `cms_forum_files` WHERE `topic` = '$id'");
                $db->query("OPTIMIZE TABLE `cms_forum_files`");
            }

            $db->exec("DELETE FROM `forum` WHERE `refid` = '$id'");
            $db->exec("DELETE FROM `forum` WHERE `id`='$id'");
        } elseif ($del = 1) {
            // Скрываем топик
            $db->exec("UPDATE `forum` SET `close` = '1', `close_who` = '" . $systemUser->name . "' WHERE `id` = '$id'");
            $db->exec("UPDATE `cms_forum_files` SET `del` = '1' WHERE `topic` = '$id'");
        }

        header('Location: ?id=' . $res['refid']);
    } else {
        // Меню выбора режима удаления темы
        ob_start();
        echo '<div class="phdr"><a href="index.php?id=' . $id . '"><b>' . _t('Forum') . '</b></a> | ' . _t('Delete Topic') . '</div>' .
            '<div class="rmenu"><form method="post" action="index.php?act=deltema&amp;id=' . $id . '">' .
            '<p><h3>' . _t('Do you really want to delete?') . '</h3>' .
            '<input type="radio" value="1" name="del" checked="checked"/>&#160;' . _t('Hide') . '<br />' .
            ($systemUser->rights == 9 ? '<input type="radio" value="2" name="del" />&#160;' . _t('Delete') . '</p>' : '') .
            '<p><input type="submit" name="submit" value="' . _t('Perform') . '" /></p>' .
            '<p><a href="index.php?id=' . $id . '">' . _t('Cancel') . '</a>' .
            '</p></form></div>' .
            '<div class="phdr">&#160;</div>';
    }
}
