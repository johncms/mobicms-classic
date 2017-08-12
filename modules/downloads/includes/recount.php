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

if ($systemUser->rights == 4 || $systemUser->rights >= 6) {
    $req_down = $db->query("SELECT `dir`, `name`, `id` FROM `download__category`");

    while ($res_down = $req_down->fetch()) {
        $dir_files = $db->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '2' AND `dir` LIKE '" . ($res_down['dir']) . "%'")->fetchColumn();
        $db->exec("UPDATE `download__category` SET `total` = '$dir_files' WHERE `id` = '" . $res_down['id'] . "'");
    }
}

ob_start();
echo '<div class="phdr"><b>' . _t('Update counters') . '</b></div>' .
    '<div class="gmenu"><p>' . _t('All Counters successfully updated') . '</p></div>' .
    '<div class="phdr"><a href="?id=' . $id . '">' . _t('Back') . '</a></div>';
require ROOT_PATH . 'system/end.php';

