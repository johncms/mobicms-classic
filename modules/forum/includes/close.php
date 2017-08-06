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

/** @var Psr\Http\Message\ServerRequestInterface $request */
$request = $container->get(Psr\Http\Message\ServerRequestInterface::class);
$queryParams = $request->getQueryParams();

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

if (($systemUser->rights != 3 && $systemUser->rights < 6) || !$id) {
    $response->redirect('.')->sendHeaders();
    exit;
}

if ($db->query("SELECT COUNT(*) FROM `forum` WHERE `id` = '$id' AND `type` = 't'")->fetchColumn()) {
    if (isset($queryParams['closed'])) {
        $db->exec("UPDATE `forum` SET `edit` = '1' WHERE `id` = '$id'");
    } else {
        $db->exec("UPDATE `forum` SET `edit` = '0' WHERE `id` = '$id'");
    }
}

header('Location: ?id=' . $id);

