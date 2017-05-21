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

if (($systemUser->rights != 3 && $systemUser->rights < 6) || !$id) {
    header('Location: index.php');
    exit;
}

if ($db->query("SELECT COUNT(*) FROM `forum` WHERE `id` = '$id' AND `type` = 't'")->fetchColumn()) {
    if (isset($_GET['closed'])) {
        $db->exec("UPDATE `forum` SET `edit` = '1' WHERE `id` = '$id'");
    } else {
        $db->exec("UPDATE `forum` SET `edit` = '0' WHERE `id` = '$id'");
    }
}

header("Location: index.php?id=$id");
