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
 * @var int                        $id
 *
 * @var PDO                        $db
 * @var Mobicms\Api\UserInterface  $systemUser
 * @var Mobicms\Api\ToolsInterface $tools
 * @var League\Plates\Engine       $view
 */

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    if (!$id) {
        exit(_t('Wrong data'));
    }

    $ms = $db->query("SELECT * FROM `forum` WHERE `id` = '$id'")->fetch();

    if ($ms['type'] != "t") {
        exit(_t('Wrong data'));
    }

    if (isset($_POST['submit'])) {
        $nn = isset($_POST['nn']) ? trim($_POST['nn']) : '';

        if (!$nn) {
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('You have not entered topic name'), '<a href="index.php?act=ren&amp;id=' . $id . '">' . _t('Repeat') . '</a>'),
            ]);
            exit;
        }

        // Проверяем, есть ли тема с таким же названием?
        $pt = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `refid` = '" . $ms['refid'] . "' AND TEXT=" . $db->quote($nn) . " LIMIT 1");

        if ($pt->rowCount()) {
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('Topic with same name already exists in this section'), '<a href="index.php?act=ren&amp;id=' . $id . '">' . _t('Repeat') . '</a>'),
            ]);
            exit;
        }

        $db->exec("UPDATE `forum` SET  text=" . $db->quote($nn) . " WHERE id='" . $id . "'");
        header('Location: ?id=' . $id);
    } else {
        // Переименовываем тему
        ob_start();
        echo '<div class="phdr"><a href="index.php?id=' . $id . '"><b>' . _t('Forum') . '</b></a> | ' . _t('Rename Topic') . '</div>' .
            '<div class="menu"><form action="index.php?act=ren&amp;id=' . $id . '" method="post">' .
            '<p><h3>' . _t('Topic name') . '</h3>' .
            '<input type="text" name="nn" value="' . $ms['text'] . '"/></p>' .
            '<p><input type="submit" name="submit" value="' . _t('Save') . '"/></p>' .
            '</form></div>' .
            '<div class="phdr"><a href="index.php?id=' . $id . '">' . _t('Back') . '</a></div>';
    }
}
