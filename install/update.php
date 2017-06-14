<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

define('MOBICMS', 1);

require '../system/bootstrap.php';

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

// Обновляем Гостевую
$req = $db->query('SELECT `id`, `ip` FROM `guest`');

while ($res = $req->fetch()) {
    if (ctype_digit($res['ip'])) {
        $db->exec("UPDATE `guest` SET `ip` = '" . long2ip($res['ip']) . "' WHERE `id` = " . $res['id']);
    }
}

echo '<div>Guestbook</div>';

// Обновляем Форум
$req = $db->query("SELECT `id`, `ip`, `ip_via_proxy` FROM `forum` WHERE `type` = 'm'");

while ($res = $req->fetch()) {
    if (ctype_digit($res['ip'])) {
        $proxy = $res['ip_via_proxy'] ? long2ip($res['ip_via_proxy']) : '';
        $db->exec("UPDATE `forum` SET `ip` = '" . long2ip($res['ip']) . "', `ip_via_proxy` = '" . $proxy . "' WHERE `id` = " . $res['id']);
    }
}

echo '<div>Forum</div>';

// Обновляем пользователей
$req = $db->query("SELECT `id`, `ip`, `ip_via_proxy` FROM `users`");

while ($res = $req->fetch()) {
    if (ctype_digit($res['ip'])) {
        $proxy = $res['ip_via_proxy'] ? long2ip($res['ip_via_proxy']) : '';
        $db->exec("UPDATE `users` SET `ip` = '" . long2ip($res['ip']) . "', `ip_via_proxy` = '" . $proxy . "' WHERE `id` = " . $res['id']);
    }
}

echo '<div>Users</div>';

// Обновляем историю IP
$req = $db->query("SELECT `id`, `ip`, `ip_via_proxy` FROM `cms_users_iphistory`");

while ($res = $req->fetch()) {
    if (ctype_digit($res['ip'])) {
        $proxy = $res['ip_via_proxy'] ? long2ip($res['ip_via_proxy']) : '';
        $db->exec("UPDATE `cms_users_iphistory` SET `ip` = '" . long2ip($res['ip']) . "', `ip_via_proxy` = '" . $proxy . "' WHERE `id` = " . $res['id']);
    }
}

echo '<div>IP History</div>';
