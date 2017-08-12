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

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);

ob_start();

// Редактирование файла
$req_down = $db->query("SELECT * FROM `download__files` WHERE `id` = '" . $id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();

if ($systemUser->rights == 4 || $systemUser->rights >= 6) {
    if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name'])) {
        echo '<a href="?">' . _t('Downloads') . '</a>';
        echo $view->render('system::app/legacy', [
            'title'   => _t('Downloads'),
            'content' => ob_get_clean(),
        ]);
        exit;
    }

    if (isset($_POST['submit'])) {
        $name = isset($_POST['text']) ? trim($_POST['text']) : null;
        $name_link = isset($_POST['name_link']) ? htmlspecialchars(mb_substr($_POST['name_link'], 0, 200)) : null;

        if ($name_link && $name) {
            $stmt = $db->prepare("
            UPDATE `download__files` SET
            `rus_name` = ?,
            `text`     = ?
            WHERE `id` = ?
        ");

            $stmt->execute([
                $name,
                $name_link,
                $id,
            ]);

            header('Location: ?act=view&id=' . $id);
        } else {
            echo _t('The required fields are not filled') . ' <a href="?act=edit_file&amp;id=' . $id . '">' . _t('Repeat') . '</a>';
        }
    } else {
        $file_name = htmlspecialchars($res_down['rus_name']);
        echo '<div class="phdr"><b>' . $file_name . '</b></div>' .
            '<div class="list1"><form action="?act=edit_file&amp;id=' . $id . '" method="post">' .
            '<p>' . _t('Name for display') . ' (мах. 200):<br><input type="text" name="text" value="' . $file_name . '"/></p>' .
            '<p>' . _t('Link to download file') . ' (мах. 200):<br><input type="text" name="name_link" value="' . $res_down['text'] . '"/></p>' .
            '<p><br><input type="submit" name="submit" value="' . _t('Save') . '"/></p></form></div>' .
            '<div class="phdr"><a href="?act=view&amp;id=' . $id . '">' . _t('Back') . '</a></div>';
    }

    echo $view->render('system::app/legacy', [
        'title'   => _t('Downloads'),
        'content' => ob_get_clean(),
    ]);
}
