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

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

// Загрузка выбранного файла и обработка счетчика скачиваний
$error = [];
$req = $db->query("SELECT * FROM `cms_album_files` WHERE `id` = '$img'");

if ($req->rowCount()) {
    $res = $req->fetch();

    // Проверка прав доступа
    if ($systemUser->rights < 6 && $systemUser->id != $res['user_id']) {
        $req_a = $db->query("SELECT * FROM `cms_album_cat` WHERE `id` = '" . $res['album_id'] . "'");

        if ($req_a->rowCount()) {
            $res_a = $req_a->fetch();
            if ($res_a['access'] == 1 || $res_a['access'] == 2 && (!isset($_SESSION['ap']) || $_SESSION['ap'] != $res_a['password'])) {
                $error[] = _t('Access forbidden');
            }
        } else {
            $error[] = _t('Wrong data');
        }
    }

    // Проверка наличия файла
    if (!$error && !file_exists(UPLOAD_PATH . 'users/album/' . $res['user_id'] . '/' . $res['img_name'])) {
        $error[] = _t('File does not exist');
    }
} else {
    $error[] = _t('Wrong data');
}
if (!$error) {
    // Счетчик скачиваний
    if (!$db->query("SELECT COUNT(*) FROM `cms_album_downloads` WHERE `user_id` = '" . $systemUser->id . "' AND `file_id` = '$img'")->fetchColumn()) {
        $db->exec("INSERT INTO `cms_album_downloads` SET `user_id` = '" . $systemUser->id . "', `file_id` = '$img', `time` = '" . time() . "'");
        $downloads = $db->query("SELECT COUNT(*) FROM `cms_album_downloads` WHERE `file_id` = '$img'")->fetchColumn();
        $db->exec("UPDATE `cms_album_files` SET `downloads` = '$downloads' WHERE `id` = '$img'");
    }

    // Отдаем файл
    header('Location: ' . $config['homeurl'] . '/uploads/users/album/' . $res['user_id'] . '/' . $res['img_name']);
} else {
    ob_start();
    echo $tools->displayError($error, '<a href="index.php">' . _t('Back') . '</a>');
}
