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

if ($id) {
    /** @var Psr\Container\ContainerInterface $container */
    $container = App::getContainer();

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Mobicms\Api\ToolsInterface $tools */
    $tools = $container->get(Mobicms\Api\ToolsInterface::class);

    $error = false;

    // Скачивание прикрепленного файла Форума
    $req = $db->query("SELECT * FROM `cms_forum_files` WHERE `id` = '$id'");

    if ($req->rowCount()) {
        $res = $req->fetch();

        if (file_exists('../files/forum/attach/' . $res['filename'])) {
            $dlcount = $res['dlcount'] + 1;
            $db->exec("UPDATE `cms_forum_files` SET  `dlcount` = '$dlcount' WHERE `id` = '$id'");
            header('location: ../files/forum/attach/' . $res['filename']);
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if ($error) {
        require('../system/head.php');
        echo $tools->displayError(_t('File does not exist'), '<a href="index.php">' . _t('Forum') . '</a>');
        require('../system/end.php');
        exit;
    }
} else {
    header('location: index.php');
}
