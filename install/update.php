<?php

define('MOBICMS', 1);
require 'system/bootstrap.php';

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

$req = $db->query('SELECT `id`, `about` FROM `users`');
$stmt = $db->prepare('UPDATE `users` SET `about` = ? WHERE `id` = ?');

while ($res = $req->fetch()) {
    if (!empty($res['about'])) {
        $out = str_replace('<br />', '', $res['about']);
        $out = html_entity_decode($out, ENT_QUOTES, 'UTF-8');
        $stmt->execute([$out, $res['id']]);
    }
}

echo 'Converting is completed';
